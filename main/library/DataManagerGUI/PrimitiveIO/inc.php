<?php
/**
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: inc.php,v 1.3 2005/08/10 13:27:04 gabeschine Exp $
 */

/**
 * Require all of our necessary files
 * 
 */
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/PrimitiveIO.interface.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/PrimitiveIOManager.class.php";

require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_strings.classes.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_numeric.classes.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_boolean.class.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_blob.class.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_datetime.class.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_okitype.class.php";
