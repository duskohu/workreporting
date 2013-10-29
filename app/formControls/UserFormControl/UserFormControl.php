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
use Nette\Forms\Controls\SubmitButton;

/**
 * User form control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class UserFormControl extends BaseFormControl
{
	/** @var Logger $logger */
	protected $logger;

	/** @var UserManager */
	private $userManager;

	/** @var ActiveRow */
	private $userEntity;


	/**CONSTRUCTOR
	 * @param Logger $logger
	 * @param UserManager $userManager
	 */
	public function __construct(Logger $logger, UserManager $userManager)
	{
		parent::__construct();
		$this->userManager = $userManager;
		$this->logger = $logger;
	}


	/**
	 * setUserEntity
	 * @param bool|ActiveRow $userEntity
	 * @return UserFormControl provides fluent interface
	 */
	public function setUserEntity($userEntity = FALSE)
	{
		$this->userEntity = $userEntity;
		return $this;
	}


	/**
	 * FORM User
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer(new BootstrapRenderer());
		$elementPrototype = $form->getElementPrototype();
		$elementPrototype->class[] = lcfirst($this->reflection->getShortName());

		$form->addText("loginName", "Prihlasovacie meno")
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Prihlasovacie meno");

		$form->addText('email', 'Email')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Email");

		if ($this->userEntity) {
			$form["loginName"]->setDefaultValue($this->userEntity->loginName);
			$form["email"]->setDefaultValue($this->userEntity->email);

			$form->addSubmit('save', 'Uložiť')
				->onClick[] = callback($this, 'processEditSubmit');
		}
		return $form;
	}


	/**
	 * PROCESS-EDIT-SUBMIT-FORM - edit user
	 * @param SubmitButton $button
	 */
	public function processEditSubmit(SubmitButton $button)
	{
		$values = $button->getForm()->getValues();

		try {
			$userEntity = $this->userManager->editUser($this->userEntity->id, $values);
			$this->presenter->flashMessage('Vaše údaje boli úspešne uložené.', 'success');
			$this->logger->message(
				vsprintf("Aktualizácia používateľa EMAIL: %s, ID: %s ", array($userEntity->email, $userEntity->id)),
				'USER', Logger::INFO,
				$values
			);
		} catch (\Exception $e) {
			if ($e->getCode() == 45001) {
				$this->presenter->flashMessage(vsprintf('Užívateľ s emailom: "%s" je už registrovaný!', array($values->email)), 'error');
				$this->logger->message($e, 'USER', Logger::INFO, $values);
			} elseif ($e->getCode() == 45002) {
				$this->presenter->flashMessage(vsprintf('Užívateľ s prihlasovacím menom: "%s" je už registrovaný!', array($values->loginName)), 'error');
				$this->logger->message($e, 'USER', Logger::INFO, $values);
			} elseif ($e->getCode() == 45003) {
				$this->presenter->flashMessage('Je nutné zadať platnú emailovú adresu!', 'error');
				$this->logger->message($e, 'USER', Logger::INFO, $values);
			} else {
				$this->presenter->flashMessage('Nastala neočakávaná chyba, kontaktujte prosím administrátora.', 'error');
				$this->logger->message($e, 'USER', Logger::ERROR, $values);
			}
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
 * IUserFormControl
 */
interface IUserFormControl
{

	/**
	 * @return UserFormControl
	 */
	function create();
}
