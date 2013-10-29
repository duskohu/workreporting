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

use Nette\Application\UI\Presenter;
use stekycz\Cronner\Cronner;

/**
 * Cron Presenter
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class CronPresenter extends Presenter
{
	/** @var Cronner */
	private $cronner;

	/** @var  ReportCheckerTask */
	private $reportCheckerTask;


	/**
	 * INJECT Cronner
	 * @param Cronner $cronner
	 */
	public function injectCronner(Cronner $cronner)
	{
		$this->cronner = $cronner;
	}


	/**
	 * INJECT ReportCheckerTask
	 * @param ReportCheckerTask $reportCheckerTask
	 */
	public function injectReportCheckerTask(ReportCheckerTask $reportCheckerTask)
	{
		$this->reportCheckerTask = $reportCheckerTask;
	}


	/**
	 * ACTION - Default
	 */
	public function actionDefault()
	{
		$this->cronner->addTasks($this->reportCheckerTask);

		$this->cronner->run();
		$this->terminate();
	}
}
