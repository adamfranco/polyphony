<?php
/**
 * @package polyphony.library.startupcheck.requirements
 */

/**
 * @name PHPINI_EQUAL 
 */
define("PHPINI_EQUAL",0);

/**
 * @name PHPINI_BOOLEAN 
 */
define("PHPINI_BOOLEAN",1);

/**
 * @name PHPINI_LESS 
 */
define("PHPINI_LESS",2);

/**
 * @name PHPINI_GREATER 
 */
define("PHPINI_GREATER",3);


/**
 * A {@link StartupRequirement} that checks the value of a PHP config option. If the check fails, it will output an error message.
 * @package polyphony.library.startupcheck.requirements
 * @copyright 2004
 * @version $Id: PHPConfigValueRequirement.class.php,v 1.3 2005/02/04 23:06:14 adamfranco Exp $
 */
class PHPConfigValueRequirement extends StartupRequirement {

	var $_key;
	var $_value;
	var $_opt;

	/**
	* Constructor
	* @param string $key The php.ini option to look for (ie, 'register_globals')
	* @param mixed $value The value that is required.
	* @param optional int $opt An optional flag specifying how we should check the requirement. Options are: PHPINI_EQUAL (check for $key=$value, except for booleans), PHPINI_GREATER (check for $key>$value), PHPINI_LESS (check for $key<$value), PHPINI_BOOLEAN (will make sure the $key is true or false, depending on $value, which can be: "on", "off", true, false, 0, 1, "yes", "no", case insensitive)
	*/
	function PHPConfigValueRequirement($key, $value, $opt=PHPINI_EQUAL) {
		// for boolean values, we have to treat the $value specially
		if ($opt == PHPINI_BOOLEAN) {
			do {
				if (is_string($value)) {
					$value = strtolower($value);
					if ($value=="yes" || $value=="on") $value="1";
					else if ($value=="no" || $value=="off") $value="";
				} else if (is_bool($value)) {
					if ($value == true) $value="1";
					else $value="";
				} else if (is_numeric($value)) {
					if ($value==1) $value="1";
					else if ($value==0) $value="";
				}
			} while (0);
		}

		// otherwise, we'll just leave it be.
		$this->_key = strtolower($key);
		$this->_value = $value;
		$this->_opt = $opt;
	}

	/**
	* Checks the environment and returns a status value. Return value is one of STARTUP_STATUS_* defines.
	* @access public
	* @return integer
	*/
	function getStatus()
	{
		debug::output("PHPConfigValueRequirement - checking config directive $this->_key.",7,"StartupCheck");
		// let's check if the value is good or not
		if ($this->_opt == PHPINI_EQUAL || $this->_opt == PHPINI_BOOLEAN) {
			if (($curr=ini_get($this->_key)) === $this->_value) {
				return STARTUP_STATUS_OK;
			} else {
				StartupCheck::error(
					sprintf(dgettext("polyphony","PHPConfigValueRequirement - program could not proceed: a required php.ini value is not set properly for this program. I checked the <b>%s</b> directive and got <i>%s</i> when I wanted to get <i>%s</i>."),
					$this->_key,
					($this->_opt==PHPINI_BOOLEAN)?($curr=="1"?"true":"false"):$curr,
					($this->_opt==PHPINI_BOOLEAN)?($this->_value=="1"?"true":"false"):$curr)
				);
				
				return STARTUP_STATUS_ERROR;
			}
		} else if ($this->_opt == PHPINI_GREATER) {
			$curr = ini_get($this->_key);
			if ($curr > $this->_value)
				return STARTUP_STATUS_OK;
			else {
				StartupCheck::error(
					sprintf(dgettext("polyphony","PHPConfigValueRequirement - program could not proceed: a required php.ini value is not set properly for this program. I checked the <b>%s</b> directive and got <i>%s</i> when I wanted it to be greater than <i>%s</i>."),
					$this->_key,
					($this->_opt==PHPINI_BOOLEAN)?($curr=="1"?"true":"false"):$curr,
					($this->_opt==PHPINI_BOOLEAN)?($this->_value=="1"?"true":"false"):$curr)
				);
				
				return STARTUP_STATUS_ERROR;
			}
		} else if ($this->_opt == PHPINI_LESS) {
			$curr = ini_get($this->_key);
			if ($curr < $this->_value)
				return STARTUP_STATUS_OK;
			else {
				StartupCheck::error(
					sprintf(dgettext("polyphony","PHPConfigValueRequirement - program could not proceed: a required php.ini value is not set properly for this program. I checked the <b>%s</b> directive and got <i>%s</i> when I wanted it to be less than <i>%s</i>."),
					$this->_key,
					($this->_opt==PHPINI_BOOLEAN)?($curr=="1"?"true":"false"):$curr,
					($this->_opt==PHPINI_BOOLEAN)?($this->_value=="1"?"true":"false"):$curr)
				);
				
				return STARTUP_STATUS_ERROR;
			}
		} else {
			// ?
		}
	}

	/**
	* Returns this requirement's display name.
	* @access public
	* @return string
	*/
	function getDisplayName()
	{
		return sprintf(dgettext("polyphony","PHP.ini Setting (%s) Check"),$this->_key);
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
		// we don't do any updating!
		return STARTUP_STATUS_NOT_CHECKED;
	}
}