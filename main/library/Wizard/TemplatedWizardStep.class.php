<?

require_once(POLYPHONY."/main/library/Wizard/WizardStep.abstract.php");

/**
 * This is a {@link WizardStep} that gets its output text from a {@link Template}.
 * @package polyphony.wizard
 * @copyright 2004
 * @version $Id: TemplatedWizardStep.class.php,v 1.1 2004/07/22 19:36:50 gabeschine Exp $
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
	function & getLayout (& $harmoni) {
		// build our fieldset of values
		$fSet =& new FieldSet;
		foreach (array_keys($this->_properties) as $key) {
			$fSet->set($key, $this->_properties[$key]->getValue());
		}
		$this->_text = $this->_template->catchOutput($fSet);
		
		$stepLayout =& new SingleContentLayout (TEXT_BLOCK_WIDGET, 2);
		
		$text = $this->_parseText();
		
		$stepLayout->addComponent(new Content($text));
		
		return $stepLayout;
	}
	
}