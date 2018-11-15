<?php
/**
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_boolean.class.php,v 1.10 2007/09/19 14:04:44 adamfranco Exp $
 */

/**
 * 
 *
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_boolean.class.php,v 1.10 2007/09/19 14:04:44 adamfranco Exp $
 */
class PrimitiveIO_boolean extends WRadioList /* implements PrimitiveIO */ {
	
	function __construct () {
		$this->_eachPost = '';
		
		$this->addOption("1", "true");
		$this->addOption("0", "false");
		$zero = Boolean::withValue("0");
		$this->setValue($zero);
	}
	
	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValue($value)
	{
		parent::setValue($value->value()?"1":"0");
	}
	
	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValueFromSObject($value)
	{
		$this->setValue($value);
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$obj = new Boolean($this->_value=="1"?true:false);
		return $obj;
	}

}