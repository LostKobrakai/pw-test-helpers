<?php
/**
 * Created by PhpStorm.
 * User: benni
 * Date: 14.12.16
 * Time: 11:31
 */

namespace LostKobrakai\TestHelpers;


use Exception;

class ElementNotFound
{
	protected $_selector;

	public function __construct($selector)
	{
		$this->_selector = $selector;
	}

	public function __call($name, $args = [])
	{
		throw new Exception("Can't call `{$name}()` on `'{$this->_selector}'` no matching element found.");
	}

	public function __toString()
	{
		return "No matching element found for `'{$this->_selector}'`";
	}
}