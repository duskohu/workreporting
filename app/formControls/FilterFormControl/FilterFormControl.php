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

use Nette\Application\UI\Form;
use Nette\Callback;
use Nette\Database\Table\Selection;
use Nette\Forms\Controls\SubmitButton;


/**
 * Filter form control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class FilterFormControl extends BaseFormControl
{
	/** @persistent */
	public $data;

	/** @var callback returning array */
	protected $beforeSetDataCallback;

	/** @var callback returning array */
	protected $afterGetDataCallback;

	/** @var  bool|array */
	protected $filterByColumn;

	/** @var  string */
	protected $filterByColumnLabel;

	/** @var  string */
	protected $filterByColumnPrompt;

	/** @var  array */
	public $defaultsValues;


	/**
	 * @param \Nette\ComponentModel\IContainer $parent
	 * @param string $name
	 * @param $beforeSetDataCallback
	 * @param $afterGetDataCallback
	 */
	public function __construct($parent, $name, $beforeSetDataCallback = NULL, $afterGetDataCallback = NULL)
	{
		if ($beforeSetDataCallback) {
			$this->beforeSetDataCallback = new Callback($beforeSetDataCallback);
		}
		if ($afterGetDataCallback) {
			$this->afterGetDataCallback = new Callback($afterGetDataCallback);
		}

		parent::__construct($parent, $name);
	}


	/**
	 * @param array $values
	 */
	public function setDefaultValues($values)
	{
		$this->defaultsValues = $values;
	}


	/**
	 * setFilterByColumn
	 * @param array $filterByColumn
	 * @param null|string $label
	 * @param null|string $prompt
	 */
	public function setFilterByColumn($filterByColumn, $label = NULL, $prompt = NULL)
	{
		$this->filterByColumn = $filterByColumn;
		if ($label != NULL) {
			$this->filterByColumnLabel = $label;
		}
		if ($prompt !== NULL) {
			$this->filterByColumnPrompt = $prompt;
		}
	}


	/**
	 * setBeforeSetDataCallback
	 * @param  $callback
	 * @return FilterFormControl provides fluent interface
	 */
	public function setBeforeSetDataCallback($callback)
	{
		$this->beforeSetDataCallback = new Callback($callback);
		return $this;
	}


	/**
	 * setAfterGetDataCallback
	 * @param  $callback
	 * @return FilterFormControl provides fluent interface
	 */
	public function setAfterGetDataCallback($callback)
	{
		$this->afterGetDataCallback = new Callback($callback);
		return $this;
	}


	/**
	 * getData
	 * @return array $data
	 */
	public function getData()
	{
		$data = array();

		if ($this->data != NULL) {
			parse_str($this->data, $data);
			if ($this->afterGetDataCallback != NULL) {
				$data = $this->afterGetDataCallback->invoke($this, $data);
			}
		}

		if ($this->defaultsValues) {
			foreach ($this->defaultsValues as $item => $value) {
				if (array_key_exists($item, $data)) {
					if ($data[$item] == '') {
						$data[$item] = $value;
					}
				} else {
					$data[$item] = $value;
				}
			}
		}

		return $data;
	}


	/**
	 * setData
	 * @param array|string $data
	 * @return FilterFormControl provides fluent interface
	 */
	public function setData($data)
	{
		if ($data == NULL) {
			$this->data = NULL;
			return $this;
		}
		if ($this->beforeSetDataCallback != NULL) {
			$data = $this->beforeSetDataCallback->invoke($this, $data);
		}

		$filter = array();
		foreach ($data as $key => $value) {

			if ($value !== "") {
				$filter[$key] = $value;
			}
		}

		if (!empty($filter)) {
			$this->data = http_build_query($filter, '', '&');
		} else {
			$this->data = NULL;
		}
		return $this;
	}


	/**
	 * @param array $filterData
	 * @param Selection $dataList
	 * @return Selection
	 */
	public function processData($filterData, $dataList)
	{
		if ($filterData != NULL) {
			// Search in all columns
			if (array_key_exists('text_search', $filterData) && !array_key_exists('filter_column', $filterData)) {
				$allColumns = array();
				foreach ($this->filterByColumn as $filerColumns) {
					$allColumns = array_merge($allColumns, $filerColumns['columns']);
				}
				$columns = array_map(
					function ($column) {
						return ($column . " LIKE ? ");
					},
					$allColumns
				);
				$values = array_fill(0, count($columns), "%" . $filterData['text_search'] . "%");
				if (count($values) == 1) {
					$values = $values[0];
				}
				$dataList->where(implode(' OR ', $columns), $values);
			} elseif (array_key_exists('text_search', $filterData) && array_key_exists('filter_column', $filterData)) {
				$columns = array_map(
					function ($column) {
						return ($column . " LIKE ? ");
					},
					$this->filterByColumn[$filterData['filter_column']]['columns']
				);
				$values = array_fill(0, count($columns), "%" . $filterData['text_search'] . "%");
				if (count($values) == 1) {
					$values = $values[0];
				}
				$dataList->where(implode(' OR ', $columns), $values);
			}
			unset($filterData['text_search']);
			unset($filterData['filter_column']);

			foreach ($filterData as $key => $value) {
				$dataList->where($key, $value);
			}
		}
		return $dataList;
	}


	/**
	 * FORM Filter
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form();
		$elementPrototype = $form->getElementPrototype();
		$elementPrototype->class[] = lcfirst($this->reflection->getShortName());

		if ($this->filterByColumn) {
			$form->addText('text_search', 'Hľadaný výraz')
				->setAttribute('placeholder', 'Hľadaný výraz');

			$filerColumns = array();
			foreach ($this->filterByColumn as $id => $value) {
				$filerColumns[$id] = $value['label'];
			}
			$form->addSelect('filter_column', $this->filterByColumnLabel ? $this->filterByColumnLabel : 'Hľadať v stĺpci', $filerColumns)
				->setPrompt($this->filterByColumnPrompt ? $this->filterByColumnPrompt : '- Všade -');
		}

		$form->addSubmit('filter', 'Filter')
			->onClick[] = callback($this, 'processSubmit');

		$form->addSubmit('reset', 'Reset')
			->setValidationScope(FALSE)
			->onClick[] = callback($this, 'processReset');


		return $form;
	}


	/**
	 * PROCESS-SUBMIT-FORM - set filter data to persistent value
	 * @param SubmitButton $button
	 */
	public function processSubmit(SubmitButton $button)
	{
		$form = $button->getForm();
		$values = $form->getValues();
		$this->setData($values);

		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		} else {
			$this->redirect('this');
		}
	}


	/**
	 * PROCESS-RESET-FORM - delete filter data from persistent value
	 * @param SubmitButton $button
	 */
	public function processReset(SubmitButton $button)
	{
		$this->setData(NULL);
		$form = $button->getForm();
		$values = $form->getValues();

		foreach ($values as $name => $value) {
			$form[$name]->setValue(NULL);
		}

		if ($this->presenter->isAjax()) {
			$this->invalidateControl();
		} else {
			$this->redirect('this');
		}
	}


	/**
	 * @param Form $form
	 */
	private function setFormData()
	{
		/** @var Form $form */
		$form = $this['form'];

		$values = $form->getValues();
		$data = $this->getData();

		foreach ($values as $name => $value) {
			if ($value == '' || $value == NULL) {
				if (array_key_exists($name, $data)) {
					$form[$name]->setValue($data[$name]);
				}
			}
		}
	}


	/**
	 * RENDER
	 */
	public function render()
	{
		$this->setFormData();
		parent::render();
	}
}
