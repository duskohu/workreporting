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

use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;

use Arachne\SecurityAnnotations\LoggedIn;
use Arachne\SecurityAnnotations\Allowed;
use Arachne\SecurityAnnotations\InRole;

/**
 * Logger Presenter
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 *
 * @LoggedIn
 * @InRole("superAdmin")
 */
class LoggerPresenter extends SecuredPresenter
{
	/** @var LoggerManager */
	private $loggerManager;

	/** @var ActiveRow */
	private $logEntity;

	/** @var  IItemsPerPageFormControl */
	private $itemsPerPageFormControl;

	/** @var  array */
	private $loggerPriorityList;

	/** @var array columns for filter */
	private $filerColumns = array(
		'message' => array(
			'label' => 'Správa',
			'columns' => array('message')
		),
		'ip' => array(
			'label' => 'Ip adresa',
			'columns' => array('ip')
		),
		'exception' => array(
			'label' => 'Exception',
			'columns' => array('exception')
		),
		'args' => array(
			'label' => 'Argumenty',
			'columns' => array('args')
		),
		'url' => array(
			'label' => 'Url',
			'columns' => array('url')
		),
		'user' => array(
			'label' => 'Používateľ::meno, priezvisko,email,id',
			'columns' => array('user.loginName', 'user.id', 'user.email')
		),
	);


	/**
	 * INJECT LoggerManager
	 * @param LoggerManager $loggerManager
	 */
	public function injectLoggerManager(LoggerManager $loggerManager)
	{
		$this->loggerManager = $loggerManager;
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
	 * STARTUP
	 */
	public function startup()
	{
		parent::startup();
		$this->loggerPriorityList = $this->getLoggerPriorityList();
	}


	/**
	 * RENDER - Default
	 */
	public function renderDefault()
	{
		$this->template->pageTitle = 'Logger';

		// Load data list
		$logList = $this->loggerManager->findAll();
		$logListTotalCount = $logList->count();

		// Filter data list
		/** @var FilterFormControl $filter */
		$filter = $this['filter'];
		$logList = $filter->processData($filter->getData(), $logList);

		$logListFoundCount = $logList->count();

		// Sort data list
		/** @var OrderControl $order */
		$order = $this['order'];
		$orderData = array($order->column . " " . strtoupper($order->sort));
		$logList->order(implode(",", $orderData));

		// Items in data list
		/** @var ItemsPerPageFormControl $itemsPerPage */
		$itemsPerPage = $this['itemsPerPage'];

		// Pagination
		/** @var VisualPaginator $vp */
		$vp = $this['vp'];
		$paginator = $vp->getPaginator();
		$paginator->itemsPerPage = $itemsPerPage->ipp;
		$paginator->itemCount = $logListFoundCount;
		$logList->limit($paginator->itemsPerPage, $paginator->offset);

		$this->template->logList = $logList;
		$this->template->logListTotalCount = $logListTotalCount;
		$this->template->logListFoundCount = $logListFoundCount;
		$this->template->priorityList = $this->loggerPriorityList;

		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		}
	}


	/**
	 * ACTION - Show
	 * @param int $id
	 * @throws \Nette\Application\BadRequestException
	 */
	public function actionShow($id)
	{
		$this->logEntity = $this->loggerManager->find($id);
		if ($this->logEntity === FALSE) {
			throw new BadRequestException;
		}
	}


	/**
	 * RENDER - Show
	 * @param int $id
	 */
	public function renderShow($id)
	{
		$this->template->pageTitle = 'Log detail';
		$this->template->pageTitleAdd = $id;
		$this->template->logEntity = $this->logEntity;
		$this->template->priorityList = $this->loggerPriorityList;
	}


	/**
	 * ACTION - LogRecord
	 * @param int $id
	 * @throws BadRequestException
	 */
	public function actionLogRecord($id)
	{
		$this->logEntity = $this->loggerManager->find($id);
		if ($this->logEntity === FALSE || !$this->logEntity->exceptionFilename) {
			throw new BadRequestException;
		}
	}


