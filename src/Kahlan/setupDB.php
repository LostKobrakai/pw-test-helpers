<?php
namespace LostKobrakai\TestHelpers\Kahlan;


use Kahlan\Matcher;
use Kahlan\Filter\Filter;
use LostKobrakai\TestHelpers\Database;
use ProcessWire\ProcessWire;
use RuntimeException;

/**
 * @var string $path
 * @var string $dbName
 */

if(!isset($path) || !isset($dbName))
	throw new RuntimeException('$path and $dbName are not supplied');

Filter::register('testhelper.processwire.startup', function($chain) use($path, $dbName) {
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

Filter::apply($this, 'run', 'testhelper.processwire.startup');
Filter::apply($this, 'quit', 'testhelper.processwire.cleanup');
