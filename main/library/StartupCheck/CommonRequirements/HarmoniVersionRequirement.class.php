<?

/**
 * This {@link StartupRequirement} checks to make sure we are running a certain version of Harmoni, or newer.
 * @package polyphony.startupcheck.requirements
 * @copyright 2004
 * @version $Id: HarmoniVersionRequirement.class.php,v 1.2 2004/07/22 19:36:49 gabeschine Exp $
 */
class HarmoniVersionRequirement extends StartupRequirement {

	var $_harmoni;
	var $_version;
	var $_versionStr;
	
	/**
	 * Constructor
	 * @param ref object $harmoni A reference to a {$link Harmoni} object.
	 * @param int $version The integer-equivalent of the version that is required. The format is XXMMRR, XX = major number, MM = minor number, RR = release number, giving a string like 020512 for version 2.5.12. NOTE: you should leave off leading 0's so PHP doesn't think it's an octal.
	 */
	function HarmoniVersionRequirement(&$harmoni, $version) {

		$this->_harmoni =& $harmoni;
		$this->_version = $version;
		
		// now let's make a pretty string-version of this numerical version number for printing out later.
		$temp = sprintf("%06d",$version);
		$major = substr($temp,0,2);
		$minor = substr($temp,2,2);
		$release = substr($temp,4,2);
		
		$this->_versionStr = sprintf("%d.%d.%d",$major,$minor,$release);
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
	function &createWizard()
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