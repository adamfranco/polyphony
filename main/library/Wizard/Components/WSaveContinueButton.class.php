<?php
/**
 * @since 5/31/06
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WSaveContinueButton.class.php,v 1.3 2006/07/14 19:40:19 sporktim Exp $
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
 * @version $Id: WSaveContinueButton.class.php,v 1.3 2006/07/14 19:40:19 sporktim Exp $
 */
 
 require_once(POLYPHONY."/main/library/Wizard/Components/WLogicButton.class.php");
 require_once(POLYPHONY."/main/library/Wizard/Components/WSaveContinueLogic.class.php");

 
class WSaveContinueButton extends WLogicButton {
	
	/**
	 * constructor
	 * 
	 * @return void
	 * @access public
	 * @since 5/31/06
	 */
	function WSaveContinueButton () {
		parent::withLogicAndLabel(new WSaveContinueLogic(), 'Save Changes and Continue');
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
		}
	}
	
}

?>