<?

/**
 * The Wizard class provides a system for registering Wizard properties and 
 * associating those properties with the appropriate form elements.
 *
 * @package polyphony.wizard
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 * @version $Id: WizardStep.interface.php,v 1.2 2004/07/29 22:11:15 adamfranco Exp $
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
	function & getProperties () {
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
	function & getLayout (& $harmoni) {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface <b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
}

?>