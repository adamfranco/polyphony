<?php
/**
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_datetime.class.php,v 1.8 2007/09/19 14:04:44 adamfranco Exp $
 */

/**
 * 
 *
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_datetime.class.php,v 1.8 2007/09/19 14:04:44 adamfranco Exp $
 */
class PrimitiveIO_datetime extends WTextField /* implements PrimitiveIO */ {

	function __construct() {
		$this->setErrorText(dgettext("polyphony", "Enter a date/time string. (example: YYYY-MM-DD HH:MM:SS)"));
// 		$this->setErrorRule(new WECNonZeroRegex("[\\w]+"));
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE. Validate should be called usually before a save event
	 * is handled, to make sure everything went smoothly. 
	 * @access public
	 * @return boolean
	 */
	function validate () {
		if (is_null($this->_value))
			return false;
		
		$parse = StringParser::getParserFor($this->_value);
		if (!$parse) {
			$this->_showError = true;
			return false;
		}
		return true;
	}

	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValue($value)
	{
		parent::setValue($value->asString());
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
		if (is_null($this->_value))
			return null;
		
		$obj = DateAndTime::fromString($this->_value);
// 		print "<pre>"; var_dump($this->_value); print "</pre>";
// 		printpre($obj);
		return $obj;
	}
}