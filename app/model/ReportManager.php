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

use Nette\Database\Table\ActiveRow;

/**
 * ReportManager
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class ReportManager extends BaseManager
{
	/**
	 * @param array $values
	 * @return bool|int|ActiveRow
	 */
	public function add($values)
	{
		$reportData = array();

		if (array_key_exists('reportDate', $values)) {
			$reportData['reportDate'] = $values['reportDate'];
		}
		if (array_key_exists('idIssue', $values)) {
			$reportData['idIssue'] = $values['idIssue'] != '' ? $values['idIssue'] : NULL;
		}
		if (array_key_exists('description', $values)) {
			$description = strip_tags($values['description']);
			$reportData['description'] = $description != '' ? $description : NULL;
		}
		if (array_key_exists('timeRequired', $values)) {
			$reportData['timeRequired'] = $values['timeRequired'] != '' ? $values['timeRequired'] : NULL;
		}
		if (array_key_exists('timeSpend', $values)) {
			$reportData['timeSpend'] = $values['timeSpend'] != '' ? $values['timeSpend'] : NULL;
		}
		if (array_key_exists('taskCompleted', $values)) {
			$reportData['taskCompleted'] = $values['taskCompleted'] != '' ? $values['taskCompleted'] : NULL;
		}
		if (array_key_exists('userId', $values)) {
			$reportData['user_id'] = $values['userId'];
		}

		$reportEntity = $this->getTable()->insert($reportData);
		return $reportEntity;
	}


	/**
	 * @param int $reportId
	 * @param array $values
	 * @return bool|int|ActiveRow
	 */
	public function edit($reportId, $values)
	{
		$reportData = array();

		if (array_key_exists('reportDate', $values)) {
			$reportData['reportDate'] = $values['reportDate'];
		}
		if (array_key_exists('idIssue', $values)) {
			$reportData['idIssue'] = $values['idIssue'] != '' ? $values['idIssue'] : NULL;
		}
		if (array_key_exists('description', $values)) {
			$description = strip_tags($values['description']);
			$reportData['description'] = $description != '' ? $description : NULL;
		}
		if (array_key_exists('timeRequired', $values)) {
			$reportData['timeRequired'] = $values['timeRequired'] != '' ? $values['timeRequired'] : NULL;
		}
		if (array_key_exists('timeSpend', $values)) {
			$reportData['timeSpend'] = $values['timeSpend'] != '' ? $values['timeSpend'] : NULL;
		}
		if (array_key_exists('taskCompleted', $values)) {
			$reportData['taskCompleted'] = $values['taskCompleted'] != '' ? $values['taskCompleted'] : NULL;
		}

		$reportEntity = $this->find($reportId);
		$reportEntity->update($reportData);
		return $reportEntity;
	}
}
