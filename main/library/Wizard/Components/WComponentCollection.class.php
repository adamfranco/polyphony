<?php
/**
 * @since 7/21/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WComponentCollection.class.php,v 1.4 2007/09/19 14:04:51 adamfranco Exp $
 */ 

/**
 * This allows for a grouping of Components. It is a single component itself that
 * wraps other components to allow for the addition of multiple components where
 * a single one is expected.
 * 
 * @since 7/21/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WComponentCollection.class.php,v 1.4 2007/09/19 14:04:51 adamfranco Exp $
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
	
	/**
     * Set the value of the child components
     * 
     * @param array $value
	 * @access public
	 * @return void
     */
    function setValue ($value) {
		ArgumentValidator::validate($value, ArrayValidatorRule::getRule());
    	$children =$this->getChildren();
    	foreach (array_keys($children) as $key) {
    		if (isset($value[$key]))
				$children[$key]->setValue($value[$key]);
		}
    }
	
}

?>