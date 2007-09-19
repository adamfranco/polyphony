<?php
/**
 * @since 5/1/06
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_AuthoritativeContainer.class.php,v 1.7 2007/09/19 14:04:44 adamfranco Exp $
 */ 

/**
 * The authoritavieContainer holds multiple fields to allow for choosing from
 * a list or adding a new value. Its children are the list and the new value
 * 
 * @since 5/1/06
 * @package polyphony.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_AuthoritativeContainer.class.php,v 1.7 2007/09/19 14:04:44 adamfranco Exp $
 */
class PrimitiveIO_AuthoritativeContainer
	extends WSelectOrNew
{
	/**
	 * Initialize our fields
	 * 
	 * @return void
	 * @access public
	 * @since 6/2/06
	 */
	function _init () {
		$this->_select->addOption('__NEW_VALUE__', (dgettext("polyphony", "* New Value *")));
		$this->_select->setValue(String::fromString('__NEW_VALUE__'));
	}
	
	/**
     * Set the value of the input component
     * 
     * @param string $value
	 * @access public
	 * @return void
     * @since 10/21/05
     */
    function setValue ($value) {
    	if (is_string($value))
    		$value = String::fromString($value);
    		
    	if ($this->_select->isOption($value)) {
			$this->_select->setValue($value);
			$this->_new->setValue(String::fromString(''));
		} else {
			$this->_select->setValue(String::fromString('__NEW_VALUE__'));
			$this->_new->setValue($value);
		}
    }
    
    /**
     * Set the size of the new input components
     * 
     * @param integer $size
     * @return void
     * @access public
     * @since 5/1/06
     */
    function setSize ($size) {
    	$hasMethods = HasMethodsValidatorRule::getRule("setSize");
    	
		if ($hasMethods->check($this->_new))
			$this->_new->setSize(40);
    	if ($hasMethods->check($this->_select))
			$this->_select->setSize(40);
    }
    
    /**
	 * Sets the javascript onchange attribute.
	 * @param string $commands
	 * @access public
	 * @return void
	 */
	function addOnChange($commands) {
		$this->_select->addOnChange($commands);
		$this->_new->addOnChange($commands);
	}
	
	/**
	 * Sets the text of the field to display until the user enters the field.
	 * @param string $text
	 * @access public
	 * @return void
	 */
	function setStartingDisplayText ($text) {
		$this->_select->setStartingDisplayText($text);
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
		if (!isset($this->_select))
			throwError(new Error("No Select Child Available.", "datamanager GUI"));
			
		$this->_select->addOptionFromSObject($valueObject);
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
	 * Return true if we should be using the new value rather than the select
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/2/06
	 */
	function isUsingNewValue () {
		if ($this->_select->isStartingDisplay())
			return false;
		
		$newOption = String::fromString('__NEW_VALUE__');
		$emptyOption = String::fromString('');
// 		print "<pre>"; var_dump($this->_select->getAllValues()); print "</pre>";
		return (!$this->_select->getAllValues() 
				|| $newOption->isEqualTo($this->_select->getAllValues()) 
				|| $emptyOption->isEqualTo($this->_select->getAllValues()));
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
		$this->_select->update($fieldName."_select");
		$this->_new->update($fieldName."_new");
		$newValue =$this->_new->getAllValues();
		if ($this->isUsingNewValue() && is_object($newValue) && $newValue->asString())
			$this->setValue($this->_new->getAllValues());
		
		return true;
	}	
}

?>