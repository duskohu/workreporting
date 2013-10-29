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
use Nette\Application\UI\Form;
use Nette\Application\UI\Presenter;
use Nette\Http\IRequest;
use Nette\Http\IResponse;

/**
 * Items per page form control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class ItemsPerPageFormControl extends BaseFormControl
{
	/** @persistent */
	public $ipp = 10;

	/** @var  bool */
	public $showSubmit;

	/** @var string */
	public $inputLabel = 'Počet záznamov na stránke';

	/** @ array*/
	private $perPageData = array(2, 5, 10, 20, 30, 50, 100);

	/** @var string */
	private $cookieMask = "-ipp";

	/** @var  IRequest */
	private $httpRequest;

	/** @var  IResponse */
	private $httpResponse;


	/**
	 * CONSTRUCT
	 * @param IRequest $httpRequest
	 * @param IResponse $httpResponse
	 */
	public function __construct(IRequest $httpRequest, IResponse $httpResponse)
	{
		$this->httpResponse = $httpResponse;
		$this->httpRequest = $httpRequest;
	}


	/**
	 * This method will be called when the component (or component's parent)
	 * becomes attached to a monitored object. Do not call this method yourself.
	 * @param  Nette\ComponentModel\IComponent
	 * @return void
	 */
	protected function attached($presenter)
	{
		if ($presenter instanceof Presenter) {
			$this->cookieMask = $this->presenter->name . ":" . $this->name . $this->cookieMask;
			$this->ipp = $this->httpRequest->getCookie($this->cookieMask, $this->ipp);
		}
		parent::attached($presenter);
	}


	/**
	 * This method return array list of items per page
	 * @return array
	 */
	public function getPerPageData()
	{
		$perPageData = array();
		foreach ($this->perPageData as $value) {
			$perPageData[$value] = $value;
		}
		$perPageData[$this->ipp] = $this->ipp;
		ksort($perPageData);

		return $perPageData;
	}


	/**
	 * setPerPageData
	 * @param array $perPageData
	 * @return ItemsPerPageFormControl provides fluent interface
	 */
	public function setPerPageData($perPageData = NULL)
	{
		if ($perPageData != NULL) {
			$this->perPageData = $perPageData;
		}
		return $this;
	}


	/**
	 * FORM Items Per Page
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;
		$form->setRenderer(new BootstrapRenderer());
		$elementPrototype = $form->getElementPrototype();
		$elementPrototype->class[] = lcfirst($this->reflection->getShortName());
		$form->addSelect('itemsPerPage', $this->inputLabel, $this->getPerPageData())
			->setAttribute('data-items-per-page')
			->setDefaultValue($this->ipp);

		if ($this->showSubmit) {
			$form->addSubmit('change', 'Ok');
		}
		$form->onSuccess[] = callback($this, 'processSubmit');

		return $form;
	}


	/**
	 * PROCESS-SUBMIT-FORM - save item pre page to cookie storage
	 * @param Form $form
	 */
	public function processSubmit(Form $form)
	{
		$values = $form->getValues();
		$this->ipp = $values->itemsPerPage;
		$this->httpResponse->setCookie($this->cookieMask, $values->itemsPerPage, 0);
		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		} else {
			$this->redirect('this');
		}
	}


	/**
	 * RENDER
	 */
	public function render()
	{
		$this->template->ajaxRequest = $this->ajaxRequest;
		$this->template->render();
	}
}


/**
 * IItemsPerPageFormControl
 */
interface IItemsPerPageFormControl
{

	/**
	 * @return ItemsPerPageFormControl
	 */
	public function create();
}
