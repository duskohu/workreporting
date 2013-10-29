<?php

/**
 * This file is part of the NasExt extensions of Nette Framework
 *
 * Copyright (c) 2013 Dusan Hudak (http://dusan-hudak.com)
 *
 * For the full copyright and license information, please view
 * the file license.txt that was distributed with this source code.
 */

namespace Nas;

use Nette\Application\UI\Control;
use Nette\Templating\ITemplate;

/**
 * BaseControl
 *
 * @author Dusan Hudak
 */
abstract class BaseControl extends Control
{
	/** @var string */
	protected $templateFile;


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
	 * @param  string|NULL
	 * @return ITemplate
	 */
	protected function createTemplate($class = NULL)
	{
		$template = parent::createTemplate($class);
		if (!$this->templateFile) {
			$this->templateFile = $this->getTemplateFilePath();
		}
		$template->setFile($this->templateFile);

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
		return $dir . DIRECTORY_SEPARATOR . $filename;
	}
}
