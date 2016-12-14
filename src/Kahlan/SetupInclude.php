<?php
/**
 * Created by PhpStorm.
 * User: benni
 * Date: 14.12.16
 * Time: 12:15
 */

namespace LostKobrakai\TestHelpers\Kahlan;


class SetupInclude
{
	public static function __callStatic ($name, $arguments)
	{
		$name = ucfirst($name);
		$file = __DIR__ . "/setup{$name}.php";
		if(is_file($file)) return $file;

		return null;
	}
}