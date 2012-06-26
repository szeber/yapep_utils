<?php
use YapepBase\Application;
use YapepBase\DependencyInjection\SystemContainer;
use YapepBase\ErrorHandler\EchoErrorHandler;

define('APP_ROOT', __DIR__);

$opts = getopt('e:');
if (!empty($opts['e'])) {
	if (is_array($opts['e'])) {
		$opts['e'] = reset($opts['e']);
	}
	if ('dev' != $opts['e']) {
		define ('ENVIRONMENT', $opts['e']);
	}
}

require(__DIR__ . '/../../bootstrap.php');
require(__DIR__ . '/config.php');

$diContainer = Application::getInstance()->getDiContainer();

// Set search namespaces
$diContainer->setSearchNamespaces(SystemContainer::NAMESPACE_SEARCH_CONTROLLER, array(
	'\\' . basename(__DIR__) . '\\Controller',
));

$diContainer->setSearchNamespaces(SystemContainer::NAMESPACE_SEARCH_BO, array(
	'\\' . basename(__DIR__) . '\\BusinessObject',
));

$diContainer->setSearchNamespaces(SystemContainer::NAMESPACE_SEARCH_DAO, array(
	'\\' . basename(__DIR__) . '\\Dao',
));

$diContainer->getErrorHandlerRegistry()->addErrorHandler(new EchoErrorHandler());
