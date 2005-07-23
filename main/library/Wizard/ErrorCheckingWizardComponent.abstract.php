<?php
/**
 * @since Jul 22, 2005
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ErrorCheckingWizardComponent.abstract.php,v 1.1 2005/07/23 01:56:15 gabeschine Exp $
 */ 
 
require_once(POLYPHONY."/main/library/Wizard/WizardComponent.interface.php");
 
/**
 * Defines a component that makes use of the Wizard's javascript error checking abilities.
 * 
 * @since Jul 22, 2005
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ErrorCheckingWizardComponent.abstract.php,v 1.1 2005/07/23 01:56:15 gabeschine Exp $
 * @abstract
 */
class ErrorCheckingWizardComponent extends WizardComponent {
	var $_errorRegex = ".*";
	var $_errorMessage = null;
	var $_errorStyle = "color: red;";
	
	/**
	 * Sets this element's regular expression. Its value must match this to be considered valid.
	 * @param string $regex
	 * @access public
	 * @return void
	 */
	function setErrorRegex ($regex) {
		$this->_errorRegex = $regex;
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
	 * Returns the error regular expression.
	 * @access public
	 * @return string
	 */
	function getErrorRegex () {
		return $this->_errorRegex;
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