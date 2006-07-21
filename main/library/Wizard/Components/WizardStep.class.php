<?php
/**
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStep.class.php,v 1.1.4.1 2006/07/21 18:10:38 adamfranco Exp $
 */

require_once(POLYPHONY."/main/library/Wizard/WizardComponentWithChildren.abstract.php");

/**
 * The Wizard class provides a system for registering Wizard properties and 
 * associating those properties with the appropriate form elements.
 *
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStep.class.php,v 1.1.4.1 2006/07/21 18:10:38 adamfranco Exp $
 * @author Gabe Schine
 */
class WizardStep extends WizardComponentWithChildren {
	
	var $_displayName;
	var $_contentText;
	
	/**
	 * Returns the displayName of this WizardStep
	 * @return string
	 */
	function getDisplayName () {
		return $this->_displayName;
	}
	
	/**
	 * Sets the display name of this wizard step
	 * @param string $displayName
	 * @return void
	 */
	function setDisplayName($displayName) {
		$this->_displayName = $displayName;
	}

	/**
	 * Sets this step's content text. This text will be parsed with {@link Wizard::parseText()}
	 * @param string $content;
	 * @access public
	 * @return void
	 */
	function setContent ($content) {
		$this->_contentText = $content;
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
		return Wizard::parseText($this->_contentText, $this->getChildren(), $fieldName."_");
	}
}

?>
