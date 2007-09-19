<?php
/**
 * Require all of our necessary files
 *
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: inc.php,v 1.6 2007/09/19 14:04:44 adamfranco Exp $
 */

require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/PrimitiveIO.interface.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/PrimitiveIOManager.class.php";

require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_strings.classes.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_numeric.classes.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_boolean.class.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_blob.class.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_datetime.class.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_okitype.class.php";

require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_Authoritative.class.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_AuthoritativeContainer.class.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_Authoritative_strings.classes.php";
require_once POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_Authoritative_datetime.class.php";
