<?php
/**
 * @since Jul 19, 2005
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardComponentWithChildren.abstract.php,v 1.4 2005/09/07 21:41:21 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/WizardComponent.abstract.php");

/**
 * This is an abstract class that defines a {@link WizardComponent} that can have children.
 * 
 * @since Jul 19, 2005
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardComponentWithChildren.abstract.php,v 1.4 2005/09/07 21:41:21 adamfranco Exp $
 * @abstract
 */
class WizardComponentWithChildren 
	extends WizardComponent 
{

	var $_children = array();	
	
	/**
	 * Adds a {@link WizardComponent} to this component. It will return the newly added component.
	 * @param string $name The short-string name of the component - this is used for creating form input field names and storing data.
	 * @param ref object $component A {@link WizardComponent} to add.
	 * @access public
	 * @return ref object
	 */
	function &addComponent ($name, &$component) {
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
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$children =& $this->getChildren();
		$ok = true;
		foreach (array_keys($children) as $key) {
			if (!$children[$key]->validate()) $ok = false;
		}
		return $ok;
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