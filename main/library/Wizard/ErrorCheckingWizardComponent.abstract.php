<?php
/**
 * @since Jul 22, 2005
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ErrorCheckingWizardComponent.abstract.php,v 1.4 2007/09/04 20:28:05 adamfranco Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/WizardComponent.abstract.php");
 
/**
 * Defines a component that makes use of the Wizard's javascript error checking abilities.
 * 
 * @since Jul 22, 2005
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ErrorCheckingWizardComponent.abstract.php,v 1.4 2007/09/04 20:28:05 adamfranco Exp $
 * @abstract
 */
class ErrorCheckingWizardComponent extends WizardComponent {
	var $_errorRule = null;
	var $_errorMessage = null;
	var $_errorStyle = "color: red;";
	
	/**
	 * Sets this element's regular expression. Its value must match this to be considered valid.
	 * @param string $regex
	 * @access public
	 * @return void
	 */
	function setErrorRule ($rule) {
		$this->_errorRule =$rule;
	}
	
	/**
	 * Sets the text to be displayed if an error occurs.
	 * @param string $text
	 * @access public
	 * @return void
	 */
	function setErrorText ($text) {
		$this->_errorMessage = $text;
	}
	
	/**
	 * Sets the CSS style of the error text.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setErrorStyle ($style) {
		$this->_errorStyle = $style;
	}
	
	/**
	 * Returns the error {@link WECRule}.
	 * @access public
	 * @return ref object
	 */
	function getErrorRule () {
		return $this->_errorRule;
	}
	
	/**
	 * Returns the error text.
	 * @access public
	 * @return string
	 */
	function getErrorText () {
		return $this->_errorMessage;
	}
	
	/**
	 * Returns the error text CSS style.
	 * @access public
	 * @return string
	 */
	function getErrorStyle () {
		return $this->_errorStyle;
	}
}


?>