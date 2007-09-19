<?php
/**
 * @since 11/29/06
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlAction.class.php,v 1.4 2007/09/19 14:04:42 adamfranco Exp $
 */ 
 
require_once(POLYPHONY_DIR.'/main/library/AbstractActions/Action.class.php');

/**
 * A generic class for writing XML responses
 * 
 * @since 11/29/06
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlAction.class.php,v 1.4 2007/09/19 14:04:42 adamfranco Exp $
 */
class XmlAction
	extends Action
{

	/**
	 * Answer a unauthorized response
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	function getUnauthorizedMessage () {
		$this->error('Unauthorized');
	}
		
	/**
	 * Start the document
	 * 
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	function start () {
		if (isset($this->_started))
			return;
		
		$this->_started = true;
		header("Content-Type: text/xml; charset=utf-8");
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<response>
END;
	}
	
	/**
	 * End the document
	 * 
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	function end () {
		print "\n</response>";
		exit;
	}
	
	/**
	 * Respond with an error
	 * 
	 * @param string $message
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	function error ($message) {
		$this->start();
		print "\n\t<error><![CDATA[".str_replace(']]>', '}}>', $message)."]]></error>";
		$this->end();
	}
}

?>