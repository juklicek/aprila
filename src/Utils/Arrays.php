<?php
/**
 * @author Honza Cerny (http://honzacerny.com)
 */

namespace Aprila\Utils;

class Arrays
{

	/**
	 * @param $array
	 * @param $curr_key
	 * @return mixed
	 */
	public static function getNextKey(&$array, $curr_key)
	{
		reset($array);
		$next = key($array);

		do {
			$tmp_key = key($array);
			$res = next($array);
		} while (($tmp_key != $curr_key) && $res);

		if ($res) {
			$next = key($array);
		}

		return $next;
	}


	/**
	 * @param $array
	 * @param $curr_key
	 * @return mixed
	 */
	public static function getPreviousKey(&$array, $curr_key)
	{
		end($array);
		$prev = key($array);

		do {
			$tmp_key = key($array);
			$res = prev($array);
		} while (($tmp_key != $curr_key) && $res);

		if ($res) {
			$prev = key($array);
		}

		return $prev;
	}


	/**
	 * @param $array
	 * @param $curr_val
	 * @return mixed
	 */
	public static function getNextValue(&$array, $curr_val)
	{
		reset($array);

		do {
			$tmp_val = current($array);
			$res = next($array);
		} while (($tmp_val != $curr_val) && $res);

		if ($res) {
			$next = current($array);
		} else {
			// return first value
			reset($array);
			$next = current($array);
		}

		return $next;
	}


	/**
	 * @param $array
	 * @param $curr_val
	 * @return mixed
	 */
	public static function getPreviousValue(&$array, $curr_val)
	{
		end($array);
		$prev = current($array);

		do {
			$tmp_val = current($array);
			$res = prev($array);
		} while (($tmp_val != $curr_val) && $res);

		if ($res) {
			$prev = current($array);
		}

		return $prev;
	}

}