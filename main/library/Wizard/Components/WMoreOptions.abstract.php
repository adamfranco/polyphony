<?php
/**
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WMoreOptions.abstract.php,v 1.1 2006/08/15 21:12:35 sporktim Exp $
 */

require_once(POLYPHONY."/main/library/Wizard/WizardComponentWithChildren.abstract.php");

/**
 * The goal here is to provide a system for having a simple view that the 
 * user can expand to get more options.
 *
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WMoreOptions.abstract.php,v 1.1 2006/08/15 21:12:35 sporktim Exp $
 * @author Gabe Schine
 */
class WMoreOptions extends WizardComponentWithChildren {
	
	
	
	var $_usingAdvanced;
	
	function init($set = false){
		$this->_usingAdvanced = $set;
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
		
		$val = RequestContext::value($fieldName."_checkbox");
		if ($val !== null)
		{
			$this->_usingAdvanced = $val;
		}
					
		$children =& $this->getChildren();
		$ok = true;
		foreach (array_keys($children) as $key) {			
			if (!$children[$key]->update($fieldName."_".$key)) {
				$ok = false;
			}
		}
		
		return $ok;
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * Return true if we should be using the new value rather than the select
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/2/06
	 */
	function isUsingAdvanced () {
		return $this->_usingAdvanced;
	}
			
	/**
	 * Returns a block of XHTML-valid code that contains markup for the "advanced"
	 * options plus the block that hides them.
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public

	 */
	function  getOptionalComponentsMarkup($fieldName) {
		$m = "";				
		$advancedId = $fieldName."_advancedfield";		
		if ($this->isUsingAdvanced())
			$display = " display: block;";
		else
			$display = " display: none;";
		$m .= "\n\t<div id='$advancedId' style='padding: 0px; margin: 0px; $display'>";
		
		$m .= "\n\t\t".$this->advancedMarkup($fieldName);
		
		$m .= "\n\t</div>";			
		return $m;
	}
	
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for the "advanced"
	 * options.
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function advancedMarkup ($fieldName) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for the checkbox
	 * 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getCheckboxMarkup ($fieldName) {
		$m ="";
		$boxId = $fieldName . "_checkbox";		
		$advancedId = $fieldName."_advancedfield";
		
		$checked = $this->isUsingAdvanced()?" checked='checked'":"";		
		$style = " style='cursor: pointer;'";	
		$m .= "\n\t\t\t<input type='checkbox' ";
		if (!$this->isEnabled())
			$m .= "\n\t\t\t\tdisabled=\"disabled\"";
		else {
			$m .= "\n\t\t\t\tonclick=\"";
			
			$m .="var advancedField = getElementFromDocument('$advancedId'); if (this.checked) { advancedField.style.display = 'block'; } else { advancedField.style.display = 'none'; }";
			
			$m .= " \"";
		}		
		$m .= "\n\t\t\t\tid='$boxId'$checked.$style />";		
		return $m;
	}
	
	
}

?>
