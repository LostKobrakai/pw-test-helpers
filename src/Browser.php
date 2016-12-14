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
	 * @var array
	 */
	private $options;

	/**
	 * Start Zombie Server and Mink
	 *
	 * @param array $options
	 * @return Mink
	 */
	public function startUp ($options = [])
	{
		$this->mink = new Mink([
			'zombie' => new Session(new ZombieDriver(new ZombieServer(
				'127.0.0.1', '8124', '/usr/local/bin/node'
			)))
		]);
		$this->mink->setDefaultSessionName('zombie');

		$this->options = array_merge([
			'rootUrl' => '',
			'database' => ''
		], $options);

		return $this;
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
	public function window ($name = null)
	{
		$mink = $this->mink;
		$session = $mink->getSession($name);

		if($this->options['database'])
			$session->setRequestHeader('X-TEST-WITH-DB', $this->options['database']);

		return $session;
	}

	/**
	 * @param $path
	 * @return string
	 */
	public function url ($path)
	{
		if($this->options['rootUrl'])
			return $this->options['rootUrl'] . ltrim($path, '/');

		return $path;
	}

	/**
	 * Load a url relative to the current root
	 *
	 * @param $path
	 */
	public function visit($path)
	{
		return $this->window()->visit($this->url($path));
	}

	/**
	 * Gets the browser page.
	 *
	 * @param  string|null $name The session name.
	 * @return DocumentElement
	 */
	public function page($name = null)
	{
		return $this->window($name)->getPage();
	}

	/**
	 * @param  string $selector The CSS selector
	 * @param  object $parent   The parent `Element` node.
	 * @return object           Returns a `NodeElement`.
	 */
	public function element($selector = 'body', $parent = null)
	{
		$parent = $parent ?: $this->page();
		$element = $parent->find('css', $selector);
		return $element ?: new ElementNotFound($selector);
	}
}