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
use Nette\Database\SqlLiteral;
use Nette\Database\Table\ActiveRow;
use Nette\Http\IRequest;
use Nette\Latte\Engine;
use Nette\Mail\IMailer;
use Nette\Mail\Message;
use Nette\Object;
use Nette\Templating\FileTemplate;


/**
 * ReportCheckerTask
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class ReportCheckerTask extends Object
{
	/** @var UserManager */
	private $userManager;

	/** @var  ReportManager */
	private $reportManager;

	/** @var Logger */
	protected $logger;

	/** @var IMailer */
	private $mailer;

	/** @var  array */
	private $ignoreList = array();

	/** @var  NasConfigStorage */
	private $nasConfigStorage;

	/** @var  IRequest */
	private $httpRequest;


	/**
	 * CONSTRUCT
	 * @param UserManager $userManager
	 * @param ReportManager $reportManager
	 * @param Logger $logger
	 * @param IMailer $mailer
	 * @param NasConfigStorage $nasConfigStorage
	 * @param IRequest $httpRequest
	 */
	public function __construct(
		UserManager $userManager,
		ReportManager $reportManager,
		Logger $logger,
		IMailer $mailer,
		NasConfigStorage $nasConfigStorage,
		IRequest $httpRequest
	)
	{
		$this->userManager = $userManager;
		$this->reportManager = $reportManager;
		$this->logger = $logger;
		$this->mailer = $mailer;
		$this->nasConfigStorage = $nasConfigStorage;
		$this->httpRequest = $httpRequest;
	}


	/**
	 * @param array $ignoreList
	 */
	public function setIgnoreList($ignoreList)
	{
		$this->ignoreList = $ignoreList;
	}


	/**
	 * @cronner-task Check user reports
	 * @cronner-time 00:01 - 24:59
	 */
	public function checkUserReports()
	{
		$userList = $this->userManager->findAll()
			->where('active', UserManager::ACTIVE);
		if ($this->ignoreList) {
			$userList->where('email NOT IN ?', $this->ignoreList);
		}

		foreach ($userList as $userEntity) {

			$userReport = $this->reportManager->findAll()
				->where('user_id', $userEntity->id)
				->where('DATE(dateAdded) = ?', new SqlLiteral('CURDATE()'));

			if ($userReport->count() == 0) {
				$this->sendNotificationMail($userEntity);
			}
		}
	}


	/**
	 * @param ActiveRow $userEntity
	 */
	private function sendNotificationMail($userEntity)
	{
		try {
			$template = new FileTemplate($this->nasConfigStorage->templatesDir . '/emails/reportNotification.latte');
			$template->registerFilter(new Engine());

			$template->baseUrl = rtrim($this->httpRequest->getUrl()->getBaseUrl(), '/');
			$template->nasConfigStorage = $this->nasConfigStorage;

			// Send email
			$msg = new Message();
			$msg->setHtmlBody($template)
				->setFrom($this->nasConfigStorage->applicationName . " <" . $this->nasConfigStorage->applicationEmail . ">")
				->setSubject('Request notifikácia - ' . $this->nasConfigStorage->applicationName)
				->addTo($userEntity->email);
			$this->mailer->send($msg);

			$this->logger->message(
				vsprintf("Request notifikácia EMAIL: %s , ID: %s bola úspešne odoslaná.", array($userEntity->email, $userEntity->id)),
				'CRON-REQUEST-NOTIFICATION',
				Logger::INFO,
				$userEntity->email
			);
		} catch (\Exception $e) {
			$this->logger->message($e, 'CRON-REQUEST-NOTIFICATION', Logger::ERROR, $userEntity->email);
		}
	}
}
