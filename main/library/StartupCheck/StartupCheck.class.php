<?

// include our StartupRequirement interface
require_once POLYPHONY."/main/library/StartupCheck/StartupRequirement.interface.php";

// then include our common requirements
require_once POLYPHONY."/main/library/StartupCheck/CommonRequirements/include.php";

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
* @version $Id: StartupCheck.class.php,v 1.7 2004/07/22 19:36:49 gabeschine Exp $
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

	/**
	* @variable string $_currentRequirement The name of the requirement we are in the middle of configuring (with user input).
	* @access private
	**/
	var $_currentRequirement;

	/**
	* @variable object $_currentWizard The {@link Wizard} object associated with the $_currentRequirement.
	* @access private
	**/
	var $_currentWizard;
	

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
		return $aOK;
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
				if (($this->_status[$key] = $this->_requirements[$key]->doUpdate()) != STARTUP_STATUS_OK) $aOK = false;
			}
		}

		return $aOK;
	}

	/**
	* Returns an array of the names of requirements that currently have the status requested.
	* @access public
	* @return array
	*/
	function getRequirementsOfStatus($status)
	{
		$array = array();
		foreach ($this->_status as $key=>$value) {
			if ($value == $status) $array[] = $key;
		}
		return $array;
	}

	/**
	* Tells a specific named requirement to update necessary components without user input.
	* @param string $name The short name of the requirement.
	* @access public
	* @return boolean FALSE if error occurs.
	*/
	function updateRequirement($name)
	{
		if ($this->getStatus($name) == STARTUP_STATUS_NEEDS_UPDATE || $this->getStatus($name) == STARTUP_STATUS_NEEDS_INSTALL) {
			if (($this->_status[$name] = $this->_requirements[$name]->doUpdate()) == STARTUP_STATUS_OK) {
				return true;
			} else {
				return false;
			}
		}
		return false;
	}

	/**
	* Tells a specific named requirement to update necessary components using the {@link WizardProperty} objects supplied by the given {@link Wizard}.
	* @access public
	* @param string $name The short-name of the requirement to update.
	* @param ref object $wizard A {@link Wizard} object.
	* @return boolean FALSE if error occurs.
	*/
	function updateRequirementWithWizard($name, &$wizard)
	{
		// we can't use the data until the end user has pressed the "Save" button on the wizard
		if ($wizard->isSaveRequested()) {
			if (($this->_status[$name] = $this->_requirements[$name]->doUpdate($wizard->getProperties())) == STARTUP_STATUS_OK) {
				return true;
			} else { // don't change its status.
				return false;
			}
		}
		return false;
	}

	/**
	* Returns the number of requirements we have to meet.
	* @access public
	* @return integer
	*/
	function getRequirementCount()
	{
		return count($this->_requirements);
	}

	/**
	* Handles the update process with user input, if necessary. Uses the {@link Harmoni} object to output HTML to the end user.
	* In order for this to work, we must be session_registered and called every page-load.
	* @access public
	* @param ref object $harmoni The {@link Harmoni} object.
	* @return boolean TRUE if program execution can continue as normal, FALSE if updating is still required.
	*/
	function handleAllUpdates(&$harmoni)
	{
		// this method does the following:
		// - checks all requirements, updates those that are autonomous automatically.
		// - if any require user input, it will step through them and handle the user input side
		// 		- if we are currently in the middle of one of these input sessions, we will
		//		  skip everything else and just output the wizard
		//		- once the person hits the "Save" button, we will give the wizard to the requirement for handling,
		// 		  and then get the next requirement that needs user input.

		// if we have nothing to do, we're done.
		if ($this->areAllOK()) return true;
		
		// if we are actively working with a wizard...
		if (!$this->_useWizard($harmoni)) return false;
		
		// ok, let's get all the updates. if everything's ok, we can just return.
		if ($this->checkAllRequirements()) return true;

		// update all autonomous requirements
		$this->updateAllAutonomousRequirements();

		// if the above command took care of everything, let's get out.
		if ($this->areAllOK()) return true;
		
		// otherwise...
		if (!$this->_currentRequirement) {
			$this->_setupNextRequirementForInput();
		} else {
			$this->_currentWizard->update();
		}

		// if we have something to work with user-input-wise, let's handle it.
		return $this->_useWizard($harmoni);
		
		return false;
	}
	
	/**
	 * Internally handles input & output from a wizard.
	 * @param ref object $harmoni A {@link Harmoni} object.
	 * @access public
	 * @return void
	 */
	function _useWizard(&$harmoni)
	{
		if ($req = $this->_currentRequirement) {
			if ($this->_currentWizard->isSaveRequested()) {
				$this->updateRequirementWithWizard($req, $this->_currentWizard);
				$this->_currentRequirement = $this->_currentWizard = null;
				if ($this->areAllOK()) return true;
				else $this->_setupNextRequirementForInput();
			} 
			if ($this->_currentWizard) {
				// output some HTML bizness
				$layout =& $this->_currentWizard->getLayout($harmoni);
				$theme =& $harmoni->getTheme();
				$theme->printPage($layout);
				return false;
			}
		}
		return true;
	}

	/**
	* Sets up the next requirement wizard for user input.
	* @access private
	* @return void
	*/
	function _setupNextRequirementForInput()
	{
		// if we have one, let's set up a new wizard and get going on that.
		$name = $this->getNextRequiringInput();
		if ($name) {
			$this->_currentRequirement = $name;
			$this->_currentWizard =& $this->_requirements[$name]->createWizard();
		}
	}

	/**
	* Returns true if all requirements have been met (and checked, of course).
	* @access public
	* @return boolean
	*/
	function areAllOK()
	{
		return ($this->getRequirementCount() == $this->getRequirementsOfStatus(STARTUP_STATUS_OK))?true:false;
	}

	/**
	* Gets the next requirement that requires user input and returns the name.
	* @access public
	* @return string or NULL if none are left.
	*/
	function getNextRequiringInput()
	{
		$array = $this->getRequirementsOfStatus(STARTUP_STATUS_NEEDS_USER_INPUT);
		if (!count($array)) return null;
		return $array[0];
	}
	
	/**
	 * This function prints out an error message to tell the user that something went wrong in the startup check process.
	 * @access private
	 * @return void
	 * @abstract
	 */
	function error($string)
	{
		$f =& new FieldSet;
		
		$f->set("pageTitle",_("Startup Error"));
		$f->set("intro", _("The startup process has encountered a problem"));
		$f->set("errorString",$string);
		
		$tpl =& new Template("error.tpl.php",POLYPHONY."/main/library/StartupCheck/");
		
		$tpl->output($f);
		
		exit(1); // exit with error
	}
}