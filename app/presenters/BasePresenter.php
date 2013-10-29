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
use Nette\Application\UI\Presenter;
use Nette\DateTime;


/**
 * Base Presenter
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
abstract class BasePresenter extends Presenter
{
	/** @var  NasConfigStorage */
	public $nasConfigStorage;

	/** @var Logger $logger */
	protected $logger;


	/**
	 * INJECT NasConfigStorage
	 * @param NasConfigStorage $nasConfigStorage
	 */
	public function injectNasConfigStorage(NasConfigStorage $nasConfigStorage)
	{
		$this->nasConfigStorage = $nasConfigStorage;
	}


	/**
	 * INJECT Logger
	 * @param Logger $logger
	 */
	public function injectLogger(Logger $logger)
	{
		$this->logger = $logger;
	}


	/**
	 * STARTUP
	 */
	public function startup()
	{
		parent::startup();

		// Set config data to template
		$this->template->nasConfigStorage = $this->nasConfigStorage;
	}


	/**
	 * BEFORE RENDER
	 */
	protected function beforeRender()
	{
		parent::beforeRender();

		// ajax flashMessages
		if ($this->isAjax()) {
			$this->invalidateControl('flashMessages');
		}
	}


	/**
	 * CONTROL - RefreshLoginControl
	 * @param string $name
	 * @return RefreshLoginControl
	 */
	protected function createComponentRefreshLogin($name)
	{
		$control = new RefreshLoginControl($this, $name);
		return $control;
	}
}
