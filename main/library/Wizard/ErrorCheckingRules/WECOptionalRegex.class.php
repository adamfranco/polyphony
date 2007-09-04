<?php
/**
 * @since Jul 23, 2005
 * @package polyphony.library.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WECOptionalRegex.class.php,v 1.2 2007/09/04 20:28:09 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/ErrorCheckingRules/WECRegex.class.php");
/**
 * Allows for regular expression javascript error checking with optional values.
 * 
 * @since Jul 23, 2005
 * @package polyphony.library.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WECOptionalRegex.class.php,v 1.2 2007/09/04 20:28:09 adamfranco Exp $
 */
class WECOptionalRegex 
	extends WECRegex 
{
	
	/**
	 * Returns a block of javascript code defining a function like so:
	 * 
	 * function(element) {
	 * 		return el.value.match(/\w+/);
	 * }
	 * @access public
	 * @return string
	 */
	function generateJavaScript () {
		$re = addslashes($this->_regex);
		return "function(el) {\n" .
				"var re = new RegExp(\"$re\");\n" .
				"if (el.value == '')\n\t\t" .
				"return true;\n\t" .
				"else\n\t\t" .
				"return el.value.match(re);\n" .
				"}";
	}
	
	/**
	 * Returns true if the passed {@link WizardComponent} validates against this rule.
	 * @param ref object $component
	 * @access public
	 * @return boolean
	 */
	function checkValue ($component) {
		$value = $component->getAllValues();
		if (!strval($value) == '') 
			return true;
		else
			return parent::checkValue($component);
	}
}


?>