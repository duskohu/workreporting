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

use Nette\InvalidArgumentException;
use Nette\Utils\Html;

/**
 * Order control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 *
 * For change default {$presenter['order']->sortLink("column", "Title", array('sort' => 'icon-sort','asc' => 'icon-sort-up', 'desc' => 'icon-sort-down'))}
 */
class OrderControl extends BaseControl
{
	/** @persistent */
	public $column;

	/** @persistent */
	public $sort;

	/** @ array */
	protected $attrs;

	/** @ array */
	protected $iconClass = array('sort' => 'icon-sort', 'asc' => 'icon-sort-up', 'desc' => 'icon-sort-down');


	/**
	 * CONSTRUCT
	 * @param $parent
	 * @param string $name
	 * @param string $column
	 * @param string $sort
	 */
	public function __construct($parent = NULL, $name = NULL, $column = NULL, $sort = NULL)
	{
		parent::__construct($parent, $name);

		if ($column == NULL) {
			new InvalidArgumentException("Parameter column is empty");
		}
		if ($sort == NULL) {
			new InvalidArgumentException("Parameter sort is empty");
		}
		if ($this->column == NULL) {
			$this->column = $column;
		}
		if ($this->sort == NULL) {
			$this->sort = $sort;
		}
	}


	/**
	 * setAttrs
	 * @param null $attrs
	 */
	public function setAttrs($attrs = NULL)
	{
		if ($attrs != NULL) {
			$this->attrs = $attrs;
		}
	}


	/**
	 * sortLink
	 * @param string $column
	 * @param string|NULL $title
	 * @param array $iconClass
	 * @return Html
	 */
	public function sortLink($column, $title = NULL, $iconClass = array())
	{
		// Set class for icons
		if (!empty($iconClass)) {
			if (array_key_exists('asc', $iconClass)) {
				$this->iconClass['asc'] = $iconClass['asc'];
			}
			if (array_key_exists('desc', $iconClass)) {
				$this->iconClass['desc'] = $iconClass['desc'];
			}
			if (array_key_exists('sort', $iconClass)) {
				$this->iconClass['sort'] = $iconClass['sort'];
			}
		}

		if ($title == NULL) {
			$title = $column;
		}
		$linkParams['column'] = $column;
		$attributes["class"] = $this->iconClass['sort'];

		if ($column == $this->column) {
			$linkParams['sort'] = $this->sort == "asc" ? "desc" : "asc";

			if ($this->sort == "asc") {
				$attributes["class"] = $this->iconClass['asc'];
			}
			if ($this->sort == "desc") {
				$attributes["class"] = $this->iconClass['desc'];
			}
		} else {
			$linkParams['sort'] = "asc";
		}

		$container = Html::el('span', array('class' => 'orderControl'));
		$url = $this->link("this", $linkParams);
		$link = Html::el('a', $this->attrs)->href($url)->setHtml($title);
		$container->add($link);
		if (isset($attributes)) {
			$link->add(Html::el('i', $attributes));
		}
		return $container;
	}
}
