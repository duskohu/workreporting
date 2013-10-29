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
use Nette\Http\IRequest;
use Nette\Latte\Engine;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Templating\FileTemplate;


/**
 * Reset password form control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class ResetPasswordFormControl extends BaseFormControl
{
	/** @var Logger $logger */
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
	 * FORM Reset password
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;
		$elementPrototype = $form->getElementPrototype();
		$elementPrototype->class[] = lcfirst($this->reflection->getShortName());

		$form->addText('email', 'Email')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->addRule($form::EMAIL, 'Je nutné zadať platnú emailovú adresu!')
			->setAttribute("placeholder", "Váš email");

		$form->addSubmit('reset', 'Získať nové heslo');
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

		// Valid if user email exist
		$userEntity = $this->userManager->findByEmail($values->email);
		if ($userEntity == FALSE) {
			$form->addError('Užívateľ s timto emailom tu nie je zaregistrovaný.');
			$this->logger->message("Užívateľ s títo emailom tu nie je zaregistrovaný", 'USER', Logger::INFO, $values);
			if($this->presenter->isAjax()){
				$this->invalidateControl();
			}
		}
	}


	/**
	 * PROCESS-SUBMIT-FORM
	 * Save new user
	 * @param Form $form
	 */
	public function processSubmit(Form $form)
	{
		$values = $form->getValues();
		$resetError = FALSE;

		try {
			$userEntity = $this->userManager->findByEmail($values->email);
			$reset = $this->userManager->resetPassword($userEntity->id);
			if ($reset != FALSE) {
				// Send new password
				$template = new FileTemplate($this->nasConfigStorage->templatesDir . '/emails/newUser.latte');
				$template->registerFilter(new Engine());

				$template->baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
				$template->username = $userEntity->email;
				$template->password = $reset;
				$template->nasConfigStorage = $this->nasConfigStorage;

				$msg = new Message();
				$msg->setHtmlBody($template)
					->setFrom($this->nasConfigStorage->applicationName . " <" . $this->nasConfigStorage->applicationEmail . ">")
					->setSubject('Nové heslo - ' . $this->nasConfigStorage->applicationName)
					->addTo($userEntity->email);
				$this->mailer->send($msg);
			}
			$this->presenter->flashMessage('Na váš email vám bolo zaslané nové heslo', 'success');
			$this->logger->message(
				vsprintf("Obnovenie hesla pre: %s , ID: %s", array($userEntity->email, $userEntity->id)),
				'USER',
				Logger::WARNING,
				$reset
			);
		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nastala neočakávaná chyba, kontaktujte prosím administrátora.', 'error');
			$this->logger->message($e, 'USER', Logger::ERROR, $reset);
			$resetError = TRUE;
		}
		if (!$resetError) {
			$this->presenter->redirect('default');
		}

		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
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
 * IResetPasswordFormControl
 */
interface IResetPasswordFormControl
{

	/** @return ResetPasswordFormControl */
	function create();
}
