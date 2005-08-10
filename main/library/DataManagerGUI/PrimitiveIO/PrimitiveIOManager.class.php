<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIOManager.class.php,v 1.5 2005/08/10 13:27:04 gabeschine Exp $
 */

/**
 * Handles the creation of {@link PrimitiveIO} objects for different data types, as registered with the DataTypeManager of Harmoni.
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIOManager.class.php,v 1.5 2005/08/10 13:27:04 gabeschine Exp $
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

		return new $class();
	}
	
}
