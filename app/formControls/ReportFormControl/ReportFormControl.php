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
use NasExt\Logger\Logger;
use Nette\Application\UI\Form;
use Nette\Database\Table\ActiveRow;
use Nette\DateTime;
use Nette\Forms\Controls\SelectBox;
use Nette\Forms\Controls\SubmitButton;
use Nette\Utils\Json;

/**
 * Report form control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class ReportFormControl extends BaseFormControl
{
	/** @var Logger $logger */
	protected $logger;

	/** @var ReportManager */
	private $reportManager;

	/** @var ActiveRow */
	private $reportEntity;

	/** @var  string */
	private $nowYear;

	/** @var  string */
	private $nowMonth;

	/** @var  string */
	private $nowDay;


	/**CONSTRUCTOR
	 * @param Logger $logger
	 * @param ReportManager $reportManager
	 */
	public function __construct(Logger $logger, ReportManager $reportManager)
	{
		parent::__construct();
		$this->reportManager = $reportManager;
		$this->logger = $logger;

		$this->nowYear = date('Y');
		$this->nowMonth = date('m');
		$this->nowDay = date('d');
	}


	/**
	 * setUserEntity
	 * @param bool|ActiveRow $reportEntity
	 * @return ReportFormControl provides fluent interface
	 */
	public function setReportEntity($reportEntity = FALSE)
	{
		$this->reportEntity = $reportEntity;
		return $this;
	}


	/**
	 * @return array
	 */
	private function getYearList()
	{
		$yearList = range(2000, date("Y"));
		return array_combine($yearList, $yearList);
	}


	/**
	 * @return array
	 */
	private function getMonthList()
	{
		$monthList = range(1, 12);
		return array_combine($monthList, $monthList);
	}


	/**
	 * @param $year
	 * @param $month
	 * @return array
	 */
	private function getDayList($year, $month)
	{
		$dayList = range(1, cal_days_in_month(CAL_GREGORIAN, $month, $year));
		return array_combine($dayList, $dayList);
	}


	/**
	 * HANDLE - DependentDay
	 * @param int $year
	 * @param int $month
	 * @param string $callerElement
	 */
	public function handleDependentDay($year, $month, $callerElement)
	{
		/** @var Form $form */
		$form = $this['form'];

		/** @var SelectBox $yearInput */
		$yearInput = $form['year'];

		/** @var SelectBox $monthInput */
		$monthInput = $form['month'];

		/** @var SelectBox $dayInput */
		$dayInput = $form['day'];

		if ($callerElement == $yearInput->getName()) {
			if ($year) {
				$yearInput->setDefaultValue($year);
				$monthInput->setDefaultValue(NULL);
				$dayInput->setDefaultValue(NULL);
				$dayInput->setItems(array());
			} else {
				$yearInput->setDefaultValue(NULL);
				$monthInput->setItems(array());
				$dayInput->setItems(array());
			}
		}

		if ($callerElement == $monthInput->getName()) {
			if ($year) {
				$yearInput->setDefaultValue($year);
				$monthInput->setDefaultValue($month);
				$dayList = $this->getDayList($year, $month);
				$dayInput->setItems($dayList);
				$dayInput->setDefaultValue(NULL);
			} else {
				$dayInput->setItems(array());
			}
		}

		$this->invalidateControl('dateGroup');
	}


	/**
	 * Set defaults feorm values
	 * @param Form $form
	 * @param null|int $year
	 * @param null|int $month
	 * @param null|int $day
	 */
	private function setDefaultDateValues(Form $form, $year = NULL, $month = NULL, $day = NULL)
	{
		$form["year"]->setItems($this->getYearList());
		$form["year"]->setDefaultValue($year ? $year : $this->nowYear);

		$form["month"]->setItems($this->getMonthList());
		$form["month"]->setDefaultValue($month ? $month : $this->nowMonth);

		$form["day"]->setItems($this->getDayList($year ? $year : $this->nowYear, $month ? $month : $this->nowMonth));
		$form["day"]->setDefaultValue($day ? $day : $this->nowDay);
	}


	/**
	 * FORM User
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;
		$elementPrototype = $form->getElementPrototype();
		$elementPrototype->class[] = lcfirst($this->reflection->getShortName());
		$form->setRenderer(new BootstrapRenderer());

		$form->addSelect("year", "Rok")
			->setPrompt('- Zvoliť - ')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!');

		$form->addSelect("month", "Mesiac")
			->setPrompt('- Zvoliť - ')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Mesiac");

		$dataDependentDay = array(
			'handle' => $this->link('dependentDay!'),
			'yearId' => $form['year']->getHtmlId(),
			'monthId' => $form['month']->getHtmlId(),
			'paramYear' => $this->name . '-year',
			'paramMonth' => $this->name . '-month',
		);

		$callerOptions = array(
			'paramCallerElement' => $this->name . '-callerElement',
			'callerElement' => $form['month']->getName()
		);
		$form['month']->setAttribute('data-dependent-select-box', Json::encode(array_merge($dataDependentDay, $callerOptions)));

		$callerOptions = array(
			'paramCallerElement' => $this->name . '-callerElement',
			'callerElement' => $form['year']->getName()
		);
		$form['year']->setAttribute('data-dependent-select-box', Json::encode(array_merge($dataDependentDay, $callerOptions)));


		$form->addSelect("day", "Deň")
			->setPrompt('- Zvoliť - ')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Rok");

		$form->addText("idIssue", "Id issue")
			->setAttribute("placeholder", "Id issue");

		$form->addTextArea("description", "Popis tasku")
			->setAttribute("placeholder", "Popis tasku");

		$form['idIssue']
			->addConditionOn($form['description'], ~$form::FILLED)
			->addRule(Form::FILLED, 'Musíte vyplniť aspoň jedno z polí (Id issue, Popis tasku)!');

		$form['description']
			->addConditionOn($form['idIssue'], ~$form::FILLED)
			->addRule(Form::FILLED, 'Musíte vyplniť aspoň jedno z polí (Id issue, Popis tasku)!');


		$form->addText("timeRequired", "Odhadovaný čas do konca tasku")
			->setAttribute("placeholder", "Odhadovaný čas do konca tasku");

		$form['timeRequired']
			->addConditionOn($form['timeRequired'], $form::FILLED, TRUE)
			->addRule($form::FLOAT, 'Pole "%label" môže mať len formát 1|1,5|1.5!');

		$form->addText("timeSpend", "Čas straveny na tasku v ramci dňa")
			->setAttribute("placeholder", "Čas straveny na tasku v ramci dňa");

		$form['timeSpend']
			->addConditionOn($form['timeSpend'], $form::FILLED, TRUE)
			->addRule($form::FLOAT, 'Pole "%label" môže mať len formát 1|1,5|1.5!');

		$form->addText("taskCompleted", "Nakoľko je dany task hotový [%]")
			->setAttribute("placeholder", "Nakoľko je dany task hotový");

		$form['taskCompleted']
			->addConditionOn($form['taskCompleted'], $form::FILLED, TRUE)
			->addRule($form::INTEGER, 'Pole "%label" musí byť číslo')
			->addRule($form::RANGE, 'Hodnota pola "%label" môže byť být od 0 do 100!', array(0, 100));

		if ($this->reportEntity) {
			/** @var DateTime $reportDate */
			$reportDate = $this->reportEntity->reportDate;
			$this->setDefaultDateValues($form, $reportDate->format('Y'), $reportDate->format('n'), $reportDate->format('j'));
			$form["idIssue"]->setDefaultValue($this->reportEntity->idIssue);
			$form["description"]->setDefaultValue($this->reportEntity->description);
			$form["timeRequired"]->setDefaultValue($this->reportEntity->timeRequired);
			$form["timeSpend"]->setDefaultValue($this->reportEntity->timeSpend);
			$form["taskCompleted"]->setDefaultValue($this->reportEntity->taskCompleted);

			$form->addSubmit('save', 'Uložiť')
				->onClick[] = callback($this, 'processEditSubmit');
		} else {
			$this->setDefaultDateValues($form);

			$form->addSubmit('save', 'Pridať')
				->onClick[] = callback($this, 'processAddSubmit');
		}

		$form->onValidate[] = callback($this, 'validateForm');
		return $form;
	}


	/**
	 * VALIDATE-FORM
	 * @param Form $form
	 */
	public function validateForm(Form $form)
	{
		$values = $form->getValues();

		// Validate 7 digit number
		if (!empty($values['idIssue'])) {
			$IssueList = explode(',', $values['idIssue']);
			foreach ($IssueList as $issue) {
				$issue = trim($issue);
				if (!preg_match('/^[0-9]{7}$/', $issue)) {
					$form->addError('Issue: "' . $issue . '" nemá požadovaný formát 7-miestneho čísla!');

					if ($this->presenter->isAjax()) {
						$this->invalidateControl();
					}

				}
			}
		}


	}


	/**
	 * PROCESS-ADD-SUBMIT-FORM - add report
	 * @param SubmitButton $button
	 */
	public function processAddSubmit(SubmitButton $button)
	{
		$form = $button->getForm();
		$values = $form->getValues();
		$reportDate = new DateTime();
		$reportDate->setTimestamp(mktime(0, 0, 0, $values['month'], $values['day'], $values['year']));
		$values['reportDate'] = $reportDate;
		$values['userId'] = $this->presenter->getUser()->id;

		try {
			$reportEntity = $this->reportManager->add($values);
			$this->presenter->flashMessage('Report bol úspešne pridaný.', 'success');
			$this->logger->message(
				vsprintf("Report bol úspešne pridaný ID: %s ", array($reportEntity->id)),
				'REPORT', Logger::INFO,
				$values
			);
		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nastala neočakávaná chyba, kontaktujte prosím administrátora.', 'error');
			$this->logger->message($e, 'REPORT', Logger::ERROR, $values);
		}

		if ($this->presenter->isAjax()) {
			$form->setValues(array(), TRUE);
			$form['year']->setValue($this->nowYear);
			$form['month']->setValue($this->nowMonth);
			$form['day']->setValue($this->nowDay);
			$this->invalidateControl();
		} else {
			$this->invalidateControl();
		}
	}


	/**
	 * PROCESS-EDIT-SUBMIT-FORM - edit report
	 * @param SubmitButton $button
	 */
	public function processEditSubmit(SubmitButton $button)
	{
		$form = $button->getForm();
		$values = $form->getValues();
		$reportDate = new DateTime();
		$reportDate->setTimestamp(mktime(0, 0, 0, $values['month'], $values['day'], $values['year']));
		$values['reportDate'] = $reportDate;

		try {
			$reportEntity = $this->reportManager->edit($this->reportEntity->id, $values);
			$this->presenter->flashMessage('Report bol úspešne upravený.', 'success');
			$this->logger->message(
				vsprintf("Report ID: %s ", array($reportEntity->id)),
				'REPORT', Logger::INFO,
				$values
			);
		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nastala neočakávaná chyba, kontaktujte prosím administrátora.', 'error');
			$this->logger->message($e, 'REPORT', Logger::ERROR, $values);
			$this->invalidateControl();
		}
		$this->presenter->redirect('ReportList:default');
	}


	/**
	 * HANDLE - DeleteReport
	 */
	public function handleDeleteReport()
	{
		try {
			$this->reportManager->find($this->reportEntity->id)->delete();

			$this->presenter->flashMessage('Report bol úspešne vymazaný.', 'success');
			$this->logger->message(
				vsprintf("Blok ID: %s bolo úspešne vymazaný.", array($this->reportEntity->id)),
				'REPORT',
				Logger::INFO
			);
		} catch (\Exception $e) {
			$this->presenter->flashMessage('Nastala neočakávaná chyba, kontaktujte prosím administrátora.', 'error');
			$this->logger->message($e, 'REPORT', Logger::ERROR, $this->blockEntity->id);
		}
		$this->presenter->redirect('default');
	}


	/**
	 * RENDER
	 */
	public function render()
	{
		$this->template->reportEntity = $this->reportEntity;
		parent::render();
	}
}


/**
 * IReportFormControl
 */
interface IReportFormControl
{

	/**
	 * @return ReportFormControl
	 */
	function create();
}
