<?php
/**
 * @author Honza Cerny (http://honzacerny.com)
 */

namespace Aprila;

use Nette\Security\User;

abstract class BaseSecurePresenter extends BasePresenter
{

	function beforeRender()
	{
		parent::beforeRender();

		if (!$this->getUser()->isLoggedIn()
			// TODO: nahradit za interface? a zbavit se \Site
			&& !$this instanceof SignPresenter
			&& !$this instanceof \AdminModule\SignPresenter
			&& !$this instanceof \App\AdminModule\Presenters\SignPresenter
			&& !$this instanceof PasswordRecoveryPresenter
			&& !$this instanceof \AdminModule\PasswordRecoveryPresenter
			&& !$this instanceof \App\AdminModule\Presenters\PasswordRecoveryPresenter
		) {
			if ($this->getUser()->getLogoutReason() === User::INACTIVITY) {
				$this->flashMessage('Logged out, because timeout.');
			}
			$this->redirect('Sign:in');
		}
	}
}