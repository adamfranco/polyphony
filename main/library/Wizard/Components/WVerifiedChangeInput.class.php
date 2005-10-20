<?php
/**
 * @since 2005/10/20
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WVerifiedChangeInput.class.php,v 1.2 2005/10/20 19:40:14 adamfranco Exp $
 */ 

/**
 * This component provides a checkbox next to the input field with which the
 * user can confirm that they wish to change this field. This is useful when
 * making forms which allow for the editing of many fields across multiple items
 * where the user may only wish to change one of the fields across all items.
 * 
 * @since 2005/10/20
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WVerifiedChangeInput.class.php,v 1.2 2005/10/20 19:40:14 adamfranco Exp $
 */

class WVerifiedChangeInput 
	extends WizardComponentWithChildren 
{

    var $_input;
    var $_checkbox;
    
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
	function &withInputComponent ( &$input ) {
		$obj =& new WVerifiedChangeInput();
		$obj->setInput($input);
		return $obj;
	}
 
/*********************************************************
 * Instance Methods
 *********************************************************/
    
    /**
     * Constructor
     * 
     * @return object
     * @access public
     * @since 10/20/05
     */
    function WVerifiedChangeInput() {
    	$this->_checkbox =& new WCheckBox;
    	$this->_checkbox->setParent($this);
    }
    
    /**
     * Set the input component
     * 
     * @param object WComponent $input
     * @return object WComponent
     * @access public
     * @since 10/20/05
     */
    function &setInputComponent ( &$input ) {
    	ArgumentValidator::validate($input,
    		ExtendsValidatorRule::getRule("WizardComponent"));
		ArgumentValidator::validate($input, 
			HasMethodsValidatorRule::getRule("setOnChange"));
		
		$this->_input =& $input;
		$this->_input->setParent($this);
		
		return $this->_input;
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
		$this->_checkbox->update($fieldName."_modify");
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
		$array['modify'] = $this->_checkbox->getAllValues();
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
		if ($this->_input->_startingDisplay) {
			$v = htmlentities($this->_input->_startingDisplay, ENT_QUOTES);
			$this->_input->setOnChange(
				"if (this.value != '$v') {".$this->_checkbox->getCheckJS($fieldName."_modify")."}");
		} else {
			$this->_input->setOnChange($this->_checkbox->getCheckJS($fieldName."_modify"));
		}
			
		$m = "\n<div>";
		$m .= "\n\t<div title='".dgettext("polyphony", "Modify")."' style='display: inline; vertical-align: top'>";
		
		$m .= "\n\t\t".$this->_checkbox->getMarkup($fieldName."_modify");
		
		$m .= "\n\t</div>\n\t<div style='display: inline; '>";
		
		$m .= "\n\t\t".$this->_input->getMarkup($fieldName);
		
		$m .= "\n\t</div>";
		$m .= "\n</div>";
		return $m;
	}
    
}
?>