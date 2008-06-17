<?php
/**
 * @since 6/16/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/Wizard/Components/WComponentCollection.class.php');

/**
 * This class works like a multi-select list, but uses check-boxes instead.
 * 
 * @since 6/16/08
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class WMultiCheckList
	extends WComponentCollection	
{

	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 6/16/08
	 */
	public function __construct () {
		$this->_optionValues = array();
	}
	
	/**
	 * sets the CSS style for the labels of the radio buttons.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle ($style) {
		$this->_style = $style;
	}
	
	/**
	 * Sets the number of viewable elements in this list.
	 * @param integer $size
	 * @access public
	 * @return void
	 */
	function setSize ($size) {
		// just here for compatability with multi-select list.
	}
	
	/**
	 * Sets the passed value to be selected in the list.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		ArgumentValidator::validate($value, StringValidatorRule::getRule());
    	foreach ($this->_optionValues as $optKey => $optVal) {
    		$child = $this->getChild(strval($optKey));
    		if ($optVal == $value)
				$child->setChecked(true);
		}
	}
	
	/**
	 * Adds a radio option to this list.
	 * @param string $value The short value that represents the displayed text.
	 * @param string $displayText The text to show to the end user.
	 * @access public
	 * @return void
	 */
	function addOption ($value, $displayText) {
		$name = strval(count($this->_optionValues));
		
		$this->_optionValues[$name] = $value;
		
		$newChild = new WCheckBox;
		$newChild->setLabel($displayText);
		
		$this->addComponent($name, $newChild);
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$array = array();
		foreach ($this->_optionValues as $optKey => $optVal) {
    		$child = $this->getChild(strval($optKey));
    		if ($child->getAllValues() == '1')
    			$array[] = $optVal;
		}
		return $array;
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
		ob_start();
		foreach ($this->getChildren() as $childName => $child) {
			print "\n\t<div>[[".$childName."]] </div>";
		}
		$this->setContent(ob_get_clean());
		
		return parent::getMarkup($fieldName);
	}
	
}

?>