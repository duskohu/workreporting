<?php

/**
 * This file is part of the Nas of Nette Framework
 *
 * Copyright (c) 2013 Dusan Hudak (http://dusan-hudak.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nas;

use Nette\Database\Table\Selection;

/**
 * UserRoleManager
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class UserRoleManager extends BaseManager
{

	/**
	 * @param int $userId
	 * @return Selection
	 */
	public function getUserRoles($userId)
	{
		$where = array("user_id" => $userId);
		$userRole = $this->findBy($where);
		return $userRole;
	}


	/**
	 * @param int $userId
	 * @param array $values
	 * @throws \PDOException
	 */
	public function editUserRole($userId, $values)
	{
		try {
			$this->connection->beginTransaction();
			$this->getUserRoles($userId)->delete();
			foreach ($values->role as $key => $value) {
				if ($value == TRUE) {
					$this->getTable()->insert(array(
						'user_id' => $userId,
						'role_id' => $key,
					));
				}
			}
			$this->connection->commit();
		} catch (\PDOException $e) {
			$this->connection->rollback();
			throw $e;
		}
	}
}
