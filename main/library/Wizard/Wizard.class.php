<?

require_once(dirname(__FILE__)."/WizardStep.class.php");
require_once(dirname(__FILE__)."/MultiValuedWizardStep.class.php");

/**
 * The Wizard class provides a system for posting, retrieving, and
 * validating user input over a series of steps, as well as maintianing
 * the submitted values over a series of steps, until the wizard is saved.
 * The wizard is designed to be called from within a single action. The values
 * of its state allow its steps to work as "sub-actions". 
 *
 * @package polyphony.wizard
 * @author Adam Franco
 * @copyright 2004 Middlebury College
 * @access public
 * @version $Id: Wizard.class.php,v 1.15 2004/11/10 17:18:13 rrichards Exp $
 */

class Wizard {
	
	/**
	 * The title of this Wizard
	 * @attribute private string _displayName
	 */
	 var $_displayName;
	
	/**
	 * The (1-based) number of the current step.
	 * @attribute private integer _currentStep
	 */
	var $_currentStep;
	
	/**
	 * The steps within the Wizard.
	 * @attribute private array _steps
	 */
	var $_steps;
	
	/**
	 * If true, steps can be accessed non-linearly.
	 * @attribute private boolean _allowStepLinks
	 */
	 var $_allowStepLinks;	
	 
	/**
	 * If true, users can cancel out of the Wizard.
	 * @attribute private boolean _allowCancel
	 */
	 var $_allowCancel;	
	
	/**
	 * Constructor
	 * @param string $displayName The title of this wizard.
	 * @param boolean $allowStepLinks If true, steps can be accessed non-linearly.
	 * @param boolean $allowCancel If true, users can cancel out of the Wizard.
	 * @return void
	 */
	function Wizard ( $displayName, $allowStepLinks = TRUE, $allowCancel = TRUE ) {
		ArgumentValidator::validate($displayName, new StringValidatorRule, true);
		ArgumentValidator::validate($allowStepLinks, new BooleanValidatorRule, true);
		ArgumentValidator::validate($allowCancel, new BooleanValidatorRule, true);
		
		$this->_displayName = $displayName;
		$this->_allowStepLinks = $allowStepLinks;
		$this->_allowCancel = $allowCancel;
		$this->_currentStep = 1;
		$this->_steps = array();
	}
	
	/**
	 * Adds a new Step in the Wizard
	 * @param string $displayName The displayName of this step.
	 * @return object The new step.
	 */
	function &createStep ( $displayName ) {
		$stepNumber = count($this->_steps) + 1;
		$this->_steps[$stepNumber] =& new WizardStep ( $displayName );
		return $this->_steps[$stepNumber];
	}
	
	/**
	 * Adds a class as a new Step in the Wizard
	 * @param object WizardStepInterface $step The class to add as a step in this wizard.
	 * @return object The new step.
	 */
	function &addStep ( & $step ) {
		ArgumentValidator::validate($step, new ExtendsValidatorRule("WizardStepInterface"), true);
		
		$stepNumber = count($this->_steps) + 1;
		$this->_steps[$stepNumber] =& $step;
		return $this->_steps[$stepNumber];
	}
	
	/**
	 * If the values of the current step are good, make the requested step
	 * the current one.
	 * @param integer $stepNumber The number of the step to go to.
	 * @return void
	 */
	function goToStep ( $stepNumber ) {
		ArgumentValidator::validate($stepNumber, new IntegerValidatorRule, true);
		if (!$this->_steps[$stepNumber])
			throwError(new Error("Step, ".$stepNumber.", does not exist in Wizard.", "Wizard", 1));

		if ($this->_steps[$this->_currentStep]->updateProperties())
			$this->_currentStep = $stepNumber;
	}
	
	/**
	 * Make the requested step the current one without checking other step's values.
	 * This is usefull for starting non-linear wizard access.
	 * @param integer $stepNumber The number of the step to go to.
	 * @return void
	 */
	function skipToStep ( $stepNumber ) {
		ArgumentValidator::validate($stepNumber, new IntegerValidatorRule, true);
		if (!$this->_steps[$stepNumber])
			throwError(new Error("Step, ".$stepNumber.", does not exist in Wizard.", "Wizard", 1));

		$this->_currentStep = $stepNumber;
	}
	
	/**
	 * If the values of the current step are good, move to the next step.
	 * @return void
	 */
	function next () {
		if ($this->_currentStep == count($this->_steps))
			throwError(new Error("No more steps in Wizard.", "Wizard", 1));

		if ($this->_steps[$this->_currentStep]->updateProperties())
			$this->_currentStep = $this->_currentStep + 1;
	}
	
	/**
	 * If the values of the current step are good, move to the previous step.
	 * @return void
	 */
	function previous () {
		if ($this->_currentStep == 1)
			throwError(new Error("No more steps in Wizard.", "Wizard", 1));

		if ($this->_steps[$this->_currentStep]->updateProperties())
			$this->_currentStep = $this->_currentStep - 1;
	}
	
