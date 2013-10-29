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

use Nette\Database\Table\ActiveRow;
use Nette\Utils\Strings;

/**
 * UserManager
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class UserManager extends BaseManager
{
	const ACTIVE = 1;
	const BLOCK = 0;


	/**
	 * Find user by LoginName
	 * @param  string $loginName
	 * @param  bool $active set if find also no-active user
	 * @return ActiveRow
	 */
	public function findByLoginName($loginName, $active = TRUE)
	{
		$where = array("loginName" => $loginName);
		if ($active == TRUE) {
			$where["active"] = self::ACTIVE;
		}
		$user = $this->findBy($where);
		return $user->fetch();
	}

	/**
	 * Find user by Email
	 * @param  string $email
	 * @param  bool $active set if find also no-active user
	 * @return ActiveRow
	 */
	public function findByEmail($email, $active = TRUE)
	{
		$where = array("email" => $email);
		if ($active == TRUE) {
			$where["active"] = self::ACTIVE;
		}
		$user = $this->findBy($where);
		return $user->fetch();
	}


	/**
	 * Computes salted password hash.
	 * @param  string
	 * @param  string
	 * @return string
	 */
	public static function calculateHash($password, $salt = NULL)
	{
		if ($salt === NULL) {
			$salt = '$2a$07$' . Strings::random(32) . '$';
		}
		return crypt($password, $salt);
	}


	/**
	 * ResetPassword
	 * @param  int|string $id userId user email
	 */
	public function resetPassword($id)
	{
		if (is_numeric($id)) {
			$userEntity = $this->find($id);
		} else {
			$userEntity = $this->findByEmail($id);
		}
		if ($userEntity) {
			$password = $this->passwordGen();
			$passwordHash = $this->calculateHash($password);
			$userEntity->update(array(
				'password' => $passwordHash,
			));
			return $password;
		} else {
			return FALSE;
		}
	}


	/**
	 * Generate password
	 * @param  int
	 * @return string
	 */
	public function passwordGen($length = 6)
	{
		mt_srand((double)microtime() * 1000000);
		$strTypes = 'abcdefghijklmnopqrstuvwxyz' .
			'ABCDEFGHIJKLMNOPQRSTUVWXYZ' .
			'0123456789';
		$password = "";
		while (strlen($password) < $length) {
			$password .= substr($strTypes, mt_rand(0, strlen($strTypes) - 1), 1);
		}
		return ($password);
	}


	/**
	 * Edit user
	 * @param  int
	 * @param  array
	 * @return ActiveRow
	 */
	public function editUser($userId, $values)
	{
		$userData = array();

		if (array_key_exists('loginName', $values)) {
			$userData['loginName'] = $values['loginName'];
		}
		if (array_key_exists('email', $values)) {
			$userData['email'] = $values['email'];
		}
		if (array_key_exists('password', $values)) {
			$userData['password'] = $values['password'];
		}
		if (array_key_exists('active', $values)) {
			$userData['active'] = $values['active'];
		}

		$userEntity = $this->find($userId);
		$userEntity->update($userData);
		return $userEntity;
	}


	/**
	 * Check unique email
	 * @param  string $email
	 * @param  int $userId user ID
	 * @return bool|ActiveRow
	 */
	public function checkEmailUnique($email, $userId = FALSE)
	{
		$email = $this->findAll()->where('email', $email);
		if ($userId != FALSE) {
			$email->where("id != ?", $userId);
		}
		return $email->fetch();
	}


	/**
	 * Check unique email
	 * @param  string $loginName
	 * @param  int $userId user ID
	 * @return bool|ActiveRow
	 */
	public function checkLoginNameUnique($loginName, $userId = FALSE)
	{
		$email = $this->findAll()->where('loginName', $loginName);
		if ($userId != FALSE) {
			$email->where("id != ?", $userId);
		}
		return $email->fetch();
	}


	/**
	 * AddUser
	 * @param array $values
	 * @return ActiveRow
	 */
	public function addUser($values)
	{
		$userData = array();

		if (array_key_exists('loginName', $values)) {
			$userData['loginName'] = $values['loginName'];
		}
		if (array_key_exists('email', $values)) {
			$userData['email'] = $values['email'];
		}
		if (array_key_exists('password', $values)) {
			$userData['password'] = $values['password'];
		}
		if (array_key_exists('active', $values)) {
			$userData['active'] = $values['active'];
		}

		$userEntity = $this->getTable()->insert($userData);
		return $userEntity;
	}
}
