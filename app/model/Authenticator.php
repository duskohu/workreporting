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

use Nette\Database\SelectionFactory;
use Nette\Security\AuthenticationException;
use Nette\Security\IAuthenticator;
use Nette\Security\Identity;
use Nette\Security\IIdentity;

/**
 * Authenticator
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class Authenticator extends BaseManager implements IAuthenticator
{
	/** @var UserManager */
	private $userManager;

	/** @var UserRoleManager */
	private $userRoleManager;


	/**
	 * CONSTRUCT
	 * @param SelectionFactory $selectionFactory
	 * @param UserManager $userManager
	 * @param UserRoleManager $userRoleManager
	 */
	public function __construct(
		SelectionFactory $selectionFactory,
		UserManager $userManager,
		UserRoleManager $userRoleManager
	)
	{
		parent::__construct($selectionFactory);
		$this->userManager = $userManager;
		$this->userRoleManager = $userRoleManager;
	}


	/**
	 * User authenticate
	 * @param array $credentials
	 * @return IIdentity
	 * @throws AuthenticationException
	 */
	public function authenticate(array $credentials)
	{
		list($loginName, $password) = $credentials;

		$userEntity = $this->userManager->findByLoginName($loginName);

		if (!$userEntity) {
			throw new AuthenticationException("Používateľ {$loginName} nebol najdený.", self::IDENTITY_NOT_FOUND);
		}

		if ($userEntity->password !== $this->userManager->calculateHash($password, $userEntity->password)) {
			throw new AuthenticationException("Neplatné heslo pre používatela {$loginName}!", self::INVALID_CREDENTIAL);
		}

		$systemRoles = $this->userRoleManager->getUserRoles($userEntity->id);
		$userRole = array();
		foreach ($systemRoles as $item) {
			$userRole[$item->role->id] = $item->role->name;
		}

		$userArray = $userEntity->toArray();
		unset($userArray["password"]);

		return new Identity($userEntity->id, $userRole, $userArray);
	}
}
