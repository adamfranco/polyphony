<?php
/**
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStep.interface.php,v 1.5 2005/04/07 17:07:50 adamfranco Exp $
 */

/**
 * The Wizard class provides a system for registering Wizard properties and 
 * associating those properties with the appropriate form elements.
 *
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WizardStep.interface.php,v 1.5 2005/04/07 17:07:50 adamfranco Exp $
 * @author Adam Franco
 */
class WizardStepInterface {
	
	/**
	 * Returns the displayName of this WizardStep
	 * @return string
	 */
	function getDisplayName () {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * Gets an array all Properties indexed by property name.
	 * @return array 
	 */
	function &getProperties () {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * Go through all properties and update their values from the submitted
	 * form. Return false if any of the submitted values are invalid.
	 *
	 * @access public
	 * @return boolean True on success. False on invalid Property values.
	 */
	function updateProperties () {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * Go through all properties and check the validity of their stored values. 
	 * Return false if any of the submitted values are invalid.
	 *
	 * @access public
	 * @return boolean True on success. False on invalid Property values.
	 */
	function arePropertiesValid () {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * Returns a layout of content for this WizardStep
	 * @param object Harmoni The harmoni object which contains the current context.
	 * @return object Layout
	 */
	function &getLayout (& $harmoni) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
}

?>