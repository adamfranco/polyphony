<?

/**
 * A startup requirement is part of the application install/update system. A requirement class has the ability to check to make sure that
 * the environment for running said program is OK, or to make updates to settings, database tables, etc to adjust for changes
 * or program updates.
 * @package polyphony.startupcheck
 * @copyright 2004
 * @version $Id: StartupRequirement.interface.php,v 1.1 2004/05/31 20:33:34 gabeschine Exp $
 */
class StartupRequirement {

	/**
	 * Checks the environment and returns a status value. Return value is one of STARTUP_STATUS_* defines.
	 * @access public
	 * @return integer
	 */
	function getStatus()
	{
		
	}
	
	/**
	 * Returns if this requirement class requires end user interaction in order to install/update successfully. If so,
	 * it will be asked for a {@link WizardStep} object to display to the user for input.
	 * @access public
	 * @return boolean
	 */
	function requiresUserInput()
	{
		
	}
	
	/**
	 * Returns this requirement's display name.
	 * @access public
	 * @return string
	 */
	function getDisplayName()
	{
		
	}
	
	/**
	 * Returns a {@link WizardStep} object containing fields for user input to complete installation process.
	 * @access public
	 * @return ref object
	 */
	function getWizardStep()
	{
		
	}
	
	/**
	 * Tells the requirement class to perform its update/install operation. If user input is required, it is passed in the form of a {@link WizardStep} containing field values.
	 * @param optional object $wizardStep A {@link WizardStep} corresponding to the one recieved from {@link getWizardStep()}.
	 * @access public
	 * @return boolean TRUE if the update succeeds, FALSE otherwise.
	 */
	function doUpdate( $wizardStep = null )
	{
		
	}

}


?>