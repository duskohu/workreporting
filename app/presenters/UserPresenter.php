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

use Arachne\SecurityAnnotations\LoggedIn;
use Arachne\SecurityAnnotations\Allowed;
use Arachne\SecurityAnnotations\InRole;


/**
 * User Presenter
 * @package Nas- Nette aplication System
 *
 * @LoggedIn
 */
class UserPresenter extends SecuredPresenter
{
	/** @var UserManager */
	private $userManager;

	/** @var IUserFormControl */
	private $userFormControl;

	/** @var IUserPasswordChangeFormControl */
	private $userPasswordChangeFormControl;

	/** @var ActiveRow */
	private $userEntity;


	/**
	 * INJECT Repository
	 * @param UserManager $userManager
	 */
	public function injectRepository(UserManager $userManager)
	{
		$this->userManager = $userManager;
	}


	/**
	 * INJECT UserFormControl
	 * @param IUserFormControl $userFormControl
	 */
	public function injectUserFormControlFactory(IUserFormControl $userFormControl)
	{
		$this->userFormControl = $userFormControl;
	}


	/**
	 * INJECT UserPasswordChangeFormControl
	 * @param IUserPasswordChangeFormControl $userPasswordChangeFormControl
	 */
	public function injectUserPasswordChangeFormControl(IUserPasswordChangeFormControl $userPasswordChangeFormControl)
	{
		$this->userPasswordChangeFormControl = $userPasswordChangeFormControl;
	}


	/**
	 * STARTUP
	 */
	public function startup()
	{
		parent::startup();
		$user = $this->getUser();
		$this->userEntity = $this->userManager->find($user->id);
		$this->template->pageTitle = 'Môj profil';
	}


	/**
	 * RENDER - Default
	 */
	public function renderDefault()
	{
		if ($this->isAjax()) {
			$this->invalidateControl();
		}
	}


	/**
	 * CONTROL - EditUser
	 * @return UserFormControl
	 */
	protected function createComponentEditUser()
	{
		$control = $this->userFormControl->create();
		$control->setUserEntity($this->userEntity);
		$control->setFileTemplate($this->nasConfigStorage->templatesDir.'/components/formEditUser.latte');
		return $control;
	}


	/**
	 * CONTROL - UserChangePassword
	 * @return UserPasswordChangeFormControl
	 */
	protected function createComponentUserChangePassword()
	{
		$control = $this->userPasswordChangeFormControl->create($this->userEntity);
		$control->setFileTemplate($this->nasConfigStorage->templatesDir.'/components/formUserChangePassword.latte');
		return $control;
	}
}
