<?php
/**
 * @since Jul 19, 2005
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardComponentWithChildren.abstract.php,v 1.1 2005/07/22 15:42:20 gabeschine Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/WizardComponent.interface.php");

/**
 * This is an abstract class that defines a {@link WizardComponent} that can have children.
 * 
 * @since Jul 19, 2005
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardComponentWithChildren.abstract.php,v 1.1 2005/07/22 15:42:20 gabeschine Exp $
 * @abstract
 */
class WizardComponentWithChildren extends WizardComponent {
	var $_children = array();
	
	var $_parent;
	
	/**
	 * Sets this component's parent (some kind of {@link WizardComponentWithChildren} so that it can
	 * have access to its information, if needed.
	 * @param ref object $parent
	 * @access public
	 * @return void
	 */
	function setParent (&$parent) {
		$this->_parent =& $parent;
	}
	
	/**
	 * Returns the top-level {@link Wizard} in which this component resides.
	 * @access public
	 * @return ref object
	 */
	function &getWizard () {
		return $this->_parent->getWizard();
	}
	
	/**
	 * Adds a {@link WizardComponent} to this component. It will return the newly added component.
	 * @param string $name The short-string name of the component - this is used for creating form input field names and storing data.
	 * @param ref object $component A {@link WizardComponent} to add.
	 * @access public
	 * @return ref object
	 */
	function & addComponent ($name, &$component) {
		$this->_children[$name] =& $component;
		$component->setParent($this);
		return $component;
	}
	
	/**
	 * Returns an array of all the children of this component, keyed by name.
	 * @access public
	 * @return ref array
	 */
	function &getChildren () {
		return $this->_children;
	}
	
	/**
	 * Returns the component specified by $name.
	 * @param string $name
	 * @access public
	 * @return ref object
	 */
	function &getChild ($name) {
		return $this->_children[$name];
	}
	
	/**
	 * Removes the specified child from this component.
	 * @param string $name
	 * @access public
	 * @return void
	 */
	function removeChild ($name) {
		unset($this->_children[$name]);
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$array = array();
		$children =& $this->getChildren();
		foreach(array_keys($children) as $key) {
			$array[$key] = $children[$key]->getAllValues();
		}
		
		return $array;
	}
}


?>