<?php
/**
 * @since 5/17/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * A version of the Text field that only allows valid and cleaned HTML
 * 
 * @since 5/17/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class WSafeHtmlTextField
	extends WTextArea
{
		
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
		$val = RequestContext::value($fieldName);
		if ($val !== null
			&& (!$this->_startingDisplay || $val != $this->_startingDisplay))
		{
			$string = HtmlString::fromString($val);
			$string->cleanXSS();
			$this->_value = $string->asString();
			
			if (trim($this->_value) != trim($val)) {
				$this->_origErrorText = $this->getErrorText();
				$this->setErrorText(dgettext('polyphony', "The value you entered has been reformatted to meet XHTML validity standards."));
				// Add both error text if validation failed as well.
				if (!$this->validate()) {
					$this->setErrorText($this->getErrorText()." ".$this->_origErrorText);
				}
				$this->_showError = true;
				// Add a dummy rule if needed.
				if (!$this->getErrorRule())
					$this->setErrorRule(new WECRegex('.*'));
			} else {
				// Reset the original error text.
				if (isset($this->_origErrorText)) {
					$this->setErrorText($this->_origErrorText);
				}
			}
			
		}
		
		return $this->validate();
	}
	
}

?>