	/**
	 * True if there is a next step
	 * @return boolean
	 */
	function hasNext () {
		if ($this->_currentStep == count($this->_steps))
			return false;
		else
			return true;
	}
	
	/**
	 * True if there is a previous step
	 * @return boolean
	 */
	function hasPrevious () {
		if ($this->_currentStep == 1)
			return false;
		else
			return true;
	}
	
	/**
	 * Returns a array of all properties in all steps.
	 * If steps have properties of the same name, there could be conflicts.
	 * @return array An array of Property objects.
	 */
	function &getProperties() {
		$allProperties = array();
	
		foreach (array_keys($this->_steps) as $number) {
			$stepProperties =& $this->_steps[$number]->getProperties();
			foreach (array_keys($stepProperties) as $name) {
				$allProperties[$name] =& $stepProperties[$name];
			}
		}
	
		return $allProperties;
	}
	
	/**
	 * Update the properties of the last step. The should generally be called
	 * before saving data if the last step has not been updated via a next()
	 * or previous() command.
	 *
	 * @access public
	 * @return boolean True on success. False on invalid Property values.
	 */
	function updateLastStep () {
		return $this->_steps[$this->_currentStep]->updateProperties();
	}
	
	/**
	 * Update the status of the Wizard from the submitted form values and
	 * handle movement to other steps in the wizard.
	 * @access public
	 * @return void
	 */
	function update () {
		debug::output(printpre($_REQUEST, TRUE));
		if ($_REQUEST['__next'] && $this->hasNext())
			$this->next();
		
		else if ($_REQUEST['__previous'] && $this->hasPrevious())
			$this->previous();
		
		else if ($_REQUEST['__go_to_step'])
			$this->goToStep($_REQUEST['__go_to_step']);
		
		else if ($_REQUEST['__skip_to_step'] && $this->_allowStepLinks)
			$this->skipToStep($_REQUEST['__skip_to_step']);
			
		else if ($_REQUEST['__update'])
			$this->updateLastStep();
	}
	
	/**
	 * Go through all properties of all steps (except the current one) and 
	 * checks the validity of their stored values. Return false if any of 
	 * the submitted values are invalid. The current Step is excluded to
	 * since the user might change them and their future state is unknown.
	 *
	 * @access public
	 * @return boolean True on success. False on invalid Property values.
	 */
	function arePropertiesValid () {
		$valid = TRUE;
		foreach (array_keys($this->_steps) as $number) {
			if ($number != $this->_currentStep && !$this->_steps[$number]->arePropertiesValid())
				$valid = FALSE;
		}
		
		return $valid;
	}
	
	/**
	 * Returns TRUE if one of the "Save" buttons was clicked AND the properties 
	 * validate successfully.
	 * @return boolean
	 */
	function isSaveRequested () {
		if ($_REQUEST['__save'] || $_REQUEST['__save_link']) {
			if ($this->updateLastStep()) return true;
			return false;
		} else
			return FALSE;
	}
	
	/**
	 * Returns TRUE if one of the "Cancel" buttons was clicked.
	 * @return boolean
	 */
	function isCancelRequested () {
		if ($_REQUEST['__cancel'] || $_REQUEST['__cancel_link'])
			return TRUE;
		else
			return FALSE;
	}
	
