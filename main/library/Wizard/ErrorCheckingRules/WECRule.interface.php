<?php
/**
 * @since Jul 23, 2005
 * @package polyphony.library.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WECRule.interface.php,v 1.2 2005/07/25 18:33:57 gabeschine Exp $
 */ 
/**
 * An interface that defines rules for javascript error checking with the {@link Wizard}.
 * 
 * @since Jul 23, 2005
 * @package polyphony.library.wizard.errorchecking
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WECRule.interface.php,v 1.2 2005/07/25 18:33:57 gabeschine Exp $
 */
class WECRule {
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
		
	}
	
	/**
	 * Returns true if the passed {@link WizardComponent} validates against this rule.
	 * @param ref object $component
	 * @access public
	 * @return boolean
	 */
	function checkValue (&$component) {
		
	}
}


?>