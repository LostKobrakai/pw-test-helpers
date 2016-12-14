<?php
namespace LostKobrakai\TestHelpers;


use PDO;
use ProcessWire\Config;
use ProcessWire\WireDatabaseBackup;

class Database
{
	/**
	 * @var Config
	 */
	private $config;

	/**
	 * @var array
	 */
	private $driver_options;

	/**
	 * TestDatabase constructor.
	 *
	 * @param $config
	 */
	public function __construct ($config)
	{
		$this->config = $config;
		$this->driver_options = array(
			PDO::MYSQL_ATTR_INIT_COMMAND => "SET NAMES 'UTF8'",
			PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION
		);
	}

	/**
	 * Create the temporary database (from scratch and 'empty')
	 *
	 * @param string $sqlPath Path to sql file
	 */
	public function create ($sqlPath)
	{
		$config = $this->config;

		// Create temp database
		$database = new PDO(
			"mysql:host=$config->dbHost;port=$config->dbPort",
			$config->dbUser, $config->dbPass, $this->driver_options
		);
		$database->exec("CREATE SCHEMA `$config->dbName` DEFAULT CHARACTER SET `utf8mb4`");

		// Fill with tables
		$backup = new WireDatabaseBackup();
		$backup->setDatabase(new PDO(
			"mysql:dbname=$config->dbName;host=$config->dbHost;port=$config->dbPort",
			$config->dbUser, $config->dbPass, $this->driver_options
		));
		$backup->restoreMerge(
			$config->paths->root . "wire/core/install.sql",
			$sqlPath,
			$this->restoreOptions()
		);
	}

	/**
	 * Drop the temporary database again
	 */
	public function drop ()
	{
		$config = $this->config;
		$database = new PDO(
			"mysql:host=$config->dbHost;port=$config->dbPort",
			$config->dbUser, $config->dbPass, $this->driver_options
		);
		$database->exec("DROP SCHEMA IF EXISTS `$config->dbName`");
	}

	/**
	 * Create the restoreOptions according to the version in install.php
	 * @return array
	 */
	private function restoreOptions ()
	{
		$config = $this->config;

		$restoreOptions = array();
		$replace = array();

		if($config->dbEngine != 'MyISAM') {
			$replace['ENGINE=MyISAM'] = "ENGINE=$config->dbEngine";
		}

		if($config->dbCharset != 'utf8') {
			$replace['CHARSET=utf8'] = "CHARSET=$config->dbCharset";
			if(strtolower($config->dbCharset) === 'utf8mb4') {
				if(strtolower($config->dbEngine) === 'innodb') {
					$replace['(255)'] = '(191)';
					$replace['(250)'] = '(191)';
				} else {
					$replace['(255)'] = '(250)';
				}
			}
		}

		if(count($replace))
			$restoreOptions['findReplaceCreateTable'] = $replace;

		return $restoreOptions;
	}
}