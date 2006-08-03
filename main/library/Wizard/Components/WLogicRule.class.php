<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicRule.class.php,v 1.4 2006/08/03 20:51:57 sporktim Exp $
 */ 

/**
 * Logic Class allows for wizards to be freeform and direct their own progress
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicRule.class.php,v 1.4 2006/08/03 20:51:57 sporktim Exp $
 */
class WLogicRule extends WizardComponent {
		
	var $_requiredSteps;
		
	/**
	 * Default constructor, added for testing
	 **/
	function WLogicRule() {
	}
	
	
	/**
	 * virtual constructor
	 * 
	 * @param array $steps an array of steps required by this logic
	 * @return VOID
	 * @access public
	 * @since 5/31/06
	 */
	function &withSteps ($steps) {
		$rule =& new WLogicRule();
		
		$rule->setRequiredSteps($steps);
		return $rule;
	}
	
	/**
	 * returns an array of the steps required by this rule being fired
	 * 
	 * @return array of names of steps that are now required
	 * @access public
	 * @since 5/31/06
	 */
	function getRequiredSteps () {
		print " required steps: ".count($this->_requiredSteps);
		return $this->_requiredSteps;
	}

	/**
	 * returns an array of the steps required by this rule being fired
	 * 
	 * @param array of names of steps that are now required
	 * @access public
	 * @since 5/31/06
	 */
	function setRequiredSteps ($steps) {
		$this->_requiredSteps = $steps;
	}
	
	/**
	 * adds a required step
	 * 
	 * @param string $stepName
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function addRequiredStepToRule ($stepName) {
		$this->_requiredSteps[] = $stepName;
	}
	
	
}

?>