	/**
	 * RENDER - LogRecord
	 * @param int $id
	 */
	public function renderLogRecord($id)
	{
		$this->template->exceptionFilename = $this->logEntity->exceptionFilename;
	}


	/**
	 * HANDLE - Log delete
	 */
	public function handleLogDelete()
	{
		$this->logEntity->delete();
		$this->flashMessage('Log bol úspešne vymazaný.', 'success');
		$this->redirect('default');
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
		$control = new OrderControl($this, $name, "datetime", "desc");
		$control->setAttrs(array('class' => 'ajax'));
		return $control;
	}


	/**
	 * CONTROL - Filter
	 * @param string $name
	 * @return FilterFormControl
	 */
	protected function createComponentFilter($name)
	{
		$control = new FilterFormControl($this, $name);
		$control->setFilterByColumn($this->filerColumns);
		$control->setNasConfigStorage($this->nasConfigStorage);

		/** @var  Form $form */
		$form = $control['form'];
		$form->addSelect('priority', 'Priorita', $this->loggerPriorityList)
			->setPrompt('- Zvoliť -');

		$form->addSelect('identifer', 'Identifikátor', $this->loggerManager->getIdentiferList())
			->setPrompt('- Zvoliť -');

		return $control;
	}


	/**
	 * FORM - Delete log records
	 * @param string $name
	 * @return BaseFormControl
	 */
	protected function createComponentDeleteLogForm($name)
	{
		$control = new BaseFormControl();
		$control->setNasConfigStorage($this->nasConfigStorage);

		$form = new Form;
		$control->addComponent($form, 'form');

		$form->addSelect('priority', 'Priorita', $this->loggerPriorityList)
			->setPrompt('- Zvoliť -');

		$form->addSelect('identifer', 'Identifikátor', $this->loggerManager->getIdentiferList())
			->setPrompt('- Zvoliť -');

		$form->addSubmit('delete', 'Vymazať')
			->setAttribute('class', 'btn-danger btn')
			->setAttribute('data-confirm', 'modal')
			->setAttribute('data-confirm-title', 'Potvrdiť')
			->setAttribute('data-confirm-text', 'Skutočne chcete vymazať vybrané záznamy?')
			->setAttribute('data-confirm-ok-class', 'btn-danger')
			->setAttribute('data-confirm-ok-text', 'Vymazať')
			->setAttribute('data-confirm-cancel-class', 'btn-success')
			->setAttribute('data-confirm-cancel-text', 'Zrušiť');

		$form->addCheckbox('deleteAll', 'Vymazať všetky logy');

		$form->onSuccess[] = callback($this, 'processDeleteSubmit');

		return $control;
	}


	/**
	 * PROCESS-DELETE-SUBMIT-FORM - delete log records
	 * @param Form $form
	 */
	public function processDeleteSubmit(Form $form)
	{
		$values = $form->getValues();

		try {
			if ($values['deleteAll'] == TRUE) {
				$this->loggerManager->deleteLog();
			} else {
				unset($values['deleteAll']);

				foreach ($values as $key => $value) {
					if ($value == NULL) {
						unset($values[$key]);
					}
				}

				if (count($values) > 0) {
					$this->loggerManager->deleteLog($values);
				}
			}
			$this->flashMessage('Vybrané záznamy boli úspešne vymazané.', 'success');
		} catch (\Exception $e) {
			$this->flashMessage('Nastala neočakávaná chyba, kontaktujte prosím administrátora.', 'error');
			$this->logger->message($e, 'LOG', \NasExt\Logger\Logger::ERROR, $values);
		}
		$this->redirect('default');
	}


	/**
	 * @return array
	 */
	private function getLoggerPriorityList()
	{
		$reflection = $this->logger->getReflection();
		$priorityConst = $reflection->getConstants();
		$priority = array_flip($priorityConst);
		return $priority;
	}
}
