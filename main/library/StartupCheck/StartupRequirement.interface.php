<?

/**
 * A startup requirement is part of the application install/update system. A requirement class has the ability to check to make sure that
 * the environment for running said program is OK, or to make updates to settings, database tables, etc to adjust for changes
 * or program updates.
 * @package polyphony.startupcheck
 * @copyright 2004
 * @version $Id: StartupRequirement.interface.php,v 1.3 2004/07/22 19:36:49 gabeschine Exp $
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
	 * Returns this requirement's display name.
	 * @access public
	 * @return string
	 */
	function getDisplayName()
	{
		
	}
	
	/**
	 * Returns a {@link Wizard} object containing fields for user input to complete installation process.
	 * @access public
	 * @return ref object
	 */
	function &createWizard()
	{
		
	}
	
	/**
	 * Tells the requirement class to perform its update/install operation. If user input is required, it is passed in the form of a {@link WizardStep} containing field values.
	 * @param optional array $properties An array of {@link WizardProperty} objects corresponding to the {@link Wizard} as created by {@link createWizard()}.
	 * @access public
	 * @return int Returns the new status of this requirement after attempting update.
	 */
	function doUpdate( $properties = null )
	{
		
	}

}


?>