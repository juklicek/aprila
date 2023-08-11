<?php
/**
 * @author Honza Cerny (http://honzacerny.com)
 */

namespace Aprila;

class Aprila extends \Nette\Object
{
	const VERSION = '4.0-dev';


	public function __construct()
	{
		throw new \Aprila\StaticClassException;
	}
}