<?

/**
 * @define STARTUP_STATUS_OK Says requirement is met.
 * @package polyphony.startupcheck
 */
define("STARTUP_STATUS_OK", 2);

/**
 * @define STARTUP_STATUS_ERROR Says the requirement is not met and that there is an internal error that can't be fixed.
 * @package polyphony.startupcheck
 */
define("STARTUP_STATUS_ERROR", 3);

/**
 * @define STARTUP_STATUS_NEEDS_UPDATE Says the requirement will be met after doing some internal updating.
 * @package polyphony.startupcheck
 */
define("STARTUP_STATUS_NEEDS_UPDATE", 1);

/**
 * @define STARTUP_STATUS_NEEDS_USER_INPUT Says the requirement will need user input before it can be updated.
 * @package polyphony.startupcheck
 */
define("STARTUP_STATUS_NEEDS_USER_INPUT", 5);

/**
 * @define STARTUP_STATUS_NOT_CHECKED Says the requirement has not yet been checked.
 * @package polyphony.startupcheck
 */
define("STARTUP_STATUS_NOT_CHECKED", 4);

/**
 * @define STARTUP_STATUS_NEEDS_INSTALL Says the requirement will be met after installing/configuring some system components.
 * @package polyphony.startupcheck
 */
define("STARTUP_STATUS_NEEDS_INSTALL", 0);

/**
 *
 * @package polyphony.startupcheck
 * @copyright 2004
 * @version $Id: StartupCheck.class.php,v 1.2 2004/06/01 18:40:39 gabeschine Exp $
 */
class StartupCheck {

	/**
	 * @variable array $_status Keeps a record of what parts of the program need updating.
	 * @access private
	 **/
	var $_status;
	
	/**
	 * @variable array $_requirements An array of {@link StartupRequirements}.
	 * @access private
	 **/
	var $_requirements;
	
	function StartupCheck() {
		$this->_requirements = array();
		$this->_status = array();
	}
	
	/**
	 * Adds a {@link StartupRequirement} to the requirement list.
	 * @param string $name A short name describing this component, such as "db tables".
	 * @param ref object $requirement A {@link StartupRequirement} to add.
	 * @access public
	 * @return void
	 */
	function addRequirement($name, &$requirement)
	{
		$this->_requirements[$name] =& $requirement;
		$this->_status[$name] = STARTUP_STATUS_NOT_CHECKED;
	}
	
	/**
	 * Runs through all the requirements and asks their status.
	 * @access public
	 * @return boolean FALSE if an update is needed, TRUE if everything is a-OK.
	 */
	function checkAllRequirements()
	{
		// cycle through requirements and get their respective statii
		$aOK = true;
		foreach(array_keys($this->_requirements) as $key) {
			$status = $this->_requirements[$key]->getStatus();
			$this->_status[$key] = $status;
			if ($status != STARTUP_STATUS_OK) $aOK = false;
			
			// check if the status is an error, if so, print a fatty error.
//			if ($status == STARTUP_STATUS_ERROR) {
//				throwError( new Error(
//					"StartupCheck::checkAllRequirements() - While processing requirement '".$this->_requirements[$key]->getDisplayName()."' a FATAL ERROR occured and the program could not continue. See other errors for more details.","StartupCheck",true)
//					);
//			}
		}
	}
	
	/**
	 * Returns the current status of the named requirement.
	 * @param string $name The component name to check.
	 * @access public
	 * @return integer One of the STARTUP_STATUS_* values.
	 */
	function getStatus($name)
	{
		return $this->_status[$name];
	}
	
	/**
	 * Tells each requirement class to update those components necessary to be met, if it can be done so without user interaction.
	 * @access public
	 * @return boolean FALSE if an error occurs.
	 */
	function updateAllAutonomousRequirements()
	{
		$aOK = true;
		foreach (array_keys($this->_requirements) as $key) {
			if ($this->getStatus($key) == STARTUP_STATUS_NEEDS_UPDATE
				|| $this->getStatus($key) == STARTUP_STATUS_NEEDS_INSTALL) {
				// do the update
				if (!$this->_requirements[$key]->doUpdate()) $aOK = false;
			}
		}
		
		return $aOK;
	}
	
	/**
	 * Tells a specific named requirement to update necessary components without user input.
	 * @param string $name The short name of the requirement.
	 * @access public
	 * @return boolean FALSE if error occurs.
	 */
	function updateNamed($name)
	{
		if ($this->getStatus($name) == STARTUP_STATUS_NEEDS_UPDATE || $this->getStatus($name) == STARTUP_STATUS_NEEDS_INSTALL)
			return $this->_requirements[$name]->doUpdate();
		return false;
	}
	
	/**
	 * Tells a specific named requirement to update necessary components using the {@link WizardProperty} objects supplied by the given {@link Wizard}.
	 * @access public
	 * @param ref object $wizard A {@link Wizard} object.
	 * @return boolean FALSE if error occurs.
	 */
	function updateNamedWithWizard(&$wizard)
	{
		// @todo - wait for Wizard update by Adam.
	}

}