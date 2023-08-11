<?php
/**
 * @author Honza Cerny (http://honzacerny.com)
 */

namespace Aprila\Model;

use Nette,
	Nette\Database\Context,
	Nette\Security,
	Nette\Security\Passwords,
	Nette\Utils\Random,
	Nette\Utils\DateTime,
	Nette\Utils\Image;

/**
 * UserManager: add, edit, or authenticate users
 *
 * @author Honza Cerny
 */
class UserManager extends Nette\Object implements Nette\Security\IAuthenticator
{
	/**
	 * @var \Nette\Database\Context
	 */
	protected $database;

	/**
	 * @var  string
	 */
	protected $dir;

	/**
	 * @var  string
	 */
	protected $uri;

	/**
	 * @var  bool
	 */
	public $useFiles = FALSE;

	/*
		Aprila base users roles:
		- guest
		- user
		- admin
		- root
	*/
	/**
	 * User roles
	 *
	 * @var array
	 */
	protected $roles = array(
		"user" => "user",
		"admin" => "admin",
		"root" => "super user"
	);


	/**
	 * @param \Nette\Database\Context $database
	 */
	public function __construct(Context $database)
	{
		$this->database = $database;
	}


	/**
	 * @param $dir
	 * @param $uri
	 */
	public function setFilesFolder($dir, $uri)
	{
		$this->dir = $dir;
		$this->uri = $uri;
		if (is_dir($this->dir)) {
			$this->useFiles = TRUE;
		} else {
			// throw excaption Bad Configuration???
		}
	}


	/**
	 * Performs an authentication
	 *
	 * @param array $credentials
	 * @return Security\Identity|Security\IIdentity
	 * @throws \Nette\Security\AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($username, $password) = $credentials;

		$row = $this->database->table('users')
			->where('username ? OR email ?', $username, $username)
			->where('active', '1')
			->fetch();

		if (!$row) {
			throw new Security\AuthenticationException('The username is incorrect.', self::IDENTITY_NOT_FOUND);

		} elseif (!Passwords::verify($password, $row->password)) {
			throw new Security\AuthenticationException('The password is incorrect.', self::INVALID_CREDENTIAL);

		} elseif (Passwords::needsRehash($row->password)) {
			$row->update(array(
				'password' => Passwords::hash($password),
			));
		}

		$arr = $row->toArray();
		unset($arr['password']);

		return new Security\Identity($row->id, $row->role, $arr);
	}


	/**
	 * Computes salted token hash.
	 *
	 * @param $token
	 * @param $salt
	 * @return string
	 */
	public static function calculateHash($token, $salt)
	{
		return hash('sha256', $token . $salt);
	}


	/**
	 * Generates salt.
	 *
	 * @param int $length
	 * @return string
	 */
	public static function generateToken($length = 32)
	{
		return Random::generate($length, '0-9a-z');
	}


	/********************* find* methods *********************/


	/**
	 * Return user by Id (ignored status for deactivation)
	 *
	 * @param $id
	 * @return \Nette\Database\Table\ActiveRow
	 */
	public function get($id)
	{
		return $this->database->table('users')->get($id);
	}


	/**
	 * Vrací řádek podle filtru
	 *
	 * @param Array where ex. array('name' => 'John')
	 * @return Array
	 */
	public function getBy(array $by)
	{
		return $this->database->table('users')->select('*')
			->where($by)
			->where('active', '1')
			->fetch();
	}


	/**
	 * Return active user by Id
	 *
	 * @param int user id
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function getActiveUser($id)
	{
		return $this->database->table('users')
			->where('id', $id)
			->where('active', '1')
			->fetch();
	}


	/**
	 * return all users
	 *
	 * @return Array
	 */
	public function findAll()
	{
		return $this->database->table('users')
			->where('active', '1')
			->fetchAll();
	}


	/**
	 * Vrací řádky podle filtru
	 *
	 * @param Array where ex. array('name' => 'John')
	 * @return Array
	 */
	public function findBy(array $by)
	{
		return $this->database->table('users')
			->where($by)
			->where('active', '1')
			->fetchAll();
	}


