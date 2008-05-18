<?php
/**
 * @since 5/16/08
 * @package polyphony.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/Wizard/ErrorCheckingRules/WECRule.interface.php");

/**
 * Validate and clean any HTML text entered, stripping any javascript or other
 * unsafe markup.
 * 
 * @since 5/16/08
 * @package polyphony.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class SafeHtmlRule
	implements WECRule
{
		
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
		$orig = $component->getAllValues();
		$htmlString = HtmlString::fromString($component->getAllValues());
		$htmlString->cleanXSS();
		if (trim($orig) == trim($htmlString->asString())) {
			printpre("HTML VALID");
			exit;
			return true;
		} else {
			printpre("HTML INVALID");
			exit;
			return false;
		}
	}
	
}

?>