<?php
/**
 * @package polyphony.library.startupcheck
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HarmoniVersionRequirement.class.php,v 1.6 2007/09/04 20:28:04 adamfranco Exp $
 */

/**
 * This {@link StartupRequirement} checks to make sure we are running a certain version of Harmoni, or newer.
 *
 * @package polyphony.library.startupcheck
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HarmoniVersionRequirement.class.php,v 1.6 2007/09/04 20:28:04 adamfranco Exp $
 */
class HarmoniVersionRequirement extends StartupRequirement {

	var $_harmoni;
	var $_version;
	var $_versionStr;
	
	/**
	 * Constructor
	 * @param ref object $harmoni A reference to a {$link Harmoni} object.
	 * @param string $version The harmoni version string, ie, "1.5.2".
	 */
	function HarmoniVersionRequirement($harmoni, $version) {

		$this->_harmoni =$harmoni;
		$this->_versionStr = $harmoni->getVersionStr($version);
		
		// let's convert the string into its numeric counterpart.
		$this->_version = $harmoni->getVersionNumber($version);
	}
	
	/**
	 * Checks the environment and returns a status value. Return value is one of STARTUP_STATUS_* defines.
	 * @access public
	 * @return integer
	 */
	function getStatus()
	{
		$harmoniVer = $this->_harmoni->getVersionNumber();
		$harmoniVerStr = $this->_harmoni->getVersionStr();
		
		debug::output("HarmoniVersionRequirement - checking to make sure Harmoni (currently $harmoniVer) is greater than ".$this->_version,7,"StartupCheck");
		
		if ($this->_version <= $harmoniVer) {
			// everything's OK!
			return STARTUP_STATUS_OK;
		} else {
			// we got trouble
			
			StartupCheck::error(
					sprintf(
						dgettext("polyphony","HarmoniVersionRequirement - program execution could not proceed. I must be running under the Harmoni framework version <i>%s</i> or newer. However, we detected you only have version <i>%s</i> installed."),
						$this->_versionStr,
						$harmoniVerStr
						)
			);
			
			return STARTUP_STATUS_ERROR;
		}
	}
	
	/**
	 * Returns this requirement's display name.
	 * @access public
	 * @return string
	 */
	function getDisplayName()
	{
		return dgettext("polyphony","Harmoni Version Check")." &gt;= ".$this->_versionStr;
	}
	
	/**
	 * Returns a {@link Wizard} object containing fields for user input to complete installation process.
	 * @access public
	 * @return ref object
	 */
	function createWizard()
	{
		$null = null; 
		return $null;
	}
	
	/**
	 * Tells the requirement class to perform its update/install operation. If user input is required, it is passed in the form of a {@link WizardStep} containing field values.
	 * @param optional array $properties An array of {@link WizardProperty} objects corresponding to the {@link Wizard} as created by {@link createWizard()}.
	 * @access public
	 * @return int Returns the new status of this requirement after attempting update.
	 */
	function doUpdate( $properties = null )
	{
		return STARTUP_STATUS_NOT_CHECKED;
	}
}