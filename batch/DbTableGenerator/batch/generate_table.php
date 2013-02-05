#!/usr/bin/env php
<?php
/**
 * This file is part of YAPEPBase utils.
 *
 * @package      DbTableGenerator
 * @subpackage   Controller
 * @copyright    2011 The YAPEP Project All rights reserved.
 * @license      http://www.opensource.org/licenses/bsd-license.php BSD License
 */

declare(ticks = 1);

namespace DbTableGenerator\Controller;

/** Require the bootstrap */
require_once __DIR__ . '/../bootstrap.php';

use YapepBase\Application;
use YapepBase\Config;
use YapepBase\Batch\BatchScript;
use YapepBase\Batch\CliUserInterfaceHelper;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Exception\Exception;

use DbTableGenerator\BusinessObject\TableGeneratorBo;
use DbTableGenerator\View\Template\TableGeneratorTemplate;

/**
 * Controller class for the database table generator batch script.
 *
 * Currently only MySQL is supported. The script can work with a mysql connection specified with command line switches,
 * or a configuration set up in the framework's configuration system.
 *
 * Run the script with the --help switch for an explanation of the switches.
 *
 * @package    DbTableGenerator
 * @subpackage Controller
 */
class TableGeneratorController extends BatchScript {

	/** Usage constant for fully specified db connection configuration. */
	const USAGE_FULL = 'full';

	/** Usage constant for configuration based connections. */
	const USAGE_CONFIG = 'config';

	/**
	 * DB host name.
	 *
	 * @var string
	 */
	protected $host = 'localhost';

	/**
	 * DB user name.
	 *
	 * @var string
	 */
	protected $user = 'root';

	/**
	 * DB password.
	 *
	 * @var string
	 */
	protected $password = '';

	/**
	 * DB port.
	 *
	 * @var int
	 */
	protected $port = 3306;

	/**
	 * DB name.
	 *
	 * @var string
	 */
	protected $dbName;

	/**
	 * Table name
	 *
	 * @var string
	 */
	protected $tableName;

	/**
	 * Name of the root namespace
	 *
	 * @var string
	 */
	protected $rootNamespace;

	/**
	 * Name of the database namespace
	 *
	 * @var string
	 */
	protected $dbNamespace;

	/**
	 * Default connection
	 *
	 * @var string
	 */
	protected $defaultConnection;

	/**
	 * The error message to show.
	 *
	 * @var string
	 */
	protected $errorMessage;

	/**
	 * If TRUE, the help will be shown too.
	 *
	 * @var bool
	 */
	protected $showHelp = false;

	/**
	 * Returns the script's description.
	 *
	 * This method should return a the description for the script. It will be used as the script description in the
	 * help.
	 *
	 * @return string
	 */
	protected function getScriptDescription() {
		return 'This script generates table descriptor classes for the DAO layer and sends the output to STDOUT. The
			classes should be reviewed after generation and placed into the project\'s common namespace, or the company
			common namespace if used by several projects. The database namespace should be the CamelCased version of
			the database name.';
	}

	/**
	 * This function is called, if the process receives an interrupt, term signal, etc. It can be used to clean up
	 * stuff. Note, that this function is not guaranteed to run or it may run after execution.
	 *
	 * @return void
	 *
	 * @throws \YapepBase\Exception\Exception   To abort the run.
	 */
	protected function abort() {
		throw new Exception('Abort signal received');
	}

	/**
	 * Sets the switches used by the script
	 *
	 * @return void
	 */
	protected function prepareSwitches() {
		parent::prepareSwitches();

		$this->usageIndexes[self::USAGE_FULL]   = $this->cliHelper->addUsage(
			'Set up the connection with database options');
		$this->usageIndexes[self::USAGE_CONFIG] = $this->cliHelper->addUsage(
			'Set up the connection from an already configured connection');

		$this->cliHelper->addSwitch('h', 'host', 'DB server host name. Defaults to "localhost".',
			$this->usageIndexes[self::USAGE_FULL], true, 'host');

		$this->cliHelper->addSwitch('u', 'user', 'DB server username. Defaults to "root".',
			$this->usageIndexes[self::USAGE_FULL], true, 'username');

		$this->cliHelper->addSwitch('p', 'password',
			'DB server password for the specified user. Defaults to empty password.',
			$this->usageIndexes[self::USAGE_FULL], true, 'password');

		$this->cliHelper->addSwitch('P', 'port', 'DB server port. Defaults to 3306',
			$this->usageIndexes[self::USAGE_FULL], true, 'port');

		$this->cliHelper->addSwitch('d', 'db-name', 'Database name',
			$this->usageIndexes[self::USAGE_FULL], false, 'databaseName');

		$this->cliHelper->addSwitch('c', 'connection-name', 'Configured connection name',
			$this->usageIndexes[self::USAGE_CONFIG], false, 'connectionName');

		$this->cliHelper->addSwitch('t', 'table', 'Table name', array($this->usageIndexes[self::USAGE_FULL],
			$this->usageIndexes[self::USAGE_CONFIG]), false, 'tableName');

		$this->cliHelper->addSwitch('', 'default-connection',
			'The name of the default connection should be used by the class',
			array($this->usageIndexes[self::USAGE_FULL], $this->usageIndexes[self::USAGE_CONFIG]), true,
			'defaultConnection');

		$this->cliHelper->addSwitch('r', 'root-namespace', 'The root namespace to use',
			array($this->usageIndexes[self::USAGE_FULL], $this->usageIndexes[self::USAGE_CONFIG]), false,
			'namespace');

		$this->cliHelper->addSwitch('n','db-namespace', 'The database namespace to use',
			array($this->usageIndexes[self::USAGE_FULL], $this->usageIndexes[self::USAGE_CONFIG]), false,
			'namespace');
	}

