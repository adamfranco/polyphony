<?

require_once(dirname(__FILE__)."/WizardProperty.class.php");
require_once(dirname(__FILE__)."/WizardStep.abstract.php");

/**
 * The Wizard class provides a system for registering Wizard properties and 
 * associating those properties with the appropriate form elements.
 *
 * @package polyphony.wizard
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 * @version $Id: WizardStep.class.php,v 1.8 2004/12/22 17:04:05 adamfranco Exp $
 */

class WizardStep 
	extends WizardStepAbstract {
		
	/**
	 * Constructor
	 * @param string $displayName The displayName of this step.
	 */
	function WizardStep ( $displayName ) {
		ArgumentValidator::validate($displayName, new StringValidatorRule, true);
		
		$this->_displayName = $displayName;
		$this->_properties = array();
	}
	
	/**
	 * Sets the text of this wizard step. The text is a string and can contain
	 * elements that will be parsed with values from the current state of the
	 * step properties.
	 *
	 * Parsed elements can have two forms:
	 * 		[[PropertyName]]
	 * or
	 * 		[[PropertyName Operator ComparisonValue|StringIfTrue|StringIfFalse]]
	 * 
	 * The property-name element should not be quoted unless quotes are
	 * nessisary for use in the comparison string. In that case single quotes, ',
	 * should be used. Examples:
	 *
	 * 		<input type='text' name='title' value='[[title]]' />
	 *
	 * 		<input type='text' name='age' value='[[age]]' /> [[age < 18|*You are not old enough!*|You are old enough.]]
	 * 		
	 * 		<input type='radio' name='width' value='5' [[width == 5| checked='checked'|]] /> Narrow Width
	 * 		<input type='radio' name='width' value='10' [[width == 10| checked='checked'|]] /> Wide Width
	 * 		
	 * 		<input type='radio' name='size' value='S' [['size' == 'S'| checked='checked'|]] /> Small
	 * 		<input type='radio' name='size' value='L' [['size' == 'L'| checked='checked'|]] /> Large
	 * 
	 * @param string $text The HTML text for this step.
	 * @access public
	 * @return void
	 */
	function setText ( $text ) {
		ArgumentValidator::validate($text, new StringValidatorRule, true);
		
		$this->_text = $text;
	}
	
}

?>