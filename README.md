# ProcessWire Test Helper

Helper classes to enable automatic test db setup and browser testing with ProcessWire.

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

// Start DB integration
/** @noinspection PhpIncludeInspection */
include SetupInclude::db();

// Start Browsertest integration
/** @noinspection PhpIncludeInspection */
include SetupInclude::browser();
```
