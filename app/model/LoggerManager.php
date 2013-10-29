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


use NasExt\Logger\ILoggerRepository;
use Nette\Database\SelectionFactory;
use Nette\Database\Table\ActiveRow;
use Nette\Http\IRequest;
use Nette\Security\User;

/**
 * LoggerManager
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class LoggerManager extends BaseManager implements ILoggerRepository
{
	/** @var string */
	private $appDir;

	/** @var  User */
	private $user;

	/** @var  IRequest */
	private $httpRequest;


	/**
	 * CONSTRUCT
	 * @param string $appDir
	 * @param User $user
	 * @param IRequest $httpRequest
	 * @param SelectionFactory $selectionFactory
	 */
	public function __construct(
		$appDir,
		User $user,
		IRequest $httpRequest,
		SelectionFactory $selectionFactory
	)
	{
		parent::__construct($selectionFactory);
		$this->appDir = $appDir;
		$this->user = $user;
		$this->httpRequest = $httpRequest;
	}


	/**
	 * save
	 * @param string $message
	 * @param string $exception
	 * @param string $exceptionFilename
	 * @param string $identifer
	 * @param int $priority
	 * @param string $args
	 * @return ActiveRow
	 */
	public function save(
		$message,
		$exception = NULL,
		$exceptionFilename = NULL,
		$identifer = NULL,
		$priority = NULL,
		$args = NULL
	)
	{
		$logEntityData = array(
			'user_id' => $this->user->getId(),
			'ip' => $this->httpRequest->getRemoteAddress(),
			'datetime' => new \Nette\DateTime(),
			'priority' => $priority,
			'exception' => $exception,
			'exceptionFilename' => $exceptionFilename,
			'message' => $message,
			'identifer' => $identifer,
			'args' => $args,
			'url' => $this->httpRequest->getUrl()->absoluteUrl,
		);
		$logEntity = $this->getTable()->insert($logEntityData);
		return $logEntity;
	}


	/**
	 * Delete log record
	 * @param array|NULL $where
	 */
	public function deleteLog($where = NULL)
	{
		$deleteLog = $this->findAll();
		if ($where != NULL) {
			$deleteLog->where((array)$where);
		}
		foreach ($deleteLog as $log) {
			if ($log->exceptionFilename != NULL) {
				if ($this->checkLogUseExceptionFilename($log->id, $log->exceptionFilename) == 0) {
					if (is_file($log->exceptionFilename)) {
						unlink($log->exceptionFilename);
					}
				}
			}
			$deleteLog->delete();
		}
	}


	/**
	 * checkLogUseExceptionFilename
	 * @param int $id
	 * @param string $exceptionFilename
	 * @return int
	 */
	private function checkLogUseExceptionFilename($id, $exceptionFilename)
	{
		$check = $this->findAll()->where('exceptionFilename', $exceptionFilename);
		$check->where('id!= ?', $id);
		return $check->count();
	}


	/**
	 * getIdentiferList
	 * @return array
	 */
	public function getIdentiferList()
	{
		$identiferList = $this->findAll()->group('identifer')->order('identifer ASC');
		return $identiferList->fetchPairs('identifer', 'identifer');
	}
}
