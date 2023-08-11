<?php
/**
 * @author Honza Cerny (http://honzacerny.com)
 */

namespace Aprila;

use Nette\Forms\Rendering\DefaultFormRenderer,
	Nette\Application\UI;

class BaseFormRenderer extends DefaultFormRenderer
{
	/**
	 *  /--- form.container
	 *
	 *    /--- if (form.errors) error.container
	 *      .... error.item [.class]
	 *    \---
	 *
	 *    /--- hidden.container
	 *      .... HIDDEN CONTROLS
	 *    \---
	 *
	 *    /--- group.container
	 *      .... group.label
	 *      .... group.description
	 *
	 *      /--- controls.container
	 *
	 *        /--- pair.container [.required .optional .odd]
	 *
	 *          /--- label.container
	 *            .... LABEL
	 *            .... label.suffix
	 *            .... label.requiredsuffix
	 *          \---
	 *
	 *          /--- control.container [.odd]
	 *            .... CONTROL [.required .text .password .file .submit .button]
	 *            .... control.requiredsuffix
	 *            .... control.description
	 *            .... if (control.errors) error.container
	 *          \---
	 *        \---
	 *      \---
	 *    \---
	 *  \--
	 *
	 * @var array of HTML tags
	 */
	public $wrappers = array(
		'form' => array(
			'container' => 'div class="form--aprila-base"',
			'errors' => TRUE,
		),

		'error' => array(
			'container' => 'div class="alert-message error"',
			'item' => 'p',
		),

		'group' => array(
			'container' => 'fieldset',
			'label' => 'legend',
			'description' => 'p',
		),

		'controls' => array(
			'container' => '',
		),

		'pair' => array(
			'container' => 'div class="row"',
			'.required' => 'required',
			'.optional' => NULL,
			'.odd' => NULL,
		),

		'control' => array(
			'container' => 'div class="columns medium-9"',
			'radiolist' => 'li',
			'.odd' => NULL,

			'errors' => FALSE,
			'description' => 'small',
			'requiredsuffix' => '',

			'.required' => 'required',
			'.text' => 'text span7',
			'.password' => 'text',
			'.file' => 'text',
			'.submit' => 'button primary',
			'.image' => 'imagebutton',
			'.button' => 'button',
		),

		'label' => array(
			'container' => 'div class="columns medium-3"',
			'suffix' => NULL,
			'requiredsuffix' => '',
		),

		'hidden' => array(
			'container' => 'div',
		),
	);


	public static function factory()
	{
		$form = new UI\Form;
		$form->setRenderer(new BaseFormRenderer);

		return $form;
	}
}
