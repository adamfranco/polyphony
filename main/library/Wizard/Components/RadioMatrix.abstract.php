<?php
/**
 * @since 11/1/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RadioMatrix.abstract.php,v 1.2 2007/11/05 21:45:32 adamfranco Exp $
 */ 

/**
 * The RadioMatrix is an inteface element that presents the user with 
 * a matrix of RadioButtons that allow choosing from a list of options for each
 * of a number of fields.
 *
 * By default, each field can be set to any option. Rules can be applied however
 * to enforce that later rows have an option less than, less than or equal, 
 * greater than, or greater than or equal to prior rows. This allows the interface
 * to give the user real-time feedback to how options should be set.
 *
 * The RadioMatrix is an abstract class. Look to its concrete forms, RowRadioMatrix and 
 * ColumnRadioMatrix, which place fields in rows or columns respectively and options 
 * in the direction.
 * 
 * @since 11/1/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RadioMatrix.abstract.php,v 1.2 2007/11/05 21:45:32 adamfranco Exp $
 */
abstract class RadioMatrix
	extends WizardComponent
{
	
	/**
	 * @var array $options; The options of the matrix 
	 * @access private
	 * @since 11/1/07
	 */
	private $options = array();
	
	/**
	 * @var array $fields; The fields of the matrix 
	 * @access private
	 * @since 11/1/07
	 */
	private $fields = array();
	
	/**
	 * Add a new option to the matrix
	 * 
	 * @param string $value
	 * @param string $displayText
	 * @param string $description
	 * @return void
	 * @access public
	 * @since 11/1/07
	 */
	public function addOption ($value, $displayText, $description = null) {
		ArgumentValidator::validate($value, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($displayText, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($description, OptionalRule::getRule(NonZeroLengthStringValidatorRule::getRule()));
		
		$option = new StdClass;
		$option->value = $value;
		$option->displayText = $displayText;
		$option->description = $description;
		$this->options[] = $option;
	}
	
	/**
	 * Answer the current options
	 * 
	 * @return array
	 * @access public
	 * @since 11/1/07
	 */
	public function getOptions () {
		return $this->options;
	}
	
	/**
	 * Add a new field to the matrix. Each field can be set to one of the options. 
	 *
	 * The final parameter allows you to set how the value of this field must
	 * relate to the field prior to it. If null, then no relation is enforced. Valid options
	 * for this parameter are:
	 *		null
	 *		'<'
	 *		'<='
	 *		'=='
	 *		'>='
	 *		'>'
	 *
	 * When an field is changed, all other fields will attempt to move and respect
	 * the rules set. If the rules cannot be met, then the field change is reverted.
	 * 
	 * @param string $key
	 * @param string $displayText
	 * @param optional mixed $initialValue null or an option value.
	 * @param optional mixed $rule One of null, '<', '<=', '==', '>=', '>'.
	 * @return void
	 * @access public
	 * @since 11/1/07
	 */
	public function addField ($key, $displayText, $initialValue = null, $rule = null) {
		ArgumentValidator::validate($key, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($displayText, NonZeroLengthStringValidatorRule::getRule());
		if (!is_null($initialValue))
			ArgumentValidator::validate($initialValue, NonZeroLengthStringValidatorRule::getRule());
		if (!is_null($rule)) {
			ArgumentValidator::validate($rule, ChoiceValidatorRule::getRule('<', '<=', '==', '>=', '>'));
		}
		
		$field = new StdClass;
		$field->key = $key;
		$field->displayText = $displayText;
		if (is_null($initialValue))
			$field->value = 0;
		else
			$field->value = $this->getOptionNumber($initialValue);
		$field->rule = $rule;
		$field->spacerAfter = false;
		
		$field->disabledOptions = array();
		$this->fields[] = $field;
		
		try {
			$this->validateState();
		} catch (RuleValidationFailedException $e) {
			throw new RuleValidationFailedException("Default state does not validate against the rules supplied. Please change either the default values of the fields or the rules in order to have a valid initial state.");
		}
	}
	
	/**
	 * Add a spacer after the most recently added field
	 * 
	 * @return void
	 * @access public
	 * @since 11/2/07
	 */
	public function addSpacer () {
		if (!count($this->fields))
			throw new Exception("Cannot add a spacer before any fields. Please add a field first.");
		$this->fields[count($this->fields) - 1]->spacerAfter = true;
	}
	
	/**
	 * Answer the current fields
	 * 
	 * @return array
	 * @access public
	 * @since 11/1/07
	 */
	public function getFields () {
		return $this->fields;
	}
	
	/**
	 * Answer the index of the field specified by key
	 * 
	 * @param string $fieldKey
	 * @return int
	 * @access protected
	 * @since 11/5/07
	 */
	protected function getFieldIndex ($fieldKey) {
		for ($i = 0; $i < count ($this->fields); $i++) {
			if ($this->fields[$i]->key === $fieldKey)
				return $i;
		}
		
		throw new Exception ("Unknown field, '$fieldKey'.");
	}
	
	/**
	 * Answer the number that corresponds to the option value-string passed
	 * 
	 * @param string $optionValue
	 * @return integer
	 * @access protected
	 * @since 11/1/07
	 */
	protected function getOptionNumber ($optionValue) {
		for ($i = 0; $i < count ($this->options); $i++) {
			if ($this->options[$i]->value === $optionValue)
				return $i;
		}
		
		throw new Exception ("Unknown option, '$optionValue'.");
	}
	
	/**
	 * Disable an option for a field
	 * 
	 * @param string $fieldKey
	 * @param string $optionValue
	 * @return void
	 * @access public
	 * @since 11/5/07
	 */
	public function makeDisabled ($fieldKey, $optionValue) {
		$field = $this->fields[$this->getFieldIndex($fieldKey)];
		$field->disabledOptions[] = $this->getOptionNumber($optionValue);
	}
	
	/**
	 * Validate the state of the fields. Throws an exception on failure.
	 * 
	 * @return void
	 * @access protected
	 * @since 11/1/07
	 */
	protected function validateState () {
		for ($i = 1; $i < count($this->fields); $i++) {
			if (!$this->isRelationValid($this->fields[$i], $this->fields[$i - 1]))
				throw new RuleValidationFailedException("Rule validation failed");
		}
	}
	
	/**
	 * Answer the current state
	 * 
	 * @return array
	 * @access protected
	 * @since 11/1/07
	 */
	protected function getState () {
		$state = array();
		for ($i = 0; $i < count($this->fields); $i++) {
			$state[] = $this->fields[$i]->value;
		}
		
		return $state;
	}
	
	/**
	 * Set the current state
	 * 
	 * @param array $state
	 * @return void
	 * @access protected
	 * @since 11/1/07
	 */
	protected function setState (array $state) {
		// Check that the state has valid entries;
		for ($i = 0; $i < count($this->fields); $i++) {
			if (!isset($state[$i]) || !is_int($state[$i]) || $state[$i] < 0 || $state[$i] >= count($this->options))
				throw new Exception ("Invalid state ".print_r($state, true).".");
		}
		
		// Set the state
		for ($i = 0; $i < count($this->fields); $i++) {
			$this->fields[$i]->value = $state[$i];
		}
	}
	
	/**
	 * Check a particular rule
	 * 
	 * @param object $fieldBelow
	 * @param object $fieldAbove
	 * @return boolean
	 * @access private
	 * @since 11/1/07
	 */
	private function isRelationValid (StdClass $fieldBelow, StdClass $fieldAbove) {
		switch ($fieldBelow->rule) {
			case '<':
				return ($fieldBelow->value < $fieldAbove->value);
			case '<=':
				return ($fieldBelow->value <= $fieldAbove->value);
			case '==':
				return ($fieldBelow->value == $fieldAbove->value);
			case '>=':
				return ($fieldBelow->value >= $fieldAbove->value);
			case '>':
				return ($fieldBelow->value > $fieldAbove->value);
			case null:
				return true;
			default:
				throw new Exception("Unknown rule, '".$fieldBelow->rule."'.");
		}
	}
	
	/**
	 * Set a value of one of the fields
	 * 
	 * @param integer $fieldNum
	 * @param integer $optionNum
	 * @return boolean true if the requested change was performed, false if it couldn't be.
	 * @access protected
	 * @since 11/1/07
	 */
	protected function setField ($fieldNum, $optionNum) {
		//Argument Validation
		if (!isset($this->fields[$fieldNum]))	
			throw new Exception("Invalid field number, $fieldNum.");
		if (!isset($this->options[$optionNum]))	
			throw new Exception("Invalid option number, $optionNum.");
		
		// Store the initial state to roll-back if needed.
		$initialState = $this->getState();
		
		try {
			$this->fields[$fieldNum]->value = $optionNum;
			// Apply the rules to previous fields
			for ($i = $fieldNum - 1; $i >= 0; $i--)
				$this->applyRuleToAbove($this->fields[$i + 1], $this->fields[$i]);
			
			// Apply the rules to previous fields
			for ($i = $fieldNum + 1; $i < count($this->fields); $i++)
				$this->applyRuleToBelow($this->fields[$i], $this->fields[$i - 1]);
		} 
		// Roll back changes if rules couldn't be applied.
		catch (RuleValidationFailedException $e) {
			$this->setState($initialState);
		}
		
		$this->validateState();
	}
	
	/**
	 * Apply a rule to a prior field
	 * 
	 * @param object $fieldBelow
	 * @param object $fieldAbove
	 * @return void
	 * @access private
	 * @since 11/1/07
	 */
	private function applyRuleToAbove (StdClass $fieldBelow, StdClass $fieldAbove) {
		if (!$this->isRelationValid($fieldBelow, $fieldAbove)) {
			switch ($fieldBelow->rule) {
				case '<':
					if ($fieldBelow->value >= (count($this->options) - 1))
						throw new RuleValidationFailedException("Cannot set field ".$fieldAbove->key." to an option greater than ".(count($this->options) - 1).".");
						
					$fieldAbove->value = $fieldBelow->value + 1;
					return;
				case '<=':
				case '==':
				case '>=':
					$fieldAbove->value = $fieldBelow->value;
					return;
				case '>':
					if ($fieldBelow->value <= 0)
						throw new RuleValidationFailedException("Cannot set field ".$fieldAbove->key." to an option less than 0.");
						
					$fieldAbove->value = $fieldBelow->value - 1;
					return;
				case null:
					return;
				default:
					throw new Exception("Unknown rule, '".$fieldBelow->rule."'.");
			}
		}
	}
	
	/**
	 * Apply a rule to a prior field
	 * 
	 * @param object $fieldBelow
	 * @param object $fieldAbove
	 * @return void
	 * @access private
	 * @since 11/1/07
	 */
	private function applyRuleToBelow (StdClass $fieldBelow, StdClass $fieldAbove) {
		if (!$this->isRelationValid($fieldBelow, $fieldAbove)) {
			switch ($fieldBelow->rule) {
				case '<':
					if ($fieldAbove->value <= 0)
						throw new RuleValidationFailedException("Cannot set field ".$fieldBelow->key." to an option less than 0.");
					
						
					$fieldBelow->value = $fieldAbove->value - 1;
					return;
				case '<=':
				case '==':
				case '>=':
					$fieldBelow->value = $fieldAbove->value;
					return;
				case '>':
					if ($fieldAbove->value >= (count($this->options) - 1))
						throw new RuleValidationFailedException("Cannot set field ".$fieldBelow->key." to an option greater than ".(count($this->options) - 1).".");
						
					$fieldBelow->value = $fieldAbove->value + 1;
					return;
				case null:
					return;
				default:
					throw new Exception("Unknown rule, '".$fieldBelow->rule."'.");
			}
		}
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	public function getAllValues () {
		$values = array();
		for ($i = 0; $i < count($this->fields); $i++) {
			$field = $this->fields[$i];
			$values[$field->key] = $this->options[$field->value]->value;
		}
		
		return $values;
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	public function update ($fieldName) {
		$initialState = $this->getState();
		
		for ($i = 0; $i < count($this->fields); $i++) {
			$field = $this->fields[$i];
			$newValue = RequestContext::value($fieldName."_".$i);
			if ($newValue !== false && $newValue !== null && $newValue != $initialState[$i]) 
				$this->setField($i, intval($newValue));
		}
	}
	
	/**
	 * Answer the radio button for a given field index and option index
	 * 
	 * @param string $fieldName
	 * @param int $fieldIndex
	 * @param int $optionIndex
	 * @return string An HTML form element
	 * @access protected
	 * @since 11/1/07
	 */
	protected function getMatrixButton ($fieldName, $fieldIndex, $optionIndex) {
		$componentId = RequestContext::name($fieldName);
		ob_start();
		print "<input type='radio' ";
		print "name='".RequestContext::name($fieldName."_".$fieldIndex)."' ";
		print "value='".$optionIndex."' ";
		if ($this->fields[$fieldIndex]->value == $optionIndex)
			print " checked='checked'";
		
		if (!$this->isEnabled() || in_array($optionIndex, $this->fields[$fieldIndex]->disabledOptions))
			print " disabled='disabled'";
		else
			print " onclick=\"window.".$componentId.".setField(this); \" ";
		print "/>";
		return ob_get_clean();
	}
	
	/**
	 * Answer a string of javascript that contains functions for applying/validating
	 * rules as well as a definition of the rules.
	 * 
	 * @return string
	 * @access protected
	 * @since 11/2/07
	 */
	protected function getRulesJS ($fieldName) {
		$componentId = RequestContext::name($fieldName);
		print "
<script type='text/javascript'>
// <![CDATA[

";
		print file_get_contents(POLYPHONY."/javascript/RadioMatrix.js");
		
		print "\nwindow.".$componentId." = new RadioMatrix(";
		print "\n\t".json_encode($this->options).", ";
		foreach ($this->fields as $fieldIndex => $field)
			$field->fieldname = RequestContext::name($fieldName."_".$fieldIndex);
		print "\n\t".json_encode($this->fields)."\n); ";
		
		print "
		
// ]]>
</script>
";
	}
}

/**
 * An exception to catagorize our non-fatal exceptions
 * 
 * @since 11/1/07
 * @package <##>
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: RadioMatrix.abstract.php,v 1.2 2007/11/05 21:45:32 adamfranco Exp $
 */
class RuleValidationFailedException
	extends Exception
{
	
}

?>