	/**
	 * Vrací řádky podle query
	 *
	 * @param string search query
	 * @return Array
	 */
	public function findFulltext($query)
	{
		$like = '%' . $query . '%';

		return $this->database->table('users')
			->where('username LIKE ? OR email LIKE ?', $like, $like)
			->where('active', '1')
			->fetchAll();
	}


	/**
	 * Return all deactivated users
	 *
	 * @return array|Nette\Database\Table\IRow[]
	 */
	public function findAllDeactivated()
	{
		return $this->database->table('users')
			->where('active', '0')
			->fetchAll();
	}


	/********************* update* methods *********************/


	/**
	 * save file and return path
	 *
	 * @param Nette\Http\FileUpload $image
	 * @return string
	 * @throws \Nette\Utils\UnknownImageFileException
	 */
	public function saveImage(Nette\Http\FileUpload $image)
	{
		$filename = $image->getSanitizedName();
		$path = $this->dir;

		$filename = $this->getFileName($path, $filename);

		$image->move("$path/$filename");
		$filePath = "{$this->uri}/$filename";

		$image = Image::fromFile("$path/$filename");
		$image->resize(150, 150, Image::EXACT);

		$image->save("$path/$filename");

		return $filePath;
	}


	/**
	 * return unique file name
	 *
	 * @param $path
	 * @param $filename
	 * @return string
	 */
	public function getFileName($path, $filename)
	{
		if (file_exists("$path/$filename")) {
			$filename = Nette\Utils\Random::generate() . '_' . $filename;
			$filename = $this->getFileName($path, $filename);
		} else {
			$filename;
		}

		return $filename;
	}


	/**
	 * Inserts new user
	 * todo: use it or add()?
	 *
	 * @param array|\Nette\ArrayHash $form
	 * @return \Nette\Database\Table\ActiveRow(
	 * @throws \Aprila\DuplicateEntryException
	 * @throws \PDOException
	 */
	public function addUser($email, $username, $password, $role, $name, Nette\Http\FileUpload $avatar = NULL)
	{
		$password = Passwords::hash($password);

		$filePath = '';

		if ($this->useFiles && $avatar && $avatar->isOk()) {
			$filePath = $this->saveImage($avatar);
		}

		$data = array(
			'email' => $email,
			'role' => $role,
			'username' => $username,
			'password' => $password,
			'name' => $name,
			'avatar' => $filePath
		);

		try {
			$person = $this->database->table('users')->insert($data);

		} catch (\PDOException $e) {
			if ($e->getCode() == '23000') {
				throw new \Aprila\DuplicateEntryException;
			} else {
				throw $e;
			}
		}

		return $person;

	}


	/**
	 * edit user
	 * todo: use it or edit()?
	 *
	 * @param $id
	 * @param $email string
	 * @param $username string
	 * @param $password string
	 * @param $role string
	 * @param $name string
	 * @param Nette\Http\FileUpload $avatar
	 * @return bool
	 * @throws DuplicateEntryException
	 */
	public function editUser($id, $email, $username, $password, $role, $name, Nette\Http\FileUpload $avatar = NULL)
	{
		$data = array(
			'email' => $email,
			'username' => $username,
			'role' => $role,
			'name' => $name
		);

		if ($this->useFiles && $avatar && $avatar->isOk()) {
			$data['avatar'] = $this->saveImage($avatar);
		}

		if ($password !== '') {
			$data['password'] = Passwords::hash($password);
		}

		try {
			$this->get($id)->update($data);

		} catch (\PDOException $e) {
			if ($e->getCode() == '23000') {
				throw new DuplicateEntryException;
			} else {
				throw $e;
			}
		}

		return TRUE;
	}


