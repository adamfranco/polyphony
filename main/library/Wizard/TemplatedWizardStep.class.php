<?php
/**
 *
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TemplatedWizardStep.class.php,v 1.4 2005/03/31 20:52:42 adamfranco Exp $
 */

/**
 * Require our needed classes.
 * 
 */
require_once(POLYPHONY."/main/library/Wizard/WizardStep.abstract.php");

/**
 * This is a {@link WizardStep} that gets its output text from a {@link Template}.
 * @package polyphony.library.wizard
 * @copyright 2004
 * @version $Id: TemplatedWizardStep.class.php,v 1.4 2005/03/31 20:52:42 adamfranco Exp $
 */
class TemplatedWizardStep extends WizardStepAbstract {
	
	var $_template;
	
	/**
	 * @param string $displayName
	 * @param ref object $template A {@link Template}.
	 */
	function TemplatedWizardStep($displayName, &$template) {
		$this->_displayName = $displayName;
		$this->_template =& $template;
	}
	

	/**
	 * Returns a layout of content for this WizardStep
	 * @param object Harmoni The harmoni object which contains the current context.
	 * @return object Layout
	 */
	function &getLayout (& $harmoni) {
		// build our fieldset of values
		$fSet =& new FieldSet;
		foreach (array_keys($this->_properties) as $key) {
			$fSet->set($key, $this->_properties[$key]->getValue());
		}
		$this->_text = $this->_template->catchOutput($fSet);
				
		$text = $this->_parseText();
		
		$stepLayout =& new Block ($text, 3);
		return $stepLayout;
	}
	
}