<?php
/**
 * Basic configurations
 */

use YapepBase\Config;


Config::getInstance()->set(array(
	// Error logging
	'resource.log.error.facility'            => LOG_LOCAL5,
	'resource.log.error.applicationIdent'    => PROGRAM_NAME,
	'resource.log.error.includeSapiName'     => false,
	'resource.log.error.addPid'              => true,


	'resource.storage.debugData.path'           => '/var/log/application/' . PROGRAM_NAME . '/error',
	'resource.storage.debugData.storePlainText' => true,
	'resource.storage.debugData.fileSuffix'     => '.log',


	// Db configs
	'system.database.paramPrefix' => '_',
));