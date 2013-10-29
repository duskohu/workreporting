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

use Nette\Database\Connection;
use Nette\Database\SelectionFactory;
use Nette\Database\Table\ActiveRow;
use Nette\Database\Table\Selection;
use Nette\Object;


/**
 * Base Manager - for all Managers
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
abstract class BaseManager extends Object
{
	/** @var Connection */
	protected $connection;

	/** @var SelectionFactory */
	protected $selectionFactory;

	/** @var string */
	protected $tableName;


	/**
	 * CONSTRUCT
	 * @param SelectionFactory $selectionFactory
	 */
	public function __construct(SelectionFactory $selectionFactory)
	{
		$this->selectionFactory = $selectionFactory;
		$this->connection = $selectionFactory->getConnection();

		preg_match('#(\w+)Manager$#', get_class($this), $m);
		if (!empty($m)) {
			$this->tableName = strtolower($m[1]);
		} else {
			$this->tableName = strtolower(get_class($this));
		}
	}


	/**
	 * getTable
	 * @return Selection
	 */
	protected function getTable()
	{
		return $this->selectionFactory->table($this->tableName);
	}


	/**
	 * findAll
	 * @return Selection
	 */
	public function findAll()
	{
		return $this->getTable();
	}


	/**
	 * findBy
	 * @param array $by
	 * @return Selection
	 */
	public function findBy(array $by)
	{
		return $this->getTable()->where($by);
	}


	/**
	 * findOneBy
	 * @param array $by
	 * @return ActiveRow
	 */
	public function findOneBy(array $by)
	{
		return $this->findBy($by)->limit(1)->fetch();
	}


	/**
	 * @param int $id
	 * @return ActiveRow
	 */
	public function find($id)
	{
		return $this->getTable()->get($id);
	}
}
