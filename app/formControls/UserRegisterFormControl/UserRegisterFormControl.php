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
use Nette\Database\Table\ActiveRow;
use Nette\Http\IRequest;
use Nette\Latte\Engine;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Templating\FileTemplate;

/**
 * User register form control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class UserRegisterFormControl extends BaseFormControl
{
	/** @var Logger */
	protected $logger;

	/** @var UserManager */
	private $userManager;

	/** @var IMailer */
	private $mailer;

	/** @var  IRequest */
	private $httpRequest;


	/**
	 * CONSTRUCTOR
	 * @param UserManager $userManager
	 * @param IMailer $mailer
	 * @param Logger $logger
	 * @param IRequest $httpRequest
	 */
	public function __construct(
		UserManager $userManager,
		IMailer $mailer,
		Logger $logger,
		IRequest $httpRequest
	)
	{
		parent::__construct();
		$this->userManager = $userManager;
		$this->mailer = $mailer;
		$this->logger = $logger;
		$this->httpRequest = $httpRequest;
	}


	/**
	 * FORM User register
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;
		$elementPrototype = $form->getElementPrototype();
		$elementPrototype->class[] = lcfirst($this->reflection->getShortName());

		$form->addText("loginName", "Prihlasovacie meno")
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Prihlasovacie meno");

		$form->addText('email', 'Email', NULL, 30)
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Email");

		$form->addPassword("password", "Heslo")
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mať aspoň %d znakov!', 6)
			->setAttribute("placeholder", "Heslo");

		$form->addPassword("password2", "Heslo pre kontrolu")
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->addRule(Form::MIN_LENGTH, 'Heslo musí mať aspoň %d znakov!', 6)
			->addRule(Form::EQUAL, 'Hesla sa nezhodujú!', $form['password'])
			->setAttribute("placeholder", "Heslo pre kontrolu");

		$form->addSubmit('add', 'Registrovať');
		$form->onSuccess[] = callback($this, 'processSubmit');
		return $form;
	}


	/**
	 * PROCESS-SUBMIT-FORM - register
	 * @param Form $form
	 */
	public function processSubmit(Form $form)
	{
		$values = $form->getValues();
		$registrationError = FALSE;
		$password = $values['password'];

		try {
			$passwordHash = $this->userManager->calculateHash($password);

			$values['password'] = $passwordHash;
			$values['active'] = UserManager::ACTIVE;
			$userEntity = $this->userManager->addUser($values);

			$this->sendRegistrationMail($userEntity, $password);

			$this->presenter->flashMessage('Gratulujeme boli ste úspešne zaregistrovaný.', 'success');
			$this->logger->message(
				vsprintf("Pridanie užívateľa EMAIL: %s , ID: %s", array($userEntity->email, $userEntity->id)),
				'USER',
				Logger::INFO,
				array($values, $password)
			);
		} catch (\Exception $e) {
			if ($e->getCode() == 45001) {
				$this->presenter->flashMessage(vsprintf('Užívateľ s emailom: "%s" je už registrovaný!', array($values->email)), 'error');
				$this->logger->message($e, 'USER', Logger::INFO, array($values, $password));
			} elseif ($e->getCode() == 45002) {
				$this->presenter->flashMessage(vsprintf('Užívateľ s prihlasovacím menom: "%s" je už registrovaný!', array($values->loginName)), 'error');
				$this->logger->message($e, 'USER', Logger::INFO, array($values, $password));
			} elseif ($e->getCode() == 45003) {
				$this->presenter->flashMessage('Je nutné zadať platnú emailovú adresu!', 'error');
				$this->logger->message($e, 'USER', Logger::INFO, array($values, $password));
			} else {
				$this->presenter->flashMessage('Nastala neočakávaná chyba, kontaktujte prosím administrátora.', 'error');
				$this->logger->message($e, 'USER', Logger::ERROR, array($values, $password));
			}
			$registrationError = TRUE;
		}

		if (!$registrationError) {
			$this->presenter->redirect('default');
		}

		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}
	}


	/**
	 * Send Notification email to new user
	 * @param ActiveRow $userEntity
	 * @param string $password
	 */
	private
	function sendRegistrationMail($userEntity, $password)
	{
		try {
			$template = new FileTemplate($this->nasConfigStorage->templatesDir . '/emails/newUser.latte');
			$template->registerFilter(new Engine());

			$template->baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
			$template->username = $userEntity->email;
			$template->password = $password;
			$template->nasConfigStorage = $this->nasConfigStorage;

			// Send email
			$msg = new Message();
			$msg->setHtmlBody($template)
				->setFrom($this->nasConfigStorage->applicationName . " <" . $this->nasConfigStorage->applicationEmail . ">")
				->setSubject('Registrácia - ' . $this->nasConfigStorage->applicationName)
				->addTo($userEntity->email);
			$this->mailer->send($msg);

			$this->logger->message(
				vsprintf("Email pre nového užívateľa EMAIL: %s , ID: %s bol úspešne odoslaný.", array($userEntity->email, $userEntity->id)),
				'MAIL',
				Logger::INFO,
				array($userEntity->email, $password)
			);
		} catch (\Exception $e) {
			$this->logger->message($e, 'MAIL', Logger::ERROR, array($userEntity->email, $password));
			$this->presenter->flashMessage('Nastala neočakávaná chyba pri odosielaní prihlasovacích údajov na váš email, kontaktujte prosím administrátora.', 'error');
		}
	}


	/**
	 * RENDER
	 */
	public
	function render()
	{
		parent::render();
	}
}

/**
 * IUserRegisterFormControl
 */
interface IUserRegisterFormControl
{

	/** @return UserRegisterFormControl */
	function create();
}