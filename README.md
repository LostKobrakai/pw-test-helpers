# ProcessWire Test Helper

Helper classes to enable automatic test db setup and browser testing with ProcessWire. The core classes should be usable by any testing framework, but currently the only real implementation is using `kahlan/kahlan`.

To install run the following in the terminal: 

`composer require --dev lostkobrakai/pw-test-helper`

## Kahlan

Install Kahlan also via composer:

`composer require --dev kahlan/kahlan`

Create or update your `kahlan-config.php`

```php
<?php

use LostKobrakai\TestHelpers\Kahlan\SetupInclude;

// Create a unique db name for the testruns
$dbName = 'pw_' . md5(__DIR__ . time());

// Path to the ProcessWire root folder
$path = __DIR__;

// Browsertest settings
$browserSettings = [
	// Send db name as request header
	'database' => $dbName,

	// Allow for relative urls in tests
	'rootUrl' => 'http://db_tests.valet/'
];

// Add DB integration
/** @noinspection PhpIncludeInspection */
include SetupInclude::db();

// Add Browsertest integration
/** @noinspection PhpIncludeInspection */
include SetupInclude::browser();
```

The database part does setup the test database and includes a bootstrapped processwire instance in kahlan. It's accessable in tests like this:

```php
it('should find the processwire homepage', function() {
	expect($this->processwire->pages->get('/')->id)->toBe(1);
});
```

The browser testing is implemented by using `Behat/Mink`, so there are only some helper function to kick off things. Otherwise it's api is documented for the Mink package. The current driver is `zombie`. A headless node based driver which is quite fast. It does require `npm i zombie --save-dev` to be present.

```php
it('should be able to load the page for inspection', function() {
	$this->browser->visit('/');

	// Check the page for specific text or elements
	expect($this->browser->element('h1')->getText())->toBe('Home');

	// Kahlan does have support for async expectations
	// It waits until an optional timeout to see the text
	waitsFor(function() {
		return $this->browser->page();
	})->toContain('Home');
});
```

When using browsertesting the request by default doesn't know anything about the temporary test database. Therefore this is needed in your config.php below the actual database config. This way the request can processwire to use the test db instead of your normal one.

```php
/**
 * Change db for browser testings
 */
if(isset($_SERVER['HTTP_X_TEST_WITH_DB']) && $_SERVER['HTTP_X_TEST_WITH_DB']){
	$config->dbName = $_SERVER['HTTP_X_TEST_WITH_DB'];
}
```

When using InnoDB it's besides the already temporary test database possible to use database transactions to avoid having sideeffects in test. With the following db changes are revoked after each test.

```php
describe('…', function() {
		beforeEach(function() {
			$this->processwire->database->beginTransaction();
		});

		afterEach(function() {
			$this->processwire->database->rollBack();
		});

		[…]
});
```