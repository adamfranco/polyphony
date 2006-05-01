<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_Authoritative_strings.classes.php,v 1.1 2006/05/01 17:43:10 adamfranco Exp $
 */

/**
 * 
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_Authoritative_strings.classes.php,v 1.1 2006/05/01 17:43:10 adamfranco Exp $
 */
class PrimitiveIO_Authoritative_string
	extends PrimitiveIO_Authoritative 
	/* implements PrimitiveIO */
{

	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function &getAllValues () {
		$obj =& new String($this->_value?$this->_value:"");
		return $obj;
	}
	
}

/**
 * 
 * @package polyphony.library.datamanager_gui
 */
class PrimitiveIO_Authoritative_shortstring 
	extends PrimitiveIO_Authoritative_string 
{}
