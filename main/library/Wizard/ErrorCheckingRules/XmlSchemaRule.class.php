<?php
/**
 * @since 5/19/08
 * @package polyphony.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This error-checking rule will validate a field value against an XML Schema.
 * This rule will overwrite any ErrorText.
 * 
 * @since 5/19/08
 * @package polyphony.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class XmlSchemaRule
	implements WECRule
{
	
	/**
	 * Construct
	 * 
	 * @param string $xmlSchemaPath
	 * @return null
	 * @access public
	 * @since 5/19/08
	 */
	public function __construct ($xmlSchemaPath) {
		ArgumentValidator::validate($xmlSchemaPath, NonzeroLengthStringValidatorRule::getRule());
		$this->xmlSchemaPath = $xmlSchemaPath;
	}
	
	/**
	 * Returns a block of javascript code defining a function like so:
	 * 
	 * function(id) {
	 * 		var el = getWizardElement(id);
	 * 		return el.value.match(/\w+/);
	 * }
	 * @access public
	 * @return string
	 */
	function generateJavaScript () {
		// Can't do checking in JS.
		return "function (el) { return true; }";
	}
	
	/**
	 * Returns true if the passed {@link WizardComponent} validates against this rule.
	 * @param ref object $component
	 * @access public
	 * @return boolean
	 */
	function checkValue ($component) {
		$doc = new Harmoni_DOMDocument;
		try {
			$doc->loadXML($component->getAllValues());
		} catch (DOMException $e) {
			$component->setErrorText($e->getMessage());
			return false;
		}
		
		try {
			$doc->schemaValidateWithException($this->xmlSchemaPath);
		} catch (ValidationFailedException $e) {
			$component->setErrorText($e->getMessage());
			return false;
		}
		
		return true;
	}
	
}

?>