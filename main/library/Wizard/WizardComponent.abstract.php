<?php
/**
 * @since Jul 19, 2005
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardComponent.abstract.php,v 1.5 2007/09/19 14:04:50 adamfranco Exp $
 */ 

/**
 * A WizardComponent is an element that you can add to a {@link Wizard}. They can be used
 * to create simple interface components or to handle form input and validation.
 * 
 * @since Jul 19, 2005
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardComponent.abstract.php,v 1.5 2007/09/19 14:04:50 adamfranco Exp $
 */
abstract class WizardComponent 
	extends SObject 
{
	var $_parent;
	var $_enabled = true;
	var $_enabledSticky = false;
	
	
	/**
	 * Sets this component's parent (some kind of {@link WizardComponentWithChildren} so that it can
	 * have access to its information, if needed.
	 * @param ref object $parent
	 * @access public
	 * @return void
	 */
	function setParent ($parent) {
		$this->_parent =$parent;
	}
	
	/**
	 * Returns the top-level {@link Wizard} in which this component resides.
	 * @access public
	 * @return ref object
	 */
	function getWizard () {
		return $this->_parent->getWizard();
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE. Validate should be called usually before a save event
	 * is handled, to make sure everything went smoothly. 
	 * @access public
	 * @return boolean
	 */
	function validate () {
		return true;
	}
	
	/**
	 * Sets if this component will be enabled or disabled.
	 * @param boolean $enabled
	 * @param boolean $sticky If true, future calls to setEnabled without sticky
	 *							will have no effect.
	 * @access public
	 * @return void
	 */
	function setEnabled ($enabled, $sticky = false) {		
		if ($this->_enabledSticky) { 
			if ($sticky) {
				$this->_enabled = $enabled;
				$this->_enabledSticky = $sticky;
			}
		} else {
			$this->_enabled = $enabled;
			$this->_enabledSticky = $sticky;
		}
	}
	
	/**
	 * Answers true if this component will be enabled.
	 * @access public
	 * @return boolean
	 */
	function isEnabled () {
		return $this->_enabled;
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
	abstract function update ($fieldName);
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	abstract function getAllValues ();
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	abstract function getMarkup ($fieldName);
}


?>