	/**
	 * Returns a layout of content for the current Wizard-state
	 * @param object Harmoni The harmoni object which contains the current context.
	 * @return object Layout
	 */
	function &getLayout (& $harmoni) {
	
		// Handle the catching of values from previous form submission and move
		// to the correct step.
		$this->update();
		
		// make sure we have the right textdomain
		$defaultTextDomain = textdomain("polyphony");
		
		ArgumentValidator::validate($harmoni, new ExtendsValidatorRule("Harmoni"), true);
		
		// Make sure we have a valid Wizard
		if (!count($this->_steps))
			throwError(new Error("No steps in Wizard.", "Wizard", 1));
			
		$wizardLayout =& new RowLayout;
		
		// :: Form tags for around the layout :: 
		$wizardLayout->setPreSurroundingText("<form action='".MYURL."/".implode("/", $harmoni->pathInfoParts)."' method='post' id='wizardform' name='wizardform'  enctype='multipart/form-data'>");
		
		ob_start();
		print "\n<input type='hidden' name='__go_to_step' value=''>";
		print "\n<input type='hidden' name='__save_link' value=''>";
		print "\n<input type='hidden' name='__cancel_link' value=''>";
		print "\n</form>";
		$wizardLayout->setPostSurroundingText(ob_get_contents());
		ob_end_clean();
		
		// Add to the page's javascript so we can skip to next pages by
		// adding values to the hiddenFields above.
		$javaScript = "
			
			// Set a flag to save the form after it is submited
			function save() {
				document.wizardform.__save_link.value = 'save';
				document.wizardform.submit();
			}
			
			// Set a flag to cancel this wizard
			function cancel() {
				document.wizardform.__cancel_link.value = 'cancel';
				document.wizardform.submit();
			}
			
			// Specify which step to go to on submit.
			function goToStep(step) {
				document.wizardform.__go_to_step.value = step;
				document.wizardform.submit();
			}
		
		";
		if (!($theme =& $harmoni->getTheme())) {
			$harmoni->setTheme( new SimpleLinesTheme() );
			$theme =& $harmoni->getTheme();
		}
		$theme->addHeadJavascript($javaScript);
		
		
		// :: Heading ::
		$heading =& new SingleContentLayout(HEADING_WIDGET, 2);
		$heading->addComponent(new Content($this->_displayName.": ".
					$this->_currentStep.". ".
					$this->_steps[$this->_currentStep]->getDisplayName()));
		$wizardLayout->addComponent($heading);
		
		$lower =& new ColumnLayout (TEXT_BLOCK_WIDGET, 2);
		$wizardLayout->addComponent($lower);
			
		// :: Steps Menu ::
		$menu =& new VerticalMenuLayout(MENU_WIDGET, 2);
		$lower->addComponent($menu);
		foreach (array_keys($this->_steps) as $number) {
			$itemText = $number.". ".$this->_steps[$number]->getDisplayName();
			if (!$this->_steps[$number]->arePropertiesValid())
				$itemText .= " *";
			
			if ($number != $this->_currentStep
				&& $this->_allowStepLinks) {
				$menu->addComponent(
					new LinkMenuItem($itemText,
						"Javascript:goToStep('".$number."')",
						FALSE)
				);			
			} else {
				$menu->addComponent(
					new StandardMenuItem($itemText,
						($number == $this->_currentStep)?TRUE:FALSE)
				);
			}
		}
		
		// Save button
		if (($this->_allowStepLinks || !$this->hasNext()) && $this->arePropertiesValid()) {
			$menu->addComponent(
				new LinkMenuItem("<div style='width: 100px'>"._("Save")."</div>",
					"Javascript:save()",
					FALSE)
			);
		} else {
			$menu->addComponent(new StandardMenuItem(_("Save"), FALSE));
		}
		
		// Cancel button
		if ($this->_allowCancel) {
			$menu->addComponent(
				new LinkMenuItem(_("Cancel"),
					"Javascript:cancel()",
					FALSE)
			);
		} else {
			$menu->addComponent(new StandardMenuItem(_("Cancel"), FALSE));
		}
		
		$center = new RowLayout;
		$lower->addComponent($center);
		
		// :: Buttons ::
		$buttons =& new SingleContentLayout (TEXT_BLOCK_WIDGET, 3);
		ob_start();
		print "\n<table width='100%'>";
		if (count($this->_steps) > 1) {
			print "\n\t<tr>";
			print "\n\t\t<td align='left'>";
			if ($this->hasPrevious())
				print "\n\t\t\t<input type='submit' name='__previous' value='"._("Previous")."'>";
			else
				print "\n\t\t\t<input type='button' disabled='disabled' value='"._("Previous")."'>";
			print "\n\t\t</td>";
			print "\n\t\t<td align='right'>";
			if ($this->hasNext())
				print "\n\t\t\t<input type='submit' name='__next' value='"._("Next")."'>";
			else
				print "\n\t\t\t<input type='button' disabled='disabled' value='"._("Next")."'>";
			print "\n\t\t</td>";
			print "\n\t</tr>";
		}
		print "\n\t<tr>";
		print "\n\t\t<td align='left'>";
		if ($this->_allowCancel)
			print "\n\t\t\t<input type='submit' name='__cancel' value='"._("Cancel")."'>";
		else
			print "\n\t\t\t<input type='button' disabled='disabled' value='"._("Cancel")."'>";
		print "\n\t\t</td>";
		print "\n\t\t<td align='right'>";
		if (($this->_allowStepLinks || !$this->hasNext()) && $this->arePropertiesValid())
			print "\n\t\t\t<input type='submit' name='__save' value='"._("Save")."'>";
		else
			print "\n\t\t\t<input type='button' disabled='disabled' value='"._("Save")."'>";
		print "\n\t\t</td>";
		print "\n\t</tr>";
		
		print "\n</table>";
		$buttons->addComponent(new Content(ob_get_contents()));
		ob_end_clean();
		$center->addComponent($buttons);
		
		// :: The Current Step ::
		$stepLayout =& $this->_steps[$this->_currentStep]->getLayout($harmoni);
		$center->addComponent($stepLayout);
		
		// :: Buttons Redeuex ::
		$center->addComponent($buttons);
		
		// go back to the default textdomain
		textdomain($defaultTextDomain);
		
		return $wizardLayout;
	}
}

?>