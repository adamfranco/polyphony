<?php
/**
 * @since 11/16/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ButtonPressedListener.class.php,v 1.2 2007/11/16 20:23:04 adamfranco Exp $
 */ 

/**
 * The Button-Pressed Listener will keep track of when a particular button was pressed.
 * Add a Button-Pressed Listener for each button and ensure that their event ids match
 * the event id given to this listener
 * 
 * @since 11/16/07
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ButtonPressedListener.class.php,v 1.2 2007/11/16 20:23:04 adamfranco Exp $
 */
class ButtonPressedListener
	extends WizardEventListener

{
		
	/**
	 * @var boolean $pressed;  
	 * @access private
	 * @since 11/16/07
	 */
	private $pressed = false;
	
	/**
	 * @var boolean $added;  
	 * @access private
	 * @since 11/16/07
	 */
	private $added = false;
	
	/**
	 * @var string $eventType;  
	 * @access private
	 * @since 11/16/07
	 */
	private $eventType = null;
	
	/**
	 * Constructor
	 * 
	 * @param string $eventType
	 * @return void
	 * @access public
	 * @since 11/16/07
	 */
	public function __construct ($eventType) {
		ArgumentValidator::validate($eventType, NonZeroLengthStringValidatorRule::getRule());
		
		$this->eventType = $eventType;
	}
	
	/**
	 * Sets this component's parent (some kind of {@link WizardComponentWithChildren} so that it can
	 * have access to its information, if needed.
	 * @param ref object $parent
	 * @access public
	 * @return void
	 */
	function setParent ($parent) {
		$this->_parent = $parent;
		
		$this->attemptAdding();
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
		$this->attemptAdding();
	}
	
	/**
	 * Attempts to add ourselves to the parent {@link Wizard} as an {@link EventListener}.
	 *
	 * @access private
	 * @return void
	 */
	private function attemptAdding () {
		if ($this->added) 
			return;
		
		$wz =$this->getWizard();
		if ($wz) {
			$wz->addEventListener($this);
			$this->added = true;
		}
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		return $this->wasPressed();
	}
	
	/**
	 * Answer true if the button was pressed
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/16/07
	 */
	public function wasPressed () {
		return $this->pressed;
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
		return '';
	}
	
	/**
	 * Handles an event triggered by an {@link EventTrigger}. The event type is passed in case this
	 * particular EventListener is handling more than one type of event.
	 * @param string $eventType
	 * @param ref object $source The source object of the event.
	 * @param array $context An array of contextual parameters - the content will be dependent on the thrown event.
	 * @access public
	 * @return void
	 */
	public function handleEvent ($eventType, $source, $context) {
		if (is_null($this->eventType))
			throw new Exception("Invalid listener configuration, no event type set.");
		
		if ($eventType == $this->eventType) {
			$this->pressed = true;
		}
	}
}

?>