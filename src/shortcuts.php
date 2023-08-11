<?php
/**
 * @author Honza Cerny (http://honzacerny.com)
 */

use Tracy\Debugger;

if (!function_exists('d')) {
	/**
	 * Shortcut for Debugger::dump
	 *
	 * @author   Jan Tvrdík
	 * @param    mixed
	 * @param    mixed $var , ... optional additional variable(s) to dump
	 * @return   mixed the first dumped variable
	 */
	function d($var)
	{
		foreach (func_get_args() as $var) Debugger::dump($var);

		return func_get_arg(0);
	}
}

if (!function_exists('bd')) {
	/**
	 * Shortcut for Debugger::barDump
	 *
	 * @author   Jan Tvrdík
	 * @param    mixed
	 * @param    mixed $var , ... optional additional variable(s) to dump
	 * @return   mixed the first dumped variable
	 */
	function bd($var, $title = NULL)
	{
		return Debugger::barDump($var, $title);
	}
}

if (!function_exists('de')) {
	/**
	 * Shortcut for Debugger::dump & exit()
	 *
	 * @author   Jan Tvrdík
	 * @param    mixed
	 * @param    mixed $var , ... optional additional variable(s) to dump
	 * @return   void
	 */
	function de($var)
	{
		foreach (func_get_args() as $var) Debugger::dump($var);
		exit;
	}
}