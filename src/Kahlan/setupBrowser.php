<?php
namespace LostKobrakai\TestHelpers\Kahlan;


use Kahlan\Matcher;
use Kahlan\Filter\Filter;
use LostKobrakai\TestHelpers\Browser;
use ProcessWire\ProcessWire;

$browser = new Browser();

if(!isset($browserSettings)) $browserSettings = [];

Filter::register('testhelper.exclude.namespaces', function ($chain) {
	$defaults = ['Behat'];
	$excluded = $this->commandLine()->get('exclude');
	$this->commandLine()->set('exclude', array_unique(array_merge($excluded, $defaults)));
	return $chain->next();
});

Filter::register('testhelper.browser.startup', function ($chain) use($browser, $browserSettings) {
	$this->suite()->browser = $browser->startUp($browserSettings);
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

Filter::apply($this, 'interceptor', 'testhelper.exclude.namespaces');
Filter::apply($this, 'run', 'testhelper.browser.startup');
Filter::apply($this, 'stop', 'testhelper.browser.cleanup');
Filter::apply($this, 'run', 'testhelper.register.toContain');
