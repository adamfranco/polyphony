<?php
/**
 * @since 5/1/06
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_Authoritative.class.php,v 1.4 2007/09/04 20:27:58 adamfranco Exp $
 */ 

/**
 * This class implements standard methods needed by the Authoritative versions
 * of the primitive IO classes, for behaving as select lists.
 * 
 * @since 5/1/06
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_Authoritative.class.php,v 1.4 2007/09/04 20:27:58 adamfranco Exp $
 */
class PrimitiveIO_Authoritative 
	extends WSelectList
{
		
	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValue($value)
	{
		if (is_object($value)) {
			ArgumentValidator::validate($value, HasMethodsValidatorRule::getRule("asString"));
			parent::setValue($value->asString());
		} else
			parent::setValue($value);
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
	 * Answer true if the value passed is a valid option
	 * 
	 * @param string $value
	 * @return boolean
	 * @access public
	 * @since 4/28/06
	 */
	function isOption ($value) {
		if (is_object($value)) {
			ArgumentValidator::validate($value, HasMethodsValidatorRule::getRule("asString"));
			return parent::isOption($value->asString());
		} else
			return parent::isOption($value);
	}
	
	/**
	 * Add an option to our choose list
	 * 
	 * @param object SObject $valueObject
	 * @return void
	 * @access public
	 * @since 5/1/06
	 */
	function addOptionFromSObject ( $valueObject ) {
		ArgumentValidator::validate($valueObject, ExtendsValidatorRule::getRule('SObject'));
		$this->addOption($valueObject->asString(), $valueObject->asString());
	}
	
}

?>