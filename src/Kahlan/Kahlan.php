<?php
/**
 * Created by PhpStorm.
 * User: benni
 * Date: 14.12.16
 * Time: 11:12
 */

namespace LostKobrakai\TestHelpers\Kahlan;

use Kahlan\Filter\Filter;
use LostKobrakai\TestHelpers\Browser;
use LostKobrakai\TestHelpers\Database;

class Kahlan
{
	/**
	 * Hook all database / pw startup related tasks into the kahlan pipeline
	 *
	 * @param $kahlan
	 * @param $dbName
	 */
	public static function useDatabase ($kahlan, $dbName)
	{
		Filter::register('testhelper.processwire.startup', function($chain) use($dbName) {
			define('ENVIRONMENT', 'test');

			$config = ProcessWire::buildConfig(__DIR__);
			$config->allowExceptions = true; // Do not catch exceptions
			$config->debug = true;
			$config->dbName = $dbName;

			$testDatabase = new Database($config);
			$testDatabase->create(__DIR__ . '/../spec/setup/blank.sql');

			$this->suite()->processwire = new ProcessWire($config);

			return $chain->next();
		});

		Filter::register('testhelper.processwire.cleanup', function($chain) {
			$testDatabase = new Database($this->suite()->processwire->config);
			$testDatabase->drop();

			return $chain->next();
		});
		Filter::apply($kahlan, 'run', 'testhelper.processwire.startup');
		Filter::apply($kahlan, 'quit', 'testhelper.processwire.cleanup');
	}

	/**
	 * Hook all browser-testing related tasks into the kahlan pipeline
	 *
	 * @param $kahlan
	 */
	public static function useBrowerTests ($kahlan)
	{
		$browser = new Browser();

		Filter::register('testhelper.exclude.namespaces', function ($chain) {
			$defaults = ['Behat'];
			$excluded = $this->commandLine()->get('exclude');
			$this->commandLine()->set('exclude', array_unique(array_merge($excluded, $defaults)));
			return $chain->next();
		});

		Filter::register('testhelper.browser.startup', function ($chain) use($browser) {
			$this->suite()->mink = $browser->startUp();
			$this->suite()->afterEach(function() use ($browser) {
				$browser->reset();
			});
			return $chain->next();
		});

		Filter::register('testhelper.browser.cleanup', function ($chain) use($browser) {
			$browser->clean();
			return $chain->next();
		});

		Filter::register('testhelper.register.toContain', function ($chain) {
			Matcher::register('toContain', ToContain::class, 'Behat\Mink\Element\Element');
			return $chain->next();
		});

		Filter::apply($kahlan, 'interceptor', 'testhelper.exclude.namespaces');
		Filter::apply($kahlan, 'run', 'testhelper.browser.startup');
		Filter::apply($kahlan, 'stop', 'testhelper.browser.cleanup');
		Filter::apply($kahlan, 'run', 'testhelper.register.toContain');
	}
}