	/**
	 * Parses the switches used by the script.
	 *
	 * @param array $switches   The parsed switches.
	 *
	 * @return bool   Returns TRUE, if the validation of the provided switches was successful.
	 *
	 * @throws \YapepBase\Exception\ParameterException   On error.
	 */
	protected function parseSwitches(array $switches) {
		parent::parseSwitches($switches);
		if (self::HELP_USAGE == $this->currentUsage) {
			return;
		}

		$haveError = false;

		if (empty($switches['c']) && empty($switches['connection-name'])) {
			$this->currentUsage = self::USAGE_FULL;
			if (!empty($switches['host']) || !empty($switches['h'])) {
				$this->host = (empty($switches['host']) ? $switches['h'] : $switches['host']);
			}

			if (!empty($switches['user']) || !empty($switches['u'])) {
				$this->user = (empty($switches['user']) ? $switches['u'] : $switches['user']);
			}

			if (!empty($switches['password']) || !empty($switches['p'])) {
				$this->password = (empty($switches['password']) ? $switches['p'] : $switches['password']);
			}

			if (!empty($switches['port']) || !empty($switches['P'])) {
				$this->port = (int)(empty($switches['port']) ? $switches['P'] : $switches['port']);
			}

			if (empty($switches['db-name']) && empty($switches['d'])) {
				$haveError = true;
			} else {
				$this->dbName = (empty($switches['db-name']) ? $switches['d'] : $switches['db-name']);
			}

			$this->defaultConnection = empty($switches['default-connection'])
				? ''
				: $switches['default-connection'];
		} else {
			$this->currentUsage = self::USAGE_CONFIG;
			$connectionName = (empty($switches['connection-name']) ? $switches['c'] : $switches['connection-name']);
			$connection     = Config::getInstance()->get('resource.database.' . $connectionName . '.ro.*');
			if (empty($connection) || !isset($connection['host']) || !isset($connection['database'])) {
				$this->cliHelper->setErrorMessage('Invalid connection configuration');
				$haveError = true;
			}
			$this->host   = $connection['host'];
			$this->dbName = $connection['database'];

			if (!empty($connection['user'])) {
				$this->user = $connection['user'];
			}

			if (!empty($connection['password'])) {
				$this->password = $connection['password'];
			}

			if (!empty($connection['port'])) {
				$this->port = $connection['port'];
			}
		}

		if (empty($switches['table']) && empty($switches['t'])) {
			$haveError = true;
		} else {
			$this->tableName = (empty($switches['table']) ? $switches['t'] : $switches['table']);
		}

		if (empty($switches['root-namespace']) && empty($switches['r'])) {
			$haveError = true;
		} else {
			$this->rootNamespace = (empty($switches['root-namespace']) ? $switches['r'] : $switches['root-namespace']);
		}

		if (empty($switches['db-namespace']) && empty($switches['n'])) {
			$haveError = true;
		} else {
			$this->dbNamespace = (empty($switches['db-namespace']) ? $switches['n'] : $switches['db-namespace']);
		}

		if ($haveError) {
			echo $this->cliHelper->getUsageOutput();
			$this->currentUsage = null;
		}
	}

	/**
	 * Sets up the database connection variables
	 *
	 * @param string $connectionName   The name of the connection
	 */
	protected function setupDbConnections($connectionName) {
		$config = Config::getInstance();
		$config->set(array(
			'resource.database.' . $connectionName . '.ro.backendType' => 'mysql',
			'resource.database.' . $connectionName . '.ro.port'        => $this->port,
			'resource.database.' . $connectionName . '.ro.host'        => $this->host,
			'resource.database.' . $connectionName . '.ro.user'        => $this->user,
			'resource.database.' . $connectionName . '.ro.password'    => $this->password,
			'resource.database.' . $connectionName . '.ro.database'    => 'information_schema',
			'resource.database.' . $connectionName . '.ro.charset'     => 'utf8',
		));
	}

	/**
	 * Executes the script
	 */
	public function execute() {
		if (empty($this->currentUsage)) {
			return;
		}

		$this->setupDbConnections('table-generator');

		$bo           = new TableGeneratorBo();
		$enums        = array();
		$fields       = array();
		$tableComment = null;
		$bo->getTableStructure('table-generator', $this->dbName, $this->tableName, $enums, $fields, $tableComment);
		Application::getInstance()->getDiContainer()->getViewDo()->set(array(
			'fields'            => $fields,
			'enums'             => $enums,
			'rootNamespace'     => $this->rootNamespace,
			'dbNamespace'       => $this->dbNamespace,
			'tableName'         => $this->tableName,
			'defaultConnection' => $this->defaultConnection,
			'tableComment'      => $tableComment,
		));
		$template = new TableGeneratorTemplate('fields', 'enums', 'rootNamespace', 'dbNamespace', 'tableName',
			'defaultConnection', 'tableComment');
		$template->render();
		// Send structure to a View to render the php class
	}
}

$controller = new TableGeneratorController();
$controller->run();