<?PHP

/**
 * This file includes all necessary Polyphony classes
 *
 * @version $Id: polyphony.inc.php,v 1.5 2004/08/06 21:52:31 adamfranco Exp $
 * @copyright 2004 Middlebury College
 * @package polyphony
 * @access public
 **/

define("POLYPHONY", dirname(__FILE__));

require_once(dirname(__FILE__)."/main/library/Wizard/Wizard.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/IteratorResultPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/ArrayResultPrinter.class.php");

?>