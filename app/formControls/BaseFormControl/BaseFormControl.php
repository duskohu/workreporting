<?php

namespace Nas;

use Kdyby\BootstrapFormRenderer\Latte\FormMacros;
use Nette\Application\UI\Control;
use Nette\Latte\Engine;
use Nette\Templating\FileTemplate;
use Nette\Templating\ITemplate;
use Nette\Templating\Template;


/**
 * BaseFormControl
 *
 * @author Dusan Hudak
 */
class BaseFormControl extends Control
{
	/** @var string */
	protected $templateFile;

	/** @var  NasConfigStorage */
	protected $nasConfigStorage;

	/** @var  bool */
	protected $ajaxRequest = TRUE;

	/**
	 * @param bool $value
	 */
	public function setAjaxRequest($value = TRUE)
	{
		$this->ajaxRequest = $value;
	}


	/**
	 * INJECT NasConfigStorage
	 * @param NasConfigStorage $nasConfigStorage
	 */
	public function injectNasConfigStorage(NasConfigStorage $nasConfigStorage)
	{
		$this->nasConfigStorage = $nasConfigStorage;
	}

	/**
	 * @param NasConfigStorage $nasConfigStorage
	 */
	public function setNasConfigStorage(NasConfigStorage $nasConfigStorage)
	{
		$this->nasConfigStorage = $nasConfigStorage;
	}



	/**
	 * @param string $templateFile
	 */
	public function setFileTemplate($templateFile)
	{
		$this->templateFile = $templateFile;
	}


	/**
	 * @return string
	 */
	public function getFileTemplate()
	{
		return $this->templateFile;
	}


	/**
	 * @param Template $template
	 */
	public function templatePrepareFilters($template)
	{
		/** @var Engine $latte */
		$latte = $this->getPresenter()->getContext()->createService('nette.latte');
		FormMacros::install($latte->compiler);
		$template->registerFilter($latte);
	}


	/**
	 * @param  string|NULL
	 * @return ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		/** @var FileTemplate $template */
		$template = parent::createTemplate($class);
		if (!$this->templateFile) {
			$this->templateFile = $this->getTemplateFilePath();
		}
		$template->setFile($this->templateFile);

		$template->__form = $template->form = $this['form'];
		$template->_form = $template->form = $this['form'];
		return $template;
	}


	/**
	 * @return string
	 */
	protected function getTemplateFilePath()
	{
		$reflection = $this->getReflection();
		$dir = dirname($reflection->getFileName());
		$filename = $reflection->getShortName() . '.latte';
		$templateFile = $dir . DIRECTORY_SEPARATOR . $filename;

		if (!is_file($templateFile)) {
			$templateFile = $this->nasConfigStorage->templatesDir . '/components/form.latte';
		}
		return $templateFile;
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
