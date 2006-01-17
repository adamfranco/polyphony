<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIOManager.class.php,v 1.6 2006/01/17 20:06:40 adamfranco Exp $
 */

/**
 * Handles the creation of {@link PrimitiveIO} objects for different data types, as registered with the DataTypeManager of Harmoni.
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIOManager.class.php,v 1.6 2006/01/17 20:06:40 adamfranco Exp $
 * @author Gabe Schine
 */
class PrimitiveIOManager {
	
	/**
	 * Creates a new {@link PrimitiveIO} object for the given dataType.
	 * @param string $dataType a datatype string as registered with the DataManager of Harmoni
	 * @return ref object A new {@link PrimitiveIO} object.
	 * @access public
	 * @static
	 */
	function &createComponent($dataType) {
		$class = "PrimitiveIO_".$dataType;
		if (!class_exists($class)) return ($null=null);

		$obj =& new $class();

		return $obj;
	}
	
}
