<?php
/**
 * @since 4/28/06
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSelectOrNew.class.php,v 1.4.2.3 2006/11/13 21:55:19 adamfranco Exp $
 */ 

/**
 * <##>
 * 
 * @since 4/28/06
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSelectOrNew.class.php,v 1.4.2.3 2006/11/13 21:55:19 adamfranco Exp $
 */
class WSelectOrNew
	extends WizardComponentWithChildren 
{

    var $_select;
    var $_new;
    var $_label;
		
	/**
	 * Constructor
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 4/28/06
	 */
	function WSelectOrNew () {
		$this->_select =& new WSelectList;
		$this->_select->setParent($this);
		$this->_new =& new WTextField;
		$this->_new->setParent($this);
		
		$this->_init();
	}
	
	/**
	 * Initialize our fields
	 * 
	 * @return void
	 * @access public
	 * @since 6/2/06
	 */
	function _init () {
		$this->_select->addOption('__NEW_VALUE__', (dgettext("polyphony", "* New Value *")));
		$this->_select->setValue('__NEW_VALUE__');
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
    	if ($this->_select->isOption($value)) {
    		$this->_select->setValue($value);
    		$this->_new->setValue('');
    	} else {
	    	$this->_select->setValue('__NEW_VALUE__');
    		$this->_new->setValue($value);
    	}
    }
    
    /**
	 * Sets if this component will be enabled or disabled.
	 * @param boolean $enabled
	 * @param boolean $sticky If true, future calls to setEnabled without sticky
	 *							will have no effect.
	 * @access public
	 * @return void
	 */
	function setEnabled ($enabled, $sticky = false) {
		$this->_select->setEnabled($enabled, $sticky);
		$this->_new->setEnabled($enabled, $sticky);
	}
	
	/**
     * Set the input component
     * 
     * @param object WComponent $input
     * @return object WComponent
     * @access public
     * @since 10/20/05
     */
    function &setSelectComponent ( &$input ) {
    	ArgumentValidator::validate($input,
    		ExtendsValidatorRule::getRule("WizardComponent"));
		ArgumentValidator::validate($input, 
			HasMethodsValidatorRule::getRule("addOnChange"));
		
		$this->_select =& $input;
		$this->_select->setParent($this);
		
		$this->_init();
		
		return $this->_select;
    }
	
	/**
     * Set the input component
     * 
     * @param object WComponent $input
     * @return object WComponent
     * @access public
     * @since 10/20/05
     */
    function &setNewComponent ( &$input ) {
    	ArgumentValidator::validate($input,
    		ExtendsValidatorRule::getRule("WizardComponent"));
		
		$this->_new =& $input;
		$this->_new->setParent($this);
		
		return $this->_new;
    }
	
	/**
	 * Adds an option to this list.
	 * @param string $value The short value that represents the displayed text.
	 * @param string $displayText The text to show to the end user.
	 * @access public
	 * @return void
	 */
	function addOption ($value, $displayText) {
		$this->_select->addOption($value, $displayText);
	}
	
	/**
 	 * $this is a shallow copy, subclasses should override to copy fields as 
 	 * necessary to complete the full copy.
 	 * 
 	 * @return object
 	 * @access public
 	 * @since 7/11/05
 	 */
 	function &postCopy () {
 		$this->_select =& $this->_select->copy();
 		$this->_new =& $this->_new->copy();
 		return $this;
 	}
 	
 	/**
	 * Sets the label for this checkbox element.
	 * @param string $label;
	 * @access public
	 * @return void
	 */
	function setLabel ($label) {
		$this->_label = $label;
	}
 	
 	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE. Validate should be called usually before a save event
	 * is handled, to make sure everything went smoothly. 
	 * @access public
	 * @return boolean
	 */
	function validate () {
		return ($this->_select->validate() || $this->_new->validate());
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
		if ($this->isUsingNewValue() && $this->_new->getAllValues())
			$this->setValue($this->_new->getAllValues());
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		if ($this->isUsingNewValue())
			return $this->_new->getAllValues();
		else
			return $this->_select->getAllValues();
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
		
		if ($this->_select->getAllValues() == '__NEW_VALUE__')
			return true;
		
		if  ($this->_select->getAllValues() == '')
			return true;
			
		return false;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		$m = "";
		$m .= "\n\t<div title='".$this->_label."' style='vertical-align: top; padding: 0px; margin: 0px;'>";
		
		$newId = RequestContext::name($fieldName."_new");
		
		if ($this->isUsingNewValue())
			$display = " display: block;";
		else
			$display = " display: none;";
		
		$this->_select->addOnChange("var newField = document.get_element_by_id('$newId'); if (this.value == '__NEW_VALUE__') { newField.style.display = 'block'; } else { newField.style.display = 'none'; }");
		
		$m .= "\n\t\t".$this->_select->getMarkup($fieldName."_select");
		
		$m .= "\n\t</div>";
		
		$m .= "\n\t<div id='$newId' style='padding: 0px; margin: 0px; $display'>";
		
		$m .= "\n\t\t".$this->_new->getMarkup($fieldName."_new");
		
		$m .= "\n\t</div>";
		return $m;
	}

}

?>