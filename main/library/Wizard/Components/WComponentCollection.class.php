<?php
/**
 * @since 7/21/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WComponentCollection.class.php,v 1.1.2.1 2006/07/21 18:11:56 adamfranco Exp $
 */ 

/**
 * This allows for a grouping of Components. It is a single component itself that
 * wraps other components to allow for the addition of multiple components where
 * a single one is expected.
 * 
 * @since 7/21/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WComponentCollection.class.php,v 1.1.2.1 2006/07/21 18:11:56 adamfranco Exp $
 */
class WComponentCollection
	extends WizardComponentWithChildren
{
	
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