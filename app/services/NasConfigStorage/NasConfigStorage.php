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

use Nette\Object;


/**
 * NasConfigStorage
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class NasConfigStorage extends Object
{
	/** @var array */
	private $parameters;

	/** @var  string */
	public $webmasterEmail;

	/** @var  string */
	public $applicationEmail;

	/** @var  string */
	public $applicationName;

	/** @var  string */
	public $applicationDescription;

	/** @var  string */
	public $applicationAuthorName;

	/** @var  string */
	public $applicationLanguage;

	/** @var  string */
	public $applicationRobots;

	/** @var  string */
	public $applicationKeywords;

	/** @var  string */
	public $templatesDir;

	/** @var  string */
	public $reportIssueUrl;


	/**
	 * CONSTRUCT
	 * @param $parameters
	 */
	public function __construct($parameters)
	{
		$this->parameters = $parameters;
		if (array_key_exists('webmasterEmail', $parameters)) {
			$this->webmasterEmail = $parameters['webmasterEmail'];
		}
		if (array_key_exists('applicationEmail', $parameters)) {
			$this->applicationEmail = $parameters['applicationEmail'];
		}
		if (array_key_exists('applicationName', $parameters)) {
			$this->applicationName = $parameters['applicationName'];
		}
		if (array_key_exists('applicationDescription', $parameters)) {
			$this->applicationDescription = $parameters['applicationDescription'];
		}
		if (array_key_exists('applicationAuthorName', $parameters)) {
			$this->applicationAuthorName = $parameters['applicationAuthorName'];
		}
		if (array_key_exists('applicationLanguage', $parameters)) {
			$this->applicationLanguage = $parameters['applicationLanguage'];
		}
		if (array_key_exists('applicationRobots', $parameters)) {
			$this->applicationRobots = $parameters['applicationRobots'];
		}
		if (array_key_exists('applicationKeywords', $parameters)) {
			$this->applicationKeywords = $parameters['applicationKeywords'];
		}
		if (array_key_exists('templatesDir', $parameters)) {
			$this->templatesDir = $parameters['templatesDir'];
		}
		if (array_key_exists('reportIssueUrl', $parameters)) {
			$this->reportIssueUrl = $parameters['reportIssueUrl'];
		}
	}
}
