<?php
/**
 * @since 6/4/07
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SingleStepWizard.class.php,v 1.5 2007/11/16 15:59:09 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/SimpleStepWizard.class.php");

/**
 * This is a single-step wizard. It operates just like the multiple-step wizard,
 * but doesn't have or allow more than one step.
 * 
 * @since 6/4/07
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SingleStepWizard.class.php,v 1.5 2007/11/16 15:59:09 adamfranco Exp $
 */
class SingleStepWizard 
	extends SimpleStepWizard
{
	
	/**
	 * Adds a new {@link WizardStep} to this wizard.
	 * @param string $name A short id/name for this step.
	 * @param ref object $step
	 * @access public
	 * @return ref object
	 */
	function addStep ($name, $step) {
		if (count($this->getSteps())) {
			throwError(new Error("SingleStepWizards can only have one step. Cannot add '".$name."' step.", "Wizard"));
		}
		return parent::addStep($name, $step);
	}
	
	/**
	 * Create a new SingleStepWizard with the text-passed as the layout
	 * 
	 * @param string $text
	 * @return object 
	 * @access public
	 * @static
	 * @since 11/15/07
	 */
	public static function withText ($text, $class = 'SingleStepWizard') {
		return parent::withText($text, $class);
	}
	
	/**
	 * Returns a new SimpleStepWizard with the default layout including all the buttons.
	 * @param optional string $pre Some text to put before the layout text.
	 * @access public
	 * @return ref object
	 * @static
	 */
	static function withDefaultLayout ($pre = '') {
		return parent::withText($pre . 
				"<div>\n" .
				"<table width='100%' border='0' cellpadding='0' cellspacing='2'>\n" .
				"<tr>\n" .
				"<td align='left' width='50%'>\n" .
				"[[_cancel]]\n" .
				"</td>\n" .
				"<td align='right' width='50%'>\n" .
				"[[_save]]\n" .
				"</td></tr></table>" .
				"</div>\n" .
				"<hr/>\n" .
				"<div>\n" .
				"[[_steps]]" .
				"</div>\n", "SingleStepWizard");
	}
	
}

?>