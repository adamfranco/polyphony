<?PHP

/**
 * This file includes all necessary Polyphony classes
 *
 * @version $Id: polyphony.inc.php,v 1.9 2004/11/11 21:57:48 rrichards Exp $
 * @copyright 2004 Middlebury College
 * @package polyphony
 * @access public
 **/

define("POLYPHONY", dirname(__FILE__));

require_once(dirname(__FILE__)."/main/library/Wizard/Wizard.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/IteratorResultPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/ArrayResultPrinter.class.php");

require_once(dirname(__FILE__)."/main/library/DRInputOutputModules/DRInputOutputModuleManager.class.php");
Services::registerService("InOutModules", "DRInputOutputModuleManager");
Services::startService("InOutModules");

require_once(dirname(__FILE__)."/main/library/DRSearchModules/DRSearchModuleManager.class.php");
Services::registerService("DRSearchModules", "DRSearchModuleManager");
Services::startService("DRSearchModules");

require_once(dirname(__FILE__)."/main/library/HierarchyPrinter/GroupPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/HierarchyPrinter/HierarchyPrinter.class.php");
?>