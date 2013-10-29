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
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

use Arachne\SecurityAnnotations\LoggedIn;
use Arachne\SecurityAnnotations\Allowed;
use Arachne\SecurityAnnotations\InRole;


/**
 * ReportList Presenter
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 *
 * @LoggedIn
 */
class ReportListPresenter extends SecuredPresenter
{
	/** @var  ReportManager */
	private $reportManager;

	/** @var  IReportFormControl */
	private $reportFormControl;

	/** @var  IItemsPerPageFormControl */
	private $itemsPerPageFormControl;

	/** @var  ActiveRow */
	private $reportEntity;


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


	/**
	 * INJECT ItemsPerPageFormControl
	 * @param IItemsPerPageFormControl $itemsPerPageFormControl
	 */
	public function injectItemsPerPageFormControl(IItemsPerPageFormControl $itemsPerPageFormControl)
	{
		$this->itemsPerPageFormControl = $itemsPerPageFormControl;
	}


	/**
	 * RENDER DEFAULT
	 */
	public function renderDefault()
	{
		$this->template->pageTitle = 'Zoznam reportov';

		// Load data list
		$reportList = $this->reportManager->findAll();

		$user = $this->getUser();
		if(!$user->isInRole('superAdmin')){
			$reportList->where('user_id',$user->id );
		}

		$reportListTotalCount = $reportList->count();

		// Filter data list
		/** @var FilterFormControl $filter */
		$filter = $this['filter'];

		$filterData = $filter->getData();
		if (array_key_exists('date_from', $filterData)) {
			$reportList->where('reportDate >= ?', $filterData['date_from']);
		}
		if (array_key_exists('date_to', $filterData)) {
			$reportList->where('reportDate <= ?', $filterData['date_to']);
		}

		$reportListFoundCount = $reportList->count();

		// Sort data list
		/** @var OrderControl $order */
		$order = $this['order'];
		$orderData = array($order->column . " " . strtoupper($order->sort));
		$reportList->order(implode(",", $orderData));

		// Items in data list
		/** @var ItemsPerPageFormControl $itemsPerPage */
		$itemsPerPage = $this['itemsPerPage'];

		// Pagination
		/** @var VisualPaginator $vp */
		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = $itemsPerPage->ipp;
		$paginator->itemCount = $reportListFoundCount;
		$reportList->limit($paginator->itemsPerPage, $paginator->offset);

		$this->template->reportList = $reportList;
		$this->template->reportListTotalCount = $reportListTotalCount;
		$this->template->reportListFoundCount = $reportListFoundCount;
		$this->template->reportIssueUrl = $this->nasConfigStorage->reportIssueUrl;

		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}
	}


	/**
	 * ACTION DEFAULT
	 */
	public function actionExport()
	{
		// Load data list
		$reportList = $this->reportManager->findAll();

		$user = $this->getUser();
		if(!$user->isInRole('superAdmin')){
			$reportList->where('user_id',$user->id );
		}

		// Filter data list
		/** @var FilterFormControl $filter */
		$filter = $this['filter'];

		$filterData = $filter->getData();
		if (array_key_exists('date_from', $filterData)) {
			$reportList->where('reportDate >= ?', $filterData['date_from']);
		}
		if (array_key_exists('date_to', $filterData)) {
			$reportList->where('reportDate <= ?', $filterData['date_to']);
		}

		$headers = array(
			'Id',
			'Dátum',
			'Issue',
			'Poznámka',
			'Čas do konca tasku',
			'Stravený čas na tasku',
			'Splnenie tasku',
			'Pridané',
			'Aktualizované',
			'Používateľ',
		);

		$data = array();
		foreach ($reportList as $key => $value) {
			$data[$key][] = $value->id;
			$data[$key][] = $value->reportDate ? $value->reportDate->format("d.m.Y") : $value->reportDate;
			$data[$key][] = $value->idIssue;
			$data[$key][] = $value->description;
			$data[$key][] = $value->timeRequired;
			$data[$key][] = $value->timeSpend;
			$data[$key][] = $value->taskCompleted;
			$data[$key][] = $value->dateAdded ? $value->dateAdded->format("d.m.Y") : $value->dateAdded;
			$data[$key][] = $value->dateModified ? $value->dateModified->format("d.m.Y") : $value->dateModified;
			$data[$key][] = $value->user->loginName;
		}

		$this->sendResponse(new \CsvResponse($data, "export-" . date('Ymd-Hi') . ".csv", $headers));
	}


	/**
	 * ACTION - Edit
	 * @param int $id
	 * @throws BadRequestException
	 * @throws ForbiddenRequestException
	 */
	public function actionEdit($id)
	{
		$this->reportEntity = $this->reportManager->find($id);
		if ($this->reportEntity == FALSE) {
			$errorMessage = 'Vami požadovaná položka nebola nájdená. Je možné, že adresa je nesprávna, alebo že položka už neexistuje.';
			throw new BadRequestException($errorMessage);
		}

		if($this->reportEntity->user_id != $this->getUser()->id){
			throw new ForbiddenRequestException();
		}
	}


	/**
	 * RENDER - Edit
	 * @param int $id
	 */
	public function renderEdit($id)
	{
		$this->template->pageTitle = 'Report - detail';
	}


	/**
	 * CONTROL - Vp
	 * @param string $name
	 * @return VisualPaginator
	 */
	protected function createComponentVp($name)
	{
		$control = new VisualPaginator($this, $name);
		$control->isAjax = TRUE;
		return $control;
	}


	/**
	 * CONTROL - ItemsPerPage
	 * @return ItemsPerPageFormControl
	 */
	protected function createComponentItemsPerPage()
	{
		$control = $this->itemsPerPageFormControl->create();
		return $control;
	}


	/**
	 * CONTROL - Order
	 * @param string $name
	 * @return OrderControl
	 */
	protected function createComponentOrder($name)
	{
		$control = new OrderControl($this, $name, "reportDate", "desc");
		$control->setAttrs(array('class' => 'ajax'));
		return $control;
	}


	/**
	 * CONTROL - Filter
	 * @param $name
	 * @return FilterFormControl
	 */
	protected function createComponentFilter($name)
	{
		$setBeforeSetDataCallback = function (FilterFormControl $control, $values) {
			if (array_key_exists('date_from', $values) && $values['date_from'] != NULL) {
				$values['date_from'] = $values['date_from']->getTimestamp();
			}

			if (array_key_exists('date_to', $values) && $values['date_to'] != NULL) {
				$values['date_to'] = $values['date_to']->getTimestamp();
			}
			return $values;
		};

		$setAfterGetDataCallback = function (FilterFormControl $control, $values) {
			if (array_key_exists('date_from', $values) && $values['date_from'] != NULL) {
				$date = new \DateTime();
				$values['date_from'] = $date->setTimestamp($values['date_from']);
			}

			if (array_key_exists('date_to', $values) && $values['date_to'] != NULL) {
				$date = new \DateTime();
				$values['date_to'] = $date->setTimestamp($values['date_to']);
			}
			return $values;
		};

		$control = new FilterFormControl($this, $name, $setBeforeSetDataCallback, $setAfterGetDataCallback);
		$control->setNasConfigStorage($this->nasConfigStorage);
		$control->setFileTemplate($this->nasConfigStorage->templatesDir . '/components/formReportFilter.latte');

		/** @var Form $form */
		$form = $control['form'];
		$form->setRenderer(new BootstrapRenderer());

		$form->addDatePicker('date_from', 'Dátum od')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Dátum od");

		$form->addDatePicker('date_to', 'Dátum do')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Dátum do");

		$control->setDefaultValues(
			array(
				'date_from' => $this->getStartDate(),
				'date_to' => $this->getEndDate(),
			)
		);

		return $control;
	}


	/**
	 * CONTROL - EditReport
	 * @return ReportFormControl
	 */
	protected function createComponentEditReport()
	{
		$control = $this->reportFormControl->create();
		$control->setReportEntity($this->reportEntity);
		$control->setFileTemplate($this->nasConfigStorage->templatesDir . '/components/formEditReport.latte');
		return $control;
	}


	/**
	 * @return \DateTime
	 */
	private function getStartDate()
	{
		$date = new \DateTime();
		$date->modify('first day of this month')
			->setTime(00, 00);
		return $date;
	}


	/**
	 * @return \DateTime
	 */
	private function getEndDate()
	{
		$date = new \DateTime();
		$date->modify('last day of this month')
			->setTime(00, 00);
		return $date;
	}
}
