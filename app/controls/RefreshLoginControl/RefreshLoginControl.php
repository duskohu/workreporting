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

/**
 * RefreshLogin control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class RefreshLoginControl extends BaseControl
{


	/**
	 * HANDLE - RefreshLogin
	 */
	public function handleRefreshLogin()
	{
		$this->presenter->sendPayload();
	}


	/**
	 * RENDER
	 */
	public function render()
	{
		$this->template->render();
	}
}
