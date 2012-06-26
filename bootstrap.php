<?php
/**
 * Basic bootstrap commands what every applications should use.
 */

use YapepBase\ErrorHandler\LoggingErrorHandler;
use YapepBase\ErrorHandler\StrictErrorHandler;
use YapepBase\Storage\FileStorage;
use YapepBase\ErrorHandler\DebugDataCreator;
use YapepBase\Config;
use YapepBase\Autoloader\SimpleAutoloader;
use YapepBase\Event\Event;
use YapepBase\Application;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\Log\SyslogLogger;

/** Environment name for development. */
define('ENVIRONMENT_DEV', 'dev');
/** Environment name for stage. */
define('ENVIRONMENT_STAGE', 'stage');
/** Environment name for production. */
define('ENVIRONMENT_PRODUCTION', 'production');

// If the ENVIRONMENT is not defined, it means that the script is not running on a stage or a live server
if (!defined('ENVIRONMENT')) {

	// If no environment is defined, define it as development
	define('ENVIRONMENT', ENVIRONMENT_DEV);
}
/** The repo root directory */
define('ROOT_DIR', realpath(__DIR__));

/** The base class directory */
define('BASE_DIR', realpath(ROOT_DIR . '/class'));

/** The base directory of the third party tools */
define('VENDOR_DIR', realpath(ROOT_DIR . '/vendor'));

/** The name of the application */
define('PROGRAM_NAME', 'yapep_utils');

$vendorClasspaths = require_once VENDOR_DIR . '/composer/autoload_namespaces.php';

define('YAPEP_BASE_DIR', $vendorClasspaths['YapepBase']);

/** Require the simple autoloader */
require_once YAPEP_BASE_DIR . '/YapepBase/Autoloader/AutoloaderBase.php';
require_once YAPEP_BASE_DIR . '/YapepBase/Autoloader/SimpleAutoloader.php';
require_once YAPEP_BASE_DIR . '/YapepBase/Autoloader/AutoloaderRegistry.php';

// Autoloader setup
$autoloader = new SimpleAutoloader();
if (defined('APP_ROOT')) {
	$autoloader->addClassPath(APP_ROOT . '/class');
}

$autoloader->addClassPath(BASE_DIR);

// Add libraries tot the autoloader path
$autoloader->addClassPath(array_values($vendorClasspaths));

$autoloader->register();

/** Require the config */
require_once __DIR__ . '/config.php';

$application = Application::getInstance();

// Error handlers are disabled, uncomment them to use the default ones. See the error handler documentation for details.
/*
$application->getDiContainer()->getErrorHandlerRegistry()->addErrorHandler(new LoggingErrorHandler(
	new SyslogLogger('error')
));


$application->getDiContainer()->getErrorHandlerRegistry()->addErrorHandler(new DebugDataCreator(
	new FileStorage('debugData')
));
*/

// Clean up the global scope
unset($autoloader, $vendorClasspaths);
