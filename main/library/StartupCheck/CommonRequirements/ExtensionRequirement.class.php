<?

/**
 * This {@link StartupRequirement} checks to make sure PHP has a given extension loaded (or can load it).
 * @package polyphony.startupcheck.requirements
 * @copyright 2004
 * @version $Id: ExtensionRequirement.class.php,v 1.2 2004/07/22 19:36:49 gabeschine Exp $
 */
class ExtensionRequirement extends StartupRequirement {

	var $_extension;
	
	/**
	 * Constructor
	 * @param string $extension The name of the extension that must be loaded (case-sensitive)
	 */
	function ExtensionRequirement($extension) {
		$this->_extension = $extension;
	}
	
	/**
	 * Checks the environment and returns a status value. Return value is one of STARTUP_STATUS_* defines.
	 * @access public
	 * @return integer
	 */
	function getStatus()
	{
		debug::output(
			"ExtensionRequirement - checking to make sure PHP loaded $this->_extension.", 7, "StartupCheck");
		if (!extension_loaded($this->_extension)) {
			$prefix = (PHP_SHLIB_SUFFIX == 'dll') ? 'php_' : '';
			if (!@dl($prefix . $this->_extension . "." . PHP_SHLIB_SUFFIX)) {
				StartupCheck::error(dgettext("polyphony",
							sprintf(
								"ExtensionRequirement - PHP extension <b>%s</b> is neither loaded nor could we load it dynamically.",
								$this->_extension
							)));
				return STARTUP_STATUS_ERROR;
			}
		}
		return STARTUP_STATUS_OK;
	}
	
	/**
	 * Returns this requirement's display name.
	 * @access public
	 * @return string
	 */
	function getDisplayName()
	{
		return dgettext("polyphony","PHP Extension Check")." : ".$this->_extension;
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