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

use NasExt\Logger\Logger;
use Nette\Application\UI\Form;
use Nette\Security\AuthenticationException;
use Nette\Security\User;

/**
 * Sign Presenter
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class SignPresenter extends BasePresenter
{
	/** @persistent */
	public $backlink = '';

	/** @var User */
	private $user;

	/** @var  ISignInFormControl */
	private $signInFormControl;

	/** @var IResetPasswordFormControl */
	private $resetPasswordFormControl;

	/** @var IUserRegisterFormControl */
	private $userRegisterFormControl;


	/**
	 * INJECT SignInFormControl
	 * @param  ISignInFormControl $signInFormControl
	 */
	public function injectSignInFormControl(ISignInFormControl $signInFormControl)
	{
		$this->signInFormControl = $signInFormControl;
	}


	/**
	 * INJECT UserRegisterFormControl
	 * @param  IUserRegisterFormControl $userRegisterFormControl
	 */
	public function injectUserRegisterFormControl(IUserRegisterFormControl $userRegisterFormControl)
	{
		$this->userRegisterFormControl = $userRegisterFormControl;
	}


	/**
	 * INJECT ResetPasswordFormControl
	 * @param  IResetPasswordFormControl $resetPasswordFormControl
	 */
	public function injectResetPasswordFormControl(IResetPasswordFormControl $resetPasswordFormControl)
	{
		$this->resetPasswordFormControl = $resetPasswordFormControl;
	}


	/**
	 * STARTUP
	 */
	public function startup()
	{
		parent::startup();
		$this->user = $this->getUser();
	}


	/**
	 * RENDER - Default
	 */
	public function renderDefault()
	{
		$this->template->pageTitle = 'Prihlásiť sa';
	}


	/**
	 * RENDER - Reset
	 */
	public function renderReset()
	{
		$this->template->pageTitle = 'Nové heslo';
	}


	/**
	 * RENDER - Register
	 */
	public function renderRegister()
	{
		$this->template->pageTitle = 'Registrácia';
	}


	/**
	 * ACTION - Out
	 */
	public function actionOut()
	{
		$this->logger->message("Úspešné odhlásenie {$this->user->getIdentity()->email}.", 'USER', Logger::INFO);
		$this->user->logout(TRUE);
		$this->redirect('default');
	}


	/**
	 * CONTROL - SignIn
	 * @return SignInFormControl
	 */
	protected function createComponentSignIn()
	{
		$control = $this->signInFormControl->create();
		$control['form']->onSuccess[] = callback($this, 'processSubmit');
		$control->setFileTemplate($this->nasConfigStorage->templatesDir.'/components/formSignIn.latte');
		return $control;
	}


	/**
	 * PROCESS-SUBMIT-FORM - signIn
	 * @param Form $form
	 */
	public function processSubmit(Form $form)
	{
		$values = $form->getValues();
		$signInError = FALSE;
		try {
			if ($values->rememberMe) {
				$this->user->setExpiration('+30 days', FALSE);
			} else {
				$this->user->setExpiration('+30 minutes', TRUE);
			}
			$this->user->login($values->loginName, $values->password);
			$this->logger->message("Úspešné prihlásenie {$this->user->getIdentity()->email}.", 'USER', Logger::INFO, $values);
			$this->restoreRequest($this->backlink);
		} catch (AuthenticationException $e) {
			$this->flashMessage('Neplatné uživatelské meno alebo heslo.', 'error');
			$this->logger->message($e, 'USER', Logger::WARNING, $values);
			$signInError = TRUE;
			if($this->isAjax()){
				$this->invalidateControl();
			}
		}

		if(!$signInError){
			$this->redirect('Homepage:default');
		}
	}


	/**
	 * CONTROL - UserRegister
	 * @return UserRegisterFormControl
	 */
	protected function createComponentUserRegister()
	{
		$control = $this->userRegisterFormControl->create();
		$control->setFileTemplate($this->nasConfigStorage->templatesDir.'/components/formUserRegister.latte');
		return $control;
	}


	/**
	 * CONTROL - ResetPassword
	 * @return ResetPasswordFormControl
	 */
	protected function createComponentResetPassword()
	{
		$control = $this->resetPasswordFormControl->create();
		$control->setFileTemplate($this->nasConfigStorage->templatesDir.'/components/formResetPassword.latte');
		return $control;
	}
}
