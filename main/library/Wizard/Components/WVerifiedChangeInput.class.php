<?php
/**
 * @since 2005/10/20
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WVerifiedChangeInput.class.php,v 1.11 2007/09/19 14:04:51 adamfranco Exp $
 */ 

/**
 * This component provides a checkbox next to the input field with which the
 * user can confirm that they wish to change this field. This is useful when
 * making forms which allow for the editing of many fields across multiple items
 * where the user may only wish to change one of the fields across all items.
 * 
 * @since 2005/10/20
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WVerifiedChangeInput.class.php,v 1.11 2007/09/19 14:04:51 adamfranco Exp $
 */

class WVerifiedChangeInput 
	extends WizardComponentWithChildren 
{

    var $_input;
    var $_checkbox;
    var $_label;
    
/*********************************************************
 * Class Methods - Instance creation
 *********************************************************/
 
	/**
	 * Create a new VerifiedChangeInput with the component specified
	 * 
	 * @param object WComponent $input
	 * @return object
	 * @access public
	 * @since 10/20/05
	 */
	function withInputComponent ( $input ) {
		$obj = new WVerifiedChangeInput();
		$obj->setInput($input);
		return $obj;
	}
 
/*********************************************************
 * Instance Methods
 *********************************************************/
 	
 	/**
 	 * $this is a shallow copy, subclasses should override to copy fields as 
 	 * necessary to complete the full copy.
 	 * 
 	 * @return object
 	 * @access public
 	 * @since 7/11/05
 	 */
 	function postCopy () {
 		$this->_checkbox =$this->_checkbox->copy();
 		$this->_input =$this->_input->copy();
 		return $this;
 	}
    
    /**
     * Constructor
     * 
     * @return object
     * @access public
     * @since 10/20/05
     */
    function WVerifiedChangeInput() {
    	$this->_checkbox = new WCheckBox;
    	$this->_checkbox->setParent($this);
    	$this->_label = dgettext("polyphony", "Apply to All");
    	$this->setChecked(true);
    }
    
    /**
     * Set the input component
     * 
     * @param object WComponent $input
     * @return object WComponent
     * @access public
     * @since 10/20/05
     */
    function setInputComponent ( $input ) {
    	ArgumentValidator::validate($input,
    		ExtendsValidatorRule::getRule("WizardComponent"));
		ArgumentValidator::validate($input, 
			HasMethodsValidatorRule::getRule("addOnChange"));
		
		$this->_input =$input;
		$this->_input->setParent($this);
		
		return $this->_input;
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
    	if (is_array($value)) {
    		$this->_checkbox->setValue($value['checked']);
    		$this->_input->setValue($value['value']);
    	} else
	    	$this->_input->setValue($value);
    }
    
    /**
	 * Sets the text of the field to display until the user enters the field.
	 * @param string $text
	 * @access public
	 * @return void
	 */
	function setStartingDisplayText ($text) {
		$this->_input->setStartingDisplayText($text);
	}
    
    /**
     * Set the checked state of the checkbox
     * 
     * @param boolean $checked
     * @return void
     * @access public
     * @since 10/24/05
     */
    function setChecked ($checked) {
    	$this->_checkbox->setValue($checked);
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
		$this->_input->setEnabled($enabled, $sticky);
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
		return $this->_input->validate();
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
		$this->_checkbox->update($fieldName."_checked");
		return $this->_input->update($fieldName);
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$array = array();
		$array['checked'] = $this->_checkbox->getAllValues();
		$array['value'] = $this->_input->getAllValues();
		
		return $array;
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
		if (isset($this->_input->_startingDisplay)) {
			$v = htmlspecialchars($this->_input->_startingDisplay, ENT_QUOTES);
			$this->_input->addOnChange(
				"if (this.value != '$v') {".$this->_checkbox->getCheckJS($fieldName."_checked")."}");
		} else {
			$this->_input->addOnChange($this->_checkbox->getCheckJS($fieldName."_checked"));
		}
			
		$m = "\n<table><tr>";
		$m .= "\n\t<td title='".$this->_label."' style='vertical-align: top; padding: 0px; margin: 0px;'>";
		
		$m .= "\n\t\t".$this->_checkbox->getMarkup($fieldName."_checked");
		
		$m .= "\n\t</td>\n\t<td style='padding: 0px; margin: 0px;'>";
		
		$m .= "\n\t\t".$this->_input->getMarkup($fieldName);
		
		$m .= "\n\t</td>";
		$m .= "\n</tr></table>";
		return $m;
	}
    
}
?>