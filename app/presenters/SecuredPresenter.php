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

use Arachne\SecurityAnnotations\Exception\FailedAuthenticationException;
use Arachne\Verifier\Verifier;
use NasExt\Logger\Logger;
use Nette\Security\IUserStorage;

/**
 * Secured Presenter
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
abstract class SecuredPresenter extends BasePresenter
{

	/** @var Verifier */
	protected $verifier;


	/**
	 * @param Verifier $verifier
	 */
	final public function injectVerifier(Verifier $verifier)
	{
		$this->verifier = $verifier;
	}


	/**
	 * @param \Nette\Reflection\ClassType|\Nette\Reflection\Method $reflection
	 */
	final public function checkRequirements($reflection)
	{

		$user = $this->user;
		try {
			$this->verifier->checkAnnotations($reflection, $this->getRequest());
		} catch (FailedAuthenticationException $e) {
			if ($user->getLogoutReason() == IUserStorage::INACTIVITY) {
				$this->flashMessage('Uplynula doba neaktivity! Systém vás z bezpečnostných dôvodov odhlásil.', 'warning');
			} else {
				$this->flashMessage('Na vstup do tejto sekcie sa musíte prihlásiť!', 'warning');
				$this->logger->message('Na vstup do tejto sekcie sa musíte prihlásiť!', 'ERROR-403', Logger::WARNING, $this->getUser()->getIdentity());
			}
			$this->redirect(':Sign:default', array('backlink' => $this->storeRequest()));
		}
	}


	/**
	 * @param string $destination
	 * @param mixed[] $parameters
	 */
	protected function redirectVerified($destination, $parameters = array())
	{
		$link = $this->link($destination, $parameters);
		if ($this->verifier->isLinkAvailable($this->getLastCreatedRequest())) {
			$this->redirectUrl($link);
		}
	}
}
