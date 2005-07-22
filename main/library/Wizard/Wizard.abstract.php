<?
/**
 *
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Wizard.abstract.php,v 1.1 2005/07/22 15:42:19 gabeschine Exp $
 */

/*
 * Require our needed classes
 */

require_once(HARMONI."/GUIManager/Components/Block.class.php");
require_once(POLYPHONY."/main/library/Wizard/WizardComponentWithChildren.abstract.php");

/**
 * The Wizard class provides a system for posting, retrieving, and
 * validating user input over a series of steps, as well as maintianing
 * the submitted values over a series of steps, until the wizard is saved.
 * The wizard is designed to be called from within a single action. The values
 * of its state allow its steps to work as "sub-actions". 
 * 
 * The only method left to implement for classes that extend is getMarkup().
 *
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Wizard.abstract.php,v 1.1 2005/07/22 15:42:19 gabeschine Exp $
 * @author Gabe Schine
 * @abstract
 */

class Wizard extends WizardComponentWithChildren/*, EventTrigger*/ {
	var $_formName = 'wizard';
	
	/**
	 * Tells this wizard to update itself and run any events that have happened.
	 * @return void
	 * @access public
	 */
	function go() {
		$this->update("top");
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
		$children =& $this->getChildren();
		$ok = true;
		foreach (array_keys($children) as $key) {
			if (!$children[$key]->update($key)) {
				$ok = false;
			}
		}
		$this->triggerEvent("edu.middlebury.polyphony.wizard.update", $this);
		foreach (array_keys($this->_eventsLater) as $key) {
			$info =& $this->_eventsLater[$key];
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
	function &getWizard () {
		return $this;
	}
	
	/**
	 * Returns a layout of content for the current Wizard-state
	 * @return ref object Layout
	 */
	function &getLayout () {
		$markup = $this->getMarkup("top");
		
		return new Block($markup, 3);
	}
	
	/**
	 * Sets this component's parent (probably a {@link Wizard} object) so that it can
	 * have access to its parent's information, if needed.
	 * @param ref object
	 * @access public
	 * @return void
	 */
	function setParent (&$parent) {
		// do nothing - this component should never have a parent
	}
	
	/**
	 * Returns the name (id) of the form in which the wizard is contained.
	 * @access public
	 * @return string
	 */
	function getWizardFormName () {
		return $this->_formName;
	}
	
	/**
	 * Sets the name (id) of the form in which this wizard is contained.
	 * @param string $name
	 * @access public
	 * @return void
	 */
	function setWizardFormName ($name) {
		$this->_formName = $name;
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
	function triggerLater ($event, &$source, $context = null) {
		$newArray = array();
		$newArray[0] = $event;
		$newArray[1] =& $source;
		$newArray[2] = $context;
		$this->_eventsLater[] =& $newArray;
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
	 function parseText($text, &$components, $prepend = '')
	 {
	 	// first get all of the tags we have to work with
	 	preg_match_all("/\[{2}[^\[]*\]{2}/", $text, $matches);
	 	
	 	// to though each of the matches and get the required string
	 	$workingText = $text;
	 	foreach ($matches[0] as $match) {
	 		if (preg_match("/\[{2}([^|]*)\]{2}/", $match, $parts)) {
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
	function addEventListener (&$eventListener) {
		ArgumentValidator::validate($eventListener, HasMethodsValidatorRule::getRule("handleEvent"), true);
		$this->_eventListeners[] =& $eventListener;
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
	function triggerEvent ($eventType, &$source, $context = null) {
		$list =& $this->_eventListeners;
		foreach (array_keys($list) as $key) {
			$list[$key]->handleEvent($eventType, $source, $context);
		}
	}
}

?>
