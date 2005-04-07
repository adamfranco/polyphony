<?php
/**
 * @package polyphony.library.startupcheck
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DummyWizardRequirement.class.php,v 1.5 2005/04/07 17:07:49 adamfranco Exp $
 */

/**
 * This is a dummy requirement to test the Wizard functionality.
 *
 * @package polyphony.library.startupcheck
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: DummyWizardRequirement.class.php,v 1.5 2005/04/07 17:07:49 adamfranco Exp $
 */
class DummyWizardRequirement extends StartupRequirement {

	var $_status;
	var $_properties;
	
	/**
	 * Constructor
	 */
	function DummyWizardRequirement() {
		$this->_status = STARTUP_STATUS_NEEDS_USER_INPUT;
	}
	
	/**
	 * Checks the environment and returns a status value. Return value is one of STARTUP_STATUS_* defines.
	 * @access public
	 * @return integer
	 */
	function getStatus()
	{
		return $this->_status;
	}
	
	/**
	 * Returns this requirement's display name.
	 * @access public
	 * @return string
	 */
	function getDisplayName()
	{
		return "Dummy Wizard Requirement";
	}
	
	/**
	 * Returns a {@link Wizard} object containing fields for user input to complete installation process.
	 * @access public
	 * @return ref object
	 */
	function &createWizard()
	{
		$w =& new Wizard("Dummy Wizard", false);
		
		$s1 =& $w->createStep("Step One");
		$s2 =& $w->createStep("Step Two");
		
		$p1 =& $s1->createProperty("name", new RegexValidatorRule("^.+$"), true);
		$p1->setDefaultValue("Your Name");
		$p1->setErrorString("<b>You must enter a name!</b><br/>");
		$p2 =& $s1->createProperty("color", new StringValidatorRule(), false);
		$p2->setErrorString("<b>You must choose a color!</b><br/>");
		$p2->setDefaultValue("blue");
		
		$s1->setText( <<< END
This is a Dummy startup requirement that requires user input to succeed. Please enter information in the following fields:

<P>

[[name|Error]]
Enter your name: <input type=text value="[[name]]" name="name">
<p>
[[color|Error]]
Pick your favorite color: (not here? tough.)
<select name="color">
<option value="blue"[['color' == 'blue'| selected='selected'|]]>blue
<option value="red"[['color' == 'red'| selected='selected'|]]>red
<option value="puse"[['color' == 'puse'| selected='selected'|]]>puse
</select>

END
		);
		unset($s1,$p1,$p2);
		
		$p1 =& $s2->createProperty("age", new IntegerRangeValidatorRule(20,100), true);
		$p1->setErrorString("<b>You must be between the ages of 20 and 100.</b><br />");
		$p1->setDefaultValue(25);
		
		$p2 =& $s2->createProperty("continue", new ChoiceValidatorRule("yes","no"), true);
		$p2->setDefaultValue("no");
		$p2->setErrorString("<b>You must choose 'yes' to continue</b><br />");
		
		$s2->setText( <<< END
This is step 2. 

<p>

[[age|Error]]
Enter your age: <input type="text" name="age" value="[[age]]">
<p>

Do you want to continue?
<input type="radio" value="yes" name="continue"[['continue' == 'yes'| checked='checked'|]]> Yes
<input type="radio" value="no" name="continue"[['continue' == 'no'| checked='checked'|]]> No

<P>

Once you are done, click the "Save" button.

END
		);
		
		return $w;
	}
	
	/**
	 * Tells the requirement class to perform its update/install operation. If user input is required, it is passed in the form of a {@link WizardStep} containing field values.
	 * @param optional array $properties An array of {@link WizardProperty} objects corresponding to the {@link Wizard} as created by {@link createWizard()}.
	 * @access public
	 * @return int Returns the new status of this requirement after attempting update.
	 */
	function doUpdate( $properties = null )
	{
		if (
		$properties["color"]->getValue() == "puse" &&
		$properties["age"]->getValue() >= 20 &&
		$properties["continue"]->getValue() == "yes" ) {
			$this->_status = STARTUP_STATUS_OK;
			$this->_data =& $properties;
			debug::output("DummyWizardRequirement - updated successfully with: ".
					$properties["name"]->getValue() . ", " .
					$properties["color"]->getValue() . ", " .
					$properties["age"]->getValue() . ", " .
					$properties["continue"]->getValue(), 7, "StartupCheck");
		}
		
		return $this->_status;
	}
}