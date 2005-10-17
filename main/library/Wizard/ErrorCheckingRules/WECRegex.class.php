<?php
/**
 * @since Jul 23, 2005
 * @package polyphony.library.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WECRegex.class.php,v 1.3 2005/10/17 20:43:53 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/ErrorCheckingRules/WECRule.interface.php");
/**
 * Allows for regular expression javascript error checking.
 * 
 * @since Jul 23, 2005
 * @package polyphony.library.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WECRegex.class.php,v 1.3 2005/10/17 20:43:53 adamfranco Exp $
 */
class WECRegex extends WECRule {
	var $_regex;
	
	/**
	 * Constructor
	 * @param string $regex
	 * @access public
	 * @return void
	 */
	function WECRegex ($regex) {
		$this->_regex = $regex;
	}
	
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
				"return el.value.match(re);\n" .
				"}";
	}
	
	
	/**
	 * Returns true if the passed {@link WizardComponent} validates against this rule.
	 * @param ref object $component
	 * @access public
	 * @return boolean
	 */
	function checkValue (&$component) {
		$value = $component->getAllValues();
		
		if (preg_match("/".$this->_regex."/", $value)) return true;
		return false;
	}
}


?>