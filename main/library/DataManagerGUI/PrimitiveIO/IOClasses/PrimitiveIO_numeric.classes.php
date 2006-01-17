<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_numeric.classes.php,v 1.7 2006/01/17 20:06:41 adamfranco Exp $
 */

/**
 * Require all of our necessary files
 * 
 */
require_once(POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_strings.classes.php");

/**
 * 
 * @package polyphony.library.datamanager_gui
 */
class PrimitiveIO_integer extends PrimitiveIO_shortstring {

	function PrimitiveIO_integer() {
		$this->setErrorText(dgettext("polyphony", "Enter a valid integer (no commas)."));
		$this->setErrorRule(new WECRegex("^[0-9]+\$"));
	}

	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValue(&$value)
	{
		parent::setValue($value->asString());
	}

	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValueFromSObject(&$value)
	{
		$this->setValue($value);
	}

	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function &getAllValues () {
		$obj =& Integer::withValue($this->_value?intval($this->_value):0);
		return $obj;
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE. Validate should be called usually before a save event
	 * is handled, to make sure everything went smoothly. 
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$val = ereg("^[0-9]+\$", $this->_value);
		if (!$val) $this->_showError = true;
		return $val;
	}
}

/**
 * 
 * @package polyphony.library.datamanager_gui
 */
class PrimitiveIO_float extends PrimitiveIO_integer {

	function PrimitiveIO_float() {
		$this->setErrorText(dgettext("polyphony", "Enter a valid integer (no commas)."));
		$this->setErrorRule(new WECRegex("^[0-9\\.]+\$"));
	}

	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValue(&$value)
	{
		parent::setValue($value->asString());
	}

	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValueFromSObject(&$value)
	{
		$this->setValue($value);
	}

	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function &getAllValues () {
		$obj =& Float::withValue($this->_value?floatval($this->_value):0);
		return $obj;
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE. Validate should be called usually before a save event
	 * is handled, to make sure everything went smoothly. 
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$val = ereg("^[0-9\\.]+\$", $this->_value);
		if (!$val) $this->_showError = true;
		return $val;
	}
}
