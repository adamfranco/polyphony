<?PHP

/**
 * This file includes all necessary Polyphony classes
 *
 * @version $Id: polyphony.inc.php,v 1.7 2004/10/29 20:22:29 adamfranco Exp $
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

?>