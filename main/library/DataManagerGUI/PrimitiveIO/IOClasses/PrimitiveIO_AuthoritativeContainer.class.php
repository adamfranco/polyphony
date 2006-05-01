<?php
/**
 * @since 5/1/06
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_AuthoritativeContainer.class.php,v 1.1 2006/05/01 17:43:10 adamfranco Exp $
 */ 

/**
 * The authoritavieContainer holds multiple fields to allow for choosing from
 * a list or adding a new value. Its children are the list and the new value
 * 
 * @since 5/1/06
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_AuthoritativeContainer.class.php,v 1.1 2006/05/01 17:43:10 adamfranco Exp $
 */
class PrimitiveIO_AuthoritativeContainer
	extends WSelectOrNew
{
	
	/**
     * Set the value of the input component
     * 
     * @param string $value
	 * @access public
	 * @return void
     * @since 10/21/05
     */
    function setValue (&$value) {
    	if ($this->_select->isOption($value)) {
			$this->_select->setValue($value);
			$this->_new->setValue(String::fromString(''));
		} else {
			$this->_select->setValue(String::fromString(''));
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
    	$hasMethods =& HasMethodsValidatorRule::getRule("setSize");
    	
		if ($hasMethods->check($this->_new))
			$this->_new->setSize(40);
    	if ($hasMethods->check($this->_select))
			$this->_select->setSize(40);
    }
	
	/**
	 * Add an option to our choose list
	 * 
	 * @param object SObject $valueObject
	 * @return void
	 * @access public
	 * @since 5/1/06
	 */
	function addOptionFromSObject ( &$valueObject ) {
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
	function setValueFromSObject(&$value)
	{
		$this->setValue($value);
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
		$newValue =& $this->_new->getAllValues();
		if ($newValue->asString())
			$this->setValue($this->_new->getAllValues());
	}	
}

?>