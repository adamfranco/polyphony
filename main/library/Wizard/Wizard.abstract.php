<?php
/**
 *
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Wizard.abstract.php,v 1.23 2008/01/14 21:23:25 adamfranco Exp $
 */

/*
 * Require our needed classes
 */

require_once(HARMONI."/GUIManager/Components/Block.class.php");
require_once(POLYPHONY."/main/library/Wizard/WizardComponentWithChildren.abstract.php");
require_once(POLYPHONY."/main/library/Wizard/Listeners/WUpdateListener.class.php");

/**
 * The Wizard class provides a system for posting, retrieving, and
 * validating user input over a series of steps, as well as maintianing
 * the submitted values over a series of steps, until the wizard is saved.
 * The wizard is designed to be called from within a single action. The values
 * of its state allow its steps to work as "sub-actions". 
 * 
 * The only method left to implement for classes that extend is getMarkup().
 *
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Wizard.abstract.php,v 1.23 2008/01/14 21:23:25 adamfranco Exp $
 * @author Gabe Schine
 * @abstract
 */

class Wizard extends WizardComponentWithChildren/*, EventTrigger*/ {
	var $_formName = 'wizard';
	var $_id = 'w';
	
	/**
	 * Tells this wizard to update itself and run any events that have happened.
	 * @return void
	 * @access public
	 */
	function go() {
		$this->update($this->_id);
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
	function update ($fieldName) {
		// get all the steps we have here and update them
		$children =$this->getChildren();
		$ok = true;
		foreach (array_keys($children) as $key) {
			if (!$children[$key]->update($fieldName."_".$key)) {
				$ok = false;
			}
		}
		$this->triggerEvent("edu.middlebury.polyphony.wizard.update", $this);
		foreach (array_keys($this->_eventsLater) as $key) {
			$info =$this->_eventsLater[$key];
			$this->triggerEvent($info[0], $info[1], $info[2]);
			unset($this->_eventsLater[$key]);
		}
		return $ok;
	}

	/**
	 * Returns the top-level {@link Wizard} in which this component resides.
	 * @access public
	 * @return ref object
	 */
	function getWizard () {
		return $this;
	}
	
	/**
	 * Sets this Wizard's ID string. It is optional but must be used to allow multiple Wizards
	 * to be displayed on one page to differentiate fields and save/cancel requests.
	 * @param string $id must be alpha-numeric with _ or -. 
	 * @access public
	 * @return void
	 */
	function setIdString ($id) {
		$this->_id = $id;
	}
	
	/**
	 * Returns the ID string.
	 * @access public
	 * @return string
	 */
	function getIdString () {
		return $this->_id;
	}
	
	/**
	 * Returns a layout of content for the current Wizard-state
	 * @return ref object Layout
	 */
	function getLayout () {
		$markup = $this->getMarkup($this->_id);
		
		$obj = new Block($markup, WIZARD_BLOCK);
		
		return $obj;
	}
	
	/**
	 * Sets this component's parent (probably a {@link Wizard} object) so that it can
	 * have access to its parent's information, if needed.
	 * @param ref object
	 * @access public
	 * @return void
	 */
	function setParent ($parent) {
		// do nothing - this component should never have a parent
	}
	
	/**
	 * Returns the name (id) of the form in which the wizard is contained.
	 * @access public
	 * @return string
	 */
	function getWizardFormName () {
		return $this->_id . "_form";
	}
	
	var $_eventsLater = array();
	/**
	 * Triggers this event after the update has occured.
	 * @param string $event
	 * @param ref object $source
	 * @param optional array $context
	 * @access public
	 * @return void
	 */
	function triggerLater ($event, $source, $context = null) {
		$newArray = array();
		$newArray[0] = $event;
		$newArray[1] =$source;
		$newArray[2] = $context;
		$this->_eventsLater[] =$newArray;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		$done = isset($GLOBALS["__wizardJSDone"]);
		if ($done) return '';
		// we are going to output a bunch of javascript to handle wizard
		// validation, etc. 
		$javascript = <<< END

		function addWizardRule(elementID, rule, errorID, displayError) {
			var element = getWizardElement(elementID);
			if (!element)
				alert('WizardError: Could not find element ' + elementID);
			element._ruleCheck = rule;
			element._ruleErrorID = errorID;

			var errEl = getWizardElement(errorID);
		//	errEl.style.position = "absolute";
			errEl.style.display = (displayError?"inline":"none");
			
			
			
			//if(element._ruleCheck){
			//	alert(element.name);
			//}
			
			
		}

		function getWizardElement(id) {
			if (document.layers) return document.layers[id];
			if (document.all) return document.all[id];
			return document.getElementById(id);
		}

		function validateWizard(form) {
//			alert('checking...');
			if (form._ignoreValidation) return true;
			var elements = form.elements;
			var ok = true;
			for(var i = 0; i < form.length; i++) {
				var el = elements[i];

			
				 //alert(el.name);
				
				
				//getWizardElement(el.name);
				
				
			// var al = "hello "+el._ruleErrorID+"  world  "+elementID+"      "+ rule;
			// alert(al);
			
				if (el._ruleCheck) {
				   
					
					var errID = el._ruleErrorID;
					var errDiv = getWizardElement(errID);
					if (!el._ruleCheck(el)) {						
						ok = false;
						// show the error div
						errDiv.style.display = "inline";
					} else {
						errDiv.style.display = "none";
					}
				}
			}
			return ok;
		}

		function ignoreValidation(form) {
//			alert("ignoring...");
			form._ignoreValidation = true;
		}

		function submitWizard(form) {
//			alert("Submit!");
			if (validateWizard(form)) form.submit();
		}
	

END;

		$m = "<script type='text/javascript'>\n";
		$m .= "/*<![CDATA[*/\n" . $javascript . "\n/*]]>*/\n</script>\n";
		
		$GLOBALS["__wizardJSDone"] = true;
		
		return $m;
		
	}
	
	// ------------------------------
	// Utility static methods
	// ---------------------------------
	/**
	 * Returns the parsed string from text including [[tags]].
	 * The second parameter holds a hashtable of components to ask for content for
	 * each of the [[tags]]. 
	 * @param string $text
	 * @param ref array $components
	 * @param optional string $prepend Optionally prepends the string passed to the field names.
	 * @access public
	 * @return string
	 * @static
	 */
	 static function parseText($text, $components, $prepend = '')
	 {
	 	// first get all of the tags we have to work with
	 	preg_match_all("/\[{2}[^\[]*\]{2}/", $text, $matches);
	 	
	 	// to though each of the matches and get the required string
	 	$workingText = $text;
	 	foreach ($matches[0] as $match) {
	 		if (preg_match("/\[{2}fieldname:([^|]*)\]{2}/", $match, $parts)) {
	 			$propName = $parts[1];
	 			// fieldname:xxxx 
	 			if (isset($components[$propName])) {
					$markup = $prepend.$propName;
	 			} else {
	 				$msg = sprintf(dgettext("polyphony", "WIZARD ERROR: could not find a component to match with fieldname:<i>%s</i>!"), $propName);
	 				$markup = "<span style='color: red; font-weight: 900;'>$msg</span>";
	 			}
	 		} else if (preg_match("/\[{2}([^|]*)\]{2}/", $match, $parts)) {
	 			$propName = $parts[1];
	 			if (isset($components[$propName])) {
					$markup = $components[$propName]->getMarkup($prepend.$propName);
	 			} else {
	 				$msg = sprintf(dgettext("polyphony", "WIZARD ERROR: could not find a component to match with <i>%s</i>!"), $propName);
	 				$markup = "<span style='color: red; font-weight: 900;'>$msg</span>";
	 			}
	 		} else {
 				$msg = sprintf(dgettext("polyphony", "WIZARD ERROR: could not find a component name within the tag <i>%s</i>!"), $match);
	 			$markup = "<span style='color: red; font-weight: 900;'>$msg</span>";
	 		}
	 		$workingText = str_replace($match, $markup, $workingText);
	 	}
	 	
		return $workingText;
	 }
	 
	 /**
	  * Similar to parse-text, but only parses fieldname:xxxxxx values and does not
	  * check for field existance.
	  * 
	  * @param string $text
	  * @param optional string $prepend Optionally prepends the string passed to the field names.
	  * @return string
	  * @access public
	  * @since 1/14/08
	  * @static
	  */
	 public static function parseFieldNameText ($text, $prepend = '') {
	 	// first get all of the tags we have to work with
	 	preg_match_all("/\[{2}[^\[]*\]{2}/", $text, $matches);
	 	
	 	// to though each of the matches and get the required string
	 	$workingText = $text;
	 	foreach ($matches[0] as $match) {
	 		if (preg_match("/\[{2}fieldname:([^|]*)\]{2}/", $match, $parts)) {
	 			$propName = $parts[1];
	 			// fieldname:xxxx 
				$markup = $prepend.$propName;
			}
	 		$workingText = str_replace($match, $markup, $workingText);
	 	}
	 	
	 	return $workingText;
	 }
	
	/**
	 * Returns a block of javascript that will add a validation command to the form when submitting.
	 * @param string $elementID The ID of the form element.
	 * @param ref object $rule A {@link WECRule} for error checking.
	 * @param string $errDivID The ID of a div tag that will be displayed if the element doesn't validate.
	 * @param optional boolean $displayError Defaults to FALSE, but allows you to display the error on
	 * pageload, if we need to notify the user immediately of an error. 
	 * @access public
	 * @return string
	 * @static
	 */
	static function getValidationJavascript ($elementID, $rule, $errDivID, $displayError = false) {
		$elementID = str_replace("'", "\\'", $elementID);
		$errDivID = str_replace("'", "\\'", $errDivID);
		$checkFunc = $rule->generateJavaScript();
		$m = "<script type='text/javascript'>\n" .
				"/*<![CDATA[*/\n" .
				"addWizardRule('$elementID', $checkFunc, '$errDivID', ".($displayError?"true":"false").");" .
				"/*]]>*/\n" .
				"</script>\n";
		return $m;
	}
	
	//-----------------------------------
	// EventTrigger methods
	// -----------------------------------
	/**
	 * @var array $_eventListeners
	 * @access private
	 */
	var $_eventListeners = array();
	
	/**
	 * Adds an {@link EventListener} to be triggered whenever an event is thrown.
	 * @param string $eventType The string event type for which this {@link EventListener} is listening (example: "edu.middlebury.harmoni.action_executed")
	 * @param ref object $eventListener the {@link EventListener} object.
	 * @access public
	 * @return ref object
	 */
	function addEventListener ($eventListener) {
		ArgumentValidator::validate($eventListener, HasMethodsValidatorRule::getRule("handleEvent"), true);
		$this->_eventListeners[] = $eventListener;
		
		return $eventListener;
	}
	
	/**
	 * Notifies all of the {@link EventListener}s that have been added that an event has
	 * occured.
	 * @param string $eventType The event type string.
	 * @param ref object $source The source object of this event.
	 * @param optional array $context An array of contextual parameters for the specific event. The content of the array will depend on the event.
	 * @access public
	 * @return void
	 */
	function triggerEvent ($eventType, $source, $context = null) {
		$list = $this->_eventListeners;
		foreach (array_keys($list) as $key) {
			$list[$key]->handleEvent($eventType, $source, $context);
		}
	}
}

