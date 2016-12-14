<?php
/**
 * Created by PhpStorm.
 * User: benni
 * Date: 14.12.16
 * Time: 11:07
 */

namespace LostKobrakai\TestHelpers;


use Behat\Mink\Driver\NodeJS\Server\ZombieServer;
use Behat\Mink\Driver\Selenium2Driver;
use Behat\Mink\Driver\ZombieDriver;
use Behat\Mink\Element\DocumentElement;
use Behat\Mink\Mink;
use Behat\Mink\Session;

/**
 * Class Browser
 *
 * This does require an installation of the zombie npm package either globally or locally
 *
 * @package LostKobrakai\TestHelpers
 */
class Browser
{
	/**
	 * @var Mink
	 */
	private $mink;

	/**
	 * Start Zombie Server and Mink
	 */
	public function startUp ()
	{
		$this->mink = new Mink([
			'zombie' => new Session(new ZombieDriver(new ZombieServer(
				'127.0.0.1', '8124', '/usr/local/bin/node'
			)))
		]);
		$this->mink->setDefaultSessionName('zombie');

		return $this->mink;
	}

	/**
	 * Reset between tests
	 */
	public function reset ()
	{
		$this->mink->resetSessions();
	}

	/**
	 * Clean up the browser objects
	 */
	public function clean() {
		$this->mink->stopSessions();
	}

	/**
	 * Create a new browser session
	 *
	 * Does send the X-TEST-WITH-DB header to allow processwire to use the test db as well
	 *
	 * @param string|null $name
	 * @return Session
	 */
	public static function browser ($name = null)
	{
		/** @var Mink $mink */
		$mink = Suite::current()->mink;
		$config = Suite::current()->processwire->config;
		$session = $mink->getSession($name);
		$session->setRequestHeader('X-TEST-WITH-DB', $config->dbName);

		return $session;
	}

	/**
	 * @param $path
	 * @return string
	 */
	public static function relUrl ($path)
	{
		$config = Suite::current()->processwire->config;
		return $config->urls->httpRoot . ltrim($path, '/');
	}

	/**
	 * Load a url relative to the current root
	 *
	 * @param $path
	 */
	public static function visit($path)
	{
		return static::browser()->visit(static::relUrl($path));
	}

	/**
	 * Gets the browser page.
	 *
	 * @param  string|null $name The session name.
	 * @return DocumentElement
	 */
	public static function page($name = null)
	{
		return static::browser($name)->getPage();
	}

	/**
	 * @param  string $selector The CSS selector
	 * @param  object $parent   The parent `Element` node.
	 * @return object           Returns a `NodeElement`.
	 */
	public static function element($selector = 'body', $parent = null)
	{
		$parent = $parent ?: static::page();
		$element = $parent->find('css', $selector);
		return $element ?: new ElementNotFound($selector);
	}

	/**
	 * API shortcut.
	 *
	 * @param mixed $actual The actual value.
	 * @param int   $timeout
	 * @return kahlan\Matcher A matcher instance.
	 */
	public static function wait($actual, $timeout = 0)
	{
		return waitsFor(function() use ($actual) {
			return $actual;
		}, $timeout);
	}
}