<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_strings.classes.php,v 1.5 2005/08/10 13:27:04 gabeschine Exp $
 */

/**
 * 
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_strings.classes.php,v 1.5 2005/08/10 13:27:04 gabeschine Exp $
 */
class PrimitiveIO_shortstring extends WTextField {

	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValueFromSObject(&$value)
	{
		$this->setValue($value->asString());
	}

	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return new String($this->_value?$this->_value:"");
	}
	
}

/**
 * 
 * @package polyphony.library.datamanager_gui
 */
class PrimitiveIO_string extends WTextArea {
	
	function PrimitiveIO_string () {
		$this->setRows(5);
		$this->setColumns(70);
	}
	
	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValueFromSObject(&$value)
	{
		$this->setValue($value->asString());
	}

	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return new String($this->_value?$this->_value:"");
	}
}