	/**
	 * Inserts new user
	 * todo: use parent function
	 *
	 * @param array|\Nette\ArrayHash $form
	 * @return \Nette\Database\Table\ActiveRow
	 * @throws \Aprila\DuplicateEntryException
	 * @throws \PDOException
	 */
	public function add($form)
	{
		$password = Passwords::hash($form['password']);

		$data = array(
			'email' => $form['email'],
			'role' => $form['role'],
			'username' => $form['username'],
			'password' => $password,
			'name' => isset($form->name) ? $form->name : ''
		);

		try {
			$person = $this->database->table('users')->insert($data);

		} catch (\PDOException $e) {
			if ($e->getCode() == '23000') {
				throw new \Aprila\DuplicateEntryException;
			} else {
				throw $e;
			}
		}

		return $person;

	}


	/**
	 * Deactivate user
	 *
	 * @param $userId
	 * @return bool
	 */
	public function deactivateUser($userId)
	{
		$user = $this->get($userId);
		if ($user) {
			$user->update(array('active' => '0'));

			return TRUE;
		}

		return FALSE;
	}


	/**
	 * generate token for user, who can reset password
	 *
	 * @param int $userId
	 * @return bool|string
	 */
	public function generateRecoveryToken($userId)
	{
		$token = SELF::generateToken(8);
		$salt = SELF::generateToken();

		$tokenHash = SELF::calculateHash($token, $salt);

		$this->database->table('users_password_reset')
			->where('userId', $userId)
			->delete();

		$data = array(
			'salt' => $salt,
			'token' => $tokenHash,
			'userId' => $userId,
			'created' => new \DateTime()
		);

		$status = $this->database->table('users_password_reset')->insert($data);
		if ($status) {
			return $token;

		} else {
			return FALSE;
		}
	}


	/**
	 * verify token in database and delete it
	 *
	 * @param $token
	 * @param int $userId
	 * @return bool
	 */
	public function verifyToken($token, $userId)
	{
		$request = $this->database->table('users_password_reset')
			->where('userId', $userId)
			->fetch();

		$now = new Nette\DateTime();
		if ($request) {
			$dateDiff = $now->diff($request->created);

			$totalHours = ($dateDiff->d * 24) + $dateDiff->h;

			// remove token older than 2h
			if ($totalHours > 2) {
				$this->database->table('users_password_reset')
					->where('userId', $userId)
					->delete();

				return FALSE;
			}
		}

		if ($request && $request->id > 0) {
			if ($request->token == SELF::calculateHash($token, $request->salt)) {
				$this->database->table('users_password_reset')
					->where('userId', $userId)
					->delete();

				return TRUE;
			}
		}

		return FALSE;
	}


	/**
	 * @param $password string
	 * @param $new_password string
	 * @param $userId int
	 * @return bool
	 * @throws \Aprila\InvalidArgumentException
	 */
	public function changeUserPassword($password, $new_password, $userId)
	{
		$user = $this->database->table('users')
			->where('id', $userId)
			->fetch();

		if (!Passwords::verify($password, $user->password)) {
			throw new \Aprila\InvalidArgumentException('The origin password is incorrect');

		} else {
			$this->database->table('users')
				->where('id', $userId)
				->update(array('password' => Passwords::hash($new_password)));

			return TRUE;
		}
	}


	/**
	 * @param $password
	 * @param $userId
	 * @return bool
	 */
	public function setUserPassword($password, $userId)
	{
		$user = $this->database->table('users')
			->where('id', $userId);

		if ($user) {
			$user->update(array('password' => Passwords::hash($password)));

			return TRUE;
		}

		return FALSE;
	}


	/**
	 * @param $userId int
	 * @return bool
	 */
	public function activateUser($userId)
	{
		$user = $this->get($userId);
		if ($user) {
			$user->update(array('active' => '1'));

			return TRUE;
		}

		return FALSE;
	}


	/**
	 * @param $userId int
	 * @param $change array
	 * @return Nette\Database\Table\ActiveRow
	 */
	public function edit($userId, $change)
	{
		$data = array();

		if (isset($change['name'])) {
			$data['name'] = $change['name'];
		}

		$avatar = NULL;

		if ($this->useFiles && isset($change['avatar'])) {
			$avatar = $change['avatar'];

			if ($avatar->isOk()) {
				$data['avatar'] = $this->saveImage($avatar);
			}
		}

		$this->get($userId)->update($data);

		return $this->get($userId);
	}


