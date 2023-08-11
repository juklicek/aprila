<?php
/**
 * @author Honza Cerny (http://honzacerny.com)
 */

namespace Aprila\Model;


trait SoftDelete
{
	/**
	 * Soft delete
	 *
	 * @param $id int
	 * @param int $user
	 * @return bool
	 */
	public function delete($id, $user = 0)
	{
		$remove = array(
			'deleted' => new \DateTime()
		);

		if ($user) {
			$remove['deleted_by'] = $user;
		}

		$this->update($id, $remove);

		return TRUE;
	}
}