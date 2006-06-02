<?php
/**
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicRule.class.php,v 1.1 2006/06/02 16:00:28 cws-midd Exp $
 */ 

/**
 * Logic Class allows for wizards to be freeform and direct their own progress
 * 
 * @since 5/31/06
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WLogicRule.class.php,v 1.1 2006/06/02 16:00:28 cws-midd Exp $
 */
class WLogicRule extends WizardComponent {
		
	var $_requiredSteps;
		
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