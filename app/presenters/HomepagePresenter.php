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

use Arachne\SecurityAnnotations\LoggedIn;
use Arachne\SecurityAnnotations\Allowed;
use Arachne\SecurityAnnotations\InRole;

/**
 * Homepage Presenter
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 *
 * @LoggedIn
 */
class HomepagePresenter extends SecuredPresenter
{
	/** @var  ReportManager */
	private $reportManager;

	/** @var  IReportFormControl */
	private $reportFormControl;


	/**
	 * INJECT ReportManager
	 * @param ReportManager $reportManager
	 */
	public function injectReportManager(ReportManager $reportManager)
	{
		$this->reportManager = $reportManager;
	}


	/**
	 * INJECT ReportFormControl
	 * @param IReportFormControl $reportFormControl
	 */
	public function injectReportFormControl(IReportFormControl $reportFormControl)
	{
		$this->reportFormControl = $reportFormControl;
	}


	public function renderDefault()
	{
		$this->template->pageTitle = 'Nový report';
	}


	/**
	 * CONTROL - AddReport
	 * @return ReportFormControl
	 */
	protected function createComponentAddReport()
	{
		$control = $this->reportFormControl->create();
		$control->setFileTemplate($this->nasConfigStorage->templatesDir.'/components/formAddReport.latte');
		return $control;
	}
}
