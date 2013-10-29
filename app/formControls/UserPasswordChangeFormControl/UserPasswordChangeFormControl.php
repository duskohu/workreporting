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

use Kdyby\BootstrapFormRenderer\BootstrapRenderer;
use NasExt\Logger\Logger;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

/**
 *  User password change form control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class UserPasswordChangeFormControl extends BaseFormControl
{
	/** @var Logger $logger */
	protected $logger;

	/** @var UserManager */
	private $userManager;

	/** @var ActiveRow */
	private $userEntity;


	/**
	 * CONSTRUCTOR
	 * @param ActiveRow $userEntity
	 * @param Logger $logger
	 * @param UserManager $userManager
	 */
	public function __construct($userEntity, Logger $logger, UserManager $userManager)
	{
		parent::__construct();
		$this->userManager = $userManager;
		$this->logger = $logger;
		$this->userEntity = $userEntity;
	}


	/**
	 * FORM User password change
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer(new BootstrapRenderer());
		$elementPrototype = $form->getElementPrototype();
		$elementPrototype->class[] = lcfirst($this->reflection->getShortName());

		$form->addPassword("oldPassword", "Aktuálne heslo")
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Aktuálne heslo");

		$form->addPassword("newPassword", "Nové heslo")
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->addRule(Form::MIN_LENGTH, 'Nové heslo musí mať aspoň %d znakov', 6)
			->setAttribute("placeholder", "Nové heslo");

		$form->addPassword("newPassword2", "Potvrďte nové heslo")
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->addRule(Form::EQUAL, 'Zadné heslá sa musia zhodovať', $form['newPassword'])
			->setAttribute("placeholder", "Potvrďte nové heslo");

		$form->addSubmit('change', 'Zmeniť heslo');
		$form->onValidate[] = callback($this, 'validateForm');
		$form->onSuccess[] = callback($this, 'processSubmit');

		return $form;
	}


	/**
	 * VALIDATE-FORM
	 * @param Form $form
	 */
	public function validateForm(Form $form)
	{
		$values = $form->getValues();

		// Validate old password
		if ($this->userEntity->password !== $this->userManager->calculateHash($values->oldPassword, $this->userEntity->password)) {
			$form->addError('Heslo ktoré ste udali nie je správne.');

			if ($this->presenter->isAjax()) {
				$this->invalidateControl();
			}
		}
	}


	/**
	 * PROCESS-SUBMIT-FORM - change password
	 * @param Form $form
	 */
	public function processSubmit(Form $form)
	{
		$values = $form->getValues();
		try {
			$passwordHash = $this->userManager->calculateHash($values->newPassword);
			$this->userEntity->update(array('password' => $passwordHash));
			$this->presenter->flashMessage('Vaše heslo bolo úspešne zmenené.', 'success');
			$this->logger->message(
				vsprintf("Zmena hesla používateľa: %s , ID: %s", array($this->userEntity->email, $this->userEntity->id)),
				'USER',
				Logger::INFO,
				$values->newPassword
			);
		} catch (\Exception $e) {
			$this->flashMessage('Nastala neočakávaná chyba, kontaktujte prosím administrátora.', 'error');
			$this->logger->message($e, 'USER', Logger::ERROR, $values);
		}
		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		} else {
			$this->redirect('this');
		}
	}


	/**
	 * RENDER
	 */
	public function render()
	{
		parent::render();
	}
}


/**
 * IUserPasswordChangeFormControl
 */
interface IUserPasswordChangeFormControl
{

	/**
	 * @return UserPasswordChangeFormControl
	 * @param ActiveRow | bool $userEntity
	 */
	function create($userEntity);
}
