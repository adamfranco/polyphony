<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueButton.class.php,v 1.7 2007/09/04 20:28:08 adamfranco Exp $
 */ 

/**
 * a button that persists data changes
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueButton.class.php,v 1.7 2007/09/04 20:28:08 adamfranco Exp $
 */
 
require_once(POLYPHONY."/main/library/Wizard/Components/WEventButton.class.php");

 
class WSaveContinueButton extends WEventButton {
	
	
	
	
	var $_stepContainer;
	
	
	/**
	 * constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function WSaveContinueButton ($stepContainer) {		
		$this->setEventAndLabel("edu.middlebury.polyphony.wizard.save",'Save Changes and Continue');
		$this->_stepContainer =$stepContainer;
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
		parent::update($fieldName);
		if ($this->getAllValues()) {
			$this->_stepContainer->nextStep();
		}
	}
	
	
	/**
	 * Answers true if this component will be enabled.
	 * @access public
	 * @return boolean
	 */
	function isEnabled () {
		return $this->_stepContainer->hasNext();
	}
	
}

?>