	/**
	 * @param $id
	 * @param $email
	 * @param Nette\Application\UI\ITemplate $template
	 * @param Nette\Mail\Message $mailMessage
	 * @param Nette\Mail\IMailer $mailer
	 * @return bool
	 */
	public function changeEmailStepOne($id,
									   $email,
									   Nette\Application\UI\ITemplate $template,
									   Nette\Mail\Message $mailMessage,
									   Nette\Mail\IMailer $mailer)
	{
		$person = $this->get($id);
		if (!isset($person->email) && $person->email == '') {
			return FALSE;
		}
		$tokenOne = Random::generate(4);
		$tokenTwo = Random::generate(4);


		$tokenHashOne = Passwords::hash($tokenOne);
		$tokenHashTwo = Passwords::hash($tokenTwo);

		$data = array(
			'change_email' => $email,
			'change_email_tokenOne' => $tokenHashOne,
			'change_email_tokenTwo' => $tokenHashTwo,
			'change_email_requested' => new DateTime()
		);

		$this->get($id)->update($data);

		$statusOne = $this->changeEmailsendEmail($email, $tokenTwo, $template, $mailMessage, $mailer);
		$statusTwo = $this->changeEmailsendEmail($person->email, $tokenOne, $template, $mailMessage, $mailer);

		if ($statusOne && $statusTwo) {
			return TRUE;
		}

		return FALSE;
	}


	/**
	 * @param $id int
	 * @param $tokenOne string
	 * @param $tokenTwo string
	 */
	public function changeEmailStepTwo($id, $tokenOne, $tokenTwo)
	{
		$person = $this->get($id);

		$now = new DateTime();
		$diff = $now->diff($person->change_email_requested);

		if ($diff->h >= 1 || $diff->days != 0 ) {
			// todo throw exception
			return FALSE;
		}

		$verified = FALSE;

		if (Passwords::verify($tokenOne, $person->change_email_tokenOne)) {
			if (Passwords::verify($tokenTwo, $person->change_email_tokenTwo)) {
				$verified = TRUE;
			}
		} else {
			// swithed codes?
			if (Passwords::verify($tokenOne, $person->change_email_tokenTwo)) {
				if (Passwords::verify($tokenTwo, $person->change_email_tokenOne)) {
					$verified = TRUE;
				}
			}
		}

		if (!$verified) {
			// todo throw excaption
			return FALSE;
		}

		$newEmail = $person->change_email;

		$data = array(
			'email' => $newEmail,
			'change_email' => '',
			'change_email_tokenOne' => '',
			'change_email_tokenTwo' => '',
			'change_email_requested' => ''
		);

		$this->get($id)->update($data);

		return $newEmail;
	}


	/**
	 * @param $email
	 * @param $token
	 * @param Nette\Application\UI\ITemplate $template
	 * @param Nette\Mail\Message $mailMessage
	 * @param Nette\Mail\IMailer $mailer
	 * @return bool
	 */
	private function changeEmailsendEmail($email,
										  $token,
										  Nette\Application\UI\ITemplate $template,
										  Nette\Mail\Message $mailMessage,
										  Nette\Mail\IMailer $mailer)
	{
		$template->token = $token;

		$mailMessageCurrent = clone $mailMessage;

		$mailMessageCurrent->addTo($email)
			->setHtmlBody($template);

		try {
			$mailer->send($mailMessageCurrent);
			$status = TRUE;
		} catch (\Exception $e) {
			$status = FALSE;
		}

		return $status;
	}


	/**
	 * @param $userId int
	 * @return bool
	 */
	public function removeAvatar($userId)
	{
		$user = $this->get($userId);
		if ($user) {
			$user->update(array('avatar' => ''));

			return TRUE;
		}

		return FALSE;
	}


	public function findUserRoles()
	{
		return $this->roles;
	}

}
