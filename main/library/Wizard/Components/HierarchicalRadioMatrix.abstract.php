<?php
/**
 * @since 11/12/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HierarchicalRadioMatrix.abstract.php,v 1.1 2007/11/15 19:25:57 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/RadioMatrix.abstract.php");

/**
 * The HierarchicalRadioMatrix is like the radio matrix, with the difference that
 * fields are arranged hierarchically. Rules are compared against the parent field 
 * rather than the previous field.
 * 
 * @since 11/12/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HierarchicalRadioMatrix.abstract.php,v 1.1 2007/11/15 19:25:57 adamfranco Exp $
 */
abstract class HierarchicalRadioMatrix
	extends RadioMatrix
{
	/**
	 * @var array $rootFields;  
	 * @access private
	 * @since 11/14/07
	 */
	private $rootFields;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 11/15/07
	 */
	public function __construct () {
		parent::__construct();
		$this->rootFields = array();
		$this->jsClass = 'HierarchicalRadioMatrix';
	}
	
	/**
	 * Answer the current fields
	 * 
	 * @return array
	 * @access public
	 * @since 11/1/07
	 */
	public function getRootFields () {
		return $this->rootFields;
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
		try {
			parent::addField($key, $displayText, $initialValue, $rule);
		} catch (RuleValidationFailedException $e) {}
		
		$this->rootFields[] = $this->fields[count($this->fields) - 1];
		
		try {
			$this->validateState();
		} catch (RuleValidationFailedException $e) {
			throw new RuleValidationFailedException("Default state does not validate against the rules supplied. Please change either the default values of the fields or the rules in order to have a valid initial state.");
		}
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
	 * @param string $parentKey
	 * @param string $key
	 * @param string $displayText
	 * @param optional mixed $initialValue null or an option value.
	 * @param optional mixed $rule One of null, '<', '<=', '==', '>=', '>'.
	 * @return void
	 * @access public
	 * @since 11/1/07
	 */
	public function addChildField ($parentKey, $key, $displayText, $initialValue = null, $rule = null) {
		ArgumentValidator::validate($parentKey, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($key, NonZeroLengthStringValidatorRule::getRule());
		ArgumentValidator::validate($displayText, NonZeroLengthStringValidatorRule::getRule());
		if (!is_null($initialValue))
			ArgumentValidator::validate($initialValue, NonZeroLengthStringValidatorRule::getRule());
		if (!is_null($rule)) {
			ArgumentValidator::validate($rule, ChoiceValidatorRule::getRule('<', '<=', '==', '>=', '>'));
		}
		
		parent::addField($key, $displayText, $initialValue, null);
		$field = $this->fields[count($this->fields) - 1];
		$field->rule = $rule;
		
		$this->fields[$this->getFieldIndex($parentKey)]->addChild($field);
				
		try {
			$this->validateState();
		} catch (RuleValidationFailedException $e) {
			throw new RuleValidationFailedException("Default state does not validate against the rules supplied. Please change either the default values of the fields or the rules in order to have a valid initial state.");
		}
	}
	
	/**
	 * Create a new field object
	 * 
	 * @return RadioMatrixField
	 * @access protected
	 * @since 11/14/07
	 */
	protected function createField () {
		return new HierarchicalRadioMatrixField;
	}
	
	/**
	 * Validate the state of the fields. Throws an exception on failure.
	 * 
	 * @return void
	 * @access protected
	 * @since 11/1/07
	 */
	protected function validateState () {
		foreach ($this->fields as $field) {
			foreach ($field->getChildren() as $child) {
				if (!$this->isRelationValid($child, $field))
					throw new RuleValidationFailedException("Rule validation failed");
			}
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
		if (!isset($this->fields[$fieldNum]) || !is_object($this->fields[$fieldNum]))	
			throw new Exception("Invalid field number, $fieldNum.");
		if (!isset($this->options[$optionNum]))	
			throw new Exception("Invalid option number, $optionNum.");
		
		// Store the initial state to roll-back if needed.
		$initialState = $this->getState();
		
		try {
			$this->fields[$fieldNum]->value = $optionNum;
			// Apply the rules to previous fields
			$this->applyRulesUp($this->fields[$fieldNum]);
			
			// Apply the rules to previous fields
			$this->applyRulesDown($this->fields[$fieldNum]);
				
		} 
		// Roll back changes if rules couldn't be applied.
		catch (RuleValidationFailedException $e) {
			$this->setState($initialState);
		}
		
		$this->validateState();
	}
	
	/**
	 * Apply Rules Up
	 * 
	 * @param object HierarchicalRadioMatrixField $field
	 * @return void
	 * @access public
	 * @since 11/14/07
	 */
	public function applyRulesUp (HierarchicalRadioMatrixField $field) {
		$parent = $field->getParent();
		if ($parent) {
			$this->applyRuleToAbove($field, $parent);
			$this->applyRulesUp($parent);
			$this->applyRulesDown($parent);
		}
	}
	
	/**
	 * Apply Rules down
	 * 
	 * @param object HierarchicalRadioMatrixField $field
	 * @return void
	 * @access protected
	 * @since 11/14/07
	 */
	protected function applyRulesDown (HierarchicalRadioMatrixField $field) {
		foreach ($field->getChildren() as $child) {
			$this->applyRuleToBelow($child, $field);
			$this->applyRulesDown($child);
		}
	}
}

/**
 * A data container for fields
 * 
 * @since 11/12/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HierarchicalRadioMatrix.abstract.php,v 1.1 2007/11/15 19:25:57 adamfranco Exp $
 */
class HierarchicalRadioMatrixField
	extends RadioMatrixField 
{
	
	/**
	 * @var array $children;  
	 * @access private
	 * @since 11/13/07
	 */
	private $children;
	
	/**
	 * @var object HierarchicalRadioMatrixField $parent
	 * @access private
	 * @since 11/14/07
	 */
	private $parent = null;
	
	/**
	 * @var array $childFieldnames;  
	 * @access public
	 * @since 11/15/07
	 */
	public $childFieldnames;
	
	/**
	 * @var string $parentFieldname;  
	 * @access public
	 * @since 11/15/07
	 */
	public $parentFieldname = null;
	
	/**
	 * Constructor
	 *
	 * @return void
	 * @access public
	 * @since 11/15/07
	 */
	public function __construct () {
		parent::__construct();
		$this->children = array();
	}
	
	/**
	 * Add a child to this field
	 * 
	 * @param object HierarchicalRadioMatrixField $child
	 * @return void
	 * @access public
	 * @since 11/14/07
	 */
	public function addChild (HierarchicalRadioMatrixField $child) {
		foreach ($this->children as $aChild) {
			if ($child->key == $aChild->key)
				throw new Exception("Duplicate field key, '".$child->key."'.");
		}
		
		$this->children[] = $child;
		$child->parent = $this;
	}
	
	/**
	 * Answer the parent of the field.
	 * 
	 * @return object HierarchicalRadioMatrixField or null
	 * @access public
	 * @since 11/14/07
	 */
	public function getParent () {
		return $this->parent;
	}
	
	/**
	 * Answer the children of this field
	 * 
	 * @return array
	 * @access public
	 * @since 11/14/07
	 */
	public function getChildren () {
		return $this->children;
	}
	
	/**
	 * Set the fieldname
	 * 
	 * @param string $fieldname
	 * @return void
	 * @access public
	 * @since 11/15/07
	 */
	public function setFieldname ($fieldname) {
		$this->fieldname = RequestContext::name($fieldname."_".$this->index);
		
		if ($this->parent)
			$this->parentFieldname = RequestContext::name($fieldname."_".$this->parent->index);
		
		$this->childFieldnames = array();
		foreach ($this->children as $child)
			$this->childFieldnames[] = RequestContext::name($fieldname."_".$child->index);
	}
}
?>