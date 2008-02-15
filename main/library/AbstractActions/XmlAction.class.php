<?php
/**
 * @since 11/29/06
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: XmlAction.class.php,v 1.7 2008/02/15 16:47:31 adamfranco Exp $
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
 * @version $Id: XmlAction.class.php,v 1.7 2008/02/15 16:47:31 adamfranco Exp $
 */
abstract class XmlAction
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
	 * @param optional string $type Use this to pass the exception class if desired.
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	function error ($message, $type = 'Error') {
		$this->start();
		print "\n\t<error type='$type'><![CDATA[".str_replace(']]>', '}}>', $message)."]]></error>";
		$this->end();
	}
	
	/**
	 * Respond with a non-fatal error
	 * 
	 * @param string $message
	 * @param optional string $type Use this to pass the exception class if desired.
	 * @return void
	 * @access public
	 * @since 9/25/07
	 */
	public function nonFatalError ($message, $type = 'Error') {
		$this->start();
		print "\n\t<error type='$type'><![CDATA[".str_replace(']]>', '}}>', $message)."]]></error>";
	}
}

?>