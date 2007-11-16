<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCallbackButton.class.php,v 1.3 2007/11/16 18:39:39 adamfranco Exp $
 */ 

/**
 * This class simply evaluates its event as a callback function when pressed.
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WCallbackButton.class.php,v 1.3 2007/11/16 18:39:39 adamfranco Exp $
 */
class WCallbackButton 
	extends WEventButton
{
	
	
	
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
		$val = RequestContext::value($fieldName);
		if ($val) {
			$events = $this->getEvents();
			if (count($events) !== 1)
				throw new Exception("WCallbackButton must have one and only one event.");
				
			return eval($events[0]);
		}
		return true;
	}
	
	
	
}

?>