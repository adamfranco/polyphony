<?php

/**
 * This file includes all necessary Polyphony classes
 *
 * @version $Id: polyphony.inc.php,v 1.13 2005/04/04 19:55:47 adamfranco Exp $
 * @copyright 2004 Middlebury College
 * @package polyphony
 * @access public
 */

/**
 * Define a constant for the Polyphony root directory.
 * 
 */
define("POLYPHONY", dirname(__FILE__));

/**
 * Include our library classes
 * 
 */
require_once(dirname(__FILE__)."/main/library/Wizard/Wizard.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/IteratorResultPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/ArrayResultPrinter.class.php");


if (OKI_VERSION > 1) { 
	require_once(dirname(__FILE__)."/main/library/RepositoryInputOutputModules/RepositoryInputOutputModuleManager.class.php");
	Services::registerService("InOutModules", "RepositoryInputOutputModuleManager");
	
	require_once(dirname(__FILE__)."/main/library/RepositorySearchModules/RepositorySearchModuleManager.class.php");
	Services::registerService("RepositorySearchModules", "RepositorySearchModuleManager");
} else {
	require_once(dirname(__FILE__)."/main/library/DRInputOutputModules/DRInputOutputModuleManager.class.php");
	Services::registerService("InOutModules", "DRInputOutputModuleManager");
	
	require_once(dirname(__FILE__)."/main/library/DRSearchModules/DRSearchModuleManager.class.php");
	Services::registerService("RepositorySearchModules", "DRSearchModuleManager");
}

require_once(OKI2."osid/OsidContext.php");
$context =& new OsidContext;
$context->assignContext('harmoni', $harmoni);
require_once(HARMONI."oki2/shared/ConfigurationProperties.class.php");
$configuration =& new ConfigurationProperties;
Services::startManagerAsService("InOutModules", $context, $configuration);
Services::startManagerAsService("RepositorySearchModules", $context, $configuration);

require_once(dirname(__FILE__)."/main/library/HierarchyPrinter/GroupPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/HierarchyPrinter/HierarchyPrinter.class.php");
?>