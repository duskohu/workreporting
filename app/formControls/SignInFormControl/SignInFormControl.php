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

/**
 * Sign-in form control
 * @package Nas- Nette aplication System
 * @author Dusan Hudak <admin@dusan-hudak.com>
 */
class SignInFormControl extends BaseFormControl
{

	/**
	 * FORM Sign-in
	 * @return Form
	 */
	protected function createComponentForm()
	{
		$form = new Form;
		$elementPrototype = $form->getElementPrototype();
		$elementPrototype->class[] = lcfirst($this->reflection->getShortName());

		$form->addText('loginName', 'Prihlasovacie meno')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Vaše prihlasovacie meno");

		$form->addPassword('password', 'Heslo')
			->addRule($form::FILLED, 'Pole "%label" musí byť vyplnené!')
			->setAttribute("placeholder", "Vaše heslo");

		$form->addCheckbox('rememberMe', 'Pamätať si ma na tomto počítači');
		$form->addSubmit('login', 'Prihlásiť sa');
		return $form;
	}


	/**
	 * RENDER
	 */
	public function render()
	{
		parent::render();
	}
}

/**
 * ISignInFormControl
 */
interface ISignInFormControl
{

	/** @return SignInFormControl */
	function create();
}
