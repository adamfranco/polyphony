<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelContinueButton.class.php,v 1.4 2006/08/02 23:47:46 sporktim Exp $
 */ 

/**
 * a button that does not persist data changes
 * 
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCancelContinueButton.class.php,v 1.4 2006/08/02 23:47:46 sporktim Exp $
 */
 
 require_once(POLYPHONY."/main/library/Wizard/Components/WCancelContinueLogic.class.php");
 
class WCancelContinueButton extends WLogicButton {
	
	/**
	 * constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function WCancelContinueButton () {
		$this->setLogicAndLabel(new WCancelContinueLogic(), 'Ignore Changes and Continue');
	}
	
	/**
	 * updates itself, if the user clicked then bounce to the next step now
	 * 
	 * @param string $fieldName
	 * @return boolean
	 * @access public
	 * @since 5/31/06
	 */
	function update  ($fieldName) {
		$val = RequestContext::value($fieldName);
		if ($val) {
			$this->_parent->nextStep($this);
			$this->_pressed = true;
		}
	}

}

?>