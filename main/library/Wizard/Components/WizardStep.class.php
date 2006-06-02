<?php
/**
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStep.class.php,v 1.1.2.1 2006/06/02 21:04:47 cws-midd Exp $
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
 * @version $Id: WizardStep.class.php,v 1.1.2.1 2006/06/02 21:04:47 cws-midd Exp $
 * @author Gabe Schine
 */
class WizardStep extends WizardComponentWithChildren {
	
	var $_displayName;
	var $_contentText;

	/**
	 * @var array $_markups; an array of markup chunks for the step's components 
	 * @access private
	 * @since 5/3/06
	 */
	var $_markups = array();
	
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
	 * answers the content of this step
	 * 
	 * @return string
	 * @access public
	 * @since 5/18/06
	 */
	function getContent () {
		return $this->_contentText;
	}

	/**
	 * Adds the markup block to the array of markups, to be compiled later
	 * 
	 * @param string $markup
	 * @param string $component
	 * @return void
	 * @access public
	 * @since 5/3/06
	 */
	function setMarkupForComponent ($markup, $component) {
		$this->_markups[$component] = $markup;
	}

	/**
	 * Answers the markup block in the array of markups for the component
	 * 
	 * @param string $component
	 * @return string
	 * @access public
	 * @since 5/3/06
	 */
	function getMarkupForComponent ($component) {
		if (isset($this->_markups[$component]))
			return $this->_markups[$component];
		else
			return '';
	}

	/**
	 * Answers the array of markup chunks
	 * 
	 * @return array
	 * @access public
	 * @since 5/4/06
	 */
	function getMarkups () {
		return $this->_markups;
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
		return Wizard::parseText($this->_contentText, $this->getChildren(), $fieldName."_");
	}
}

?>
