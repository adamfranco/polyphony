<?php
/**
 * @since 6/19/08
 * @package polyphony.repository
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/ConditionalGetAction.abstract.php");
require_once(dirname(__FILE__)."/viewthumbnail.act.php");

/**
 * This Action will return a thumbnail if the user is authorized, but will fail and
 * not prompt if they are not authorized
 * 
 * @since 6/19/08
 * @package polyphony.repository
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class viewthumbnail_flashAction 
	extends ConditionalGetAction
{
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 6/19/08
	 */
	public function __construct () {
		$this->surrogate = new viewthumbnailAction;
	}
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 6/19/08
	 */
	function isAuthorizedToExecute () {
		return $this->surrogate->isExecutionAuthorized();
	}
	
	/**
	 * Return a junk image that says you can't view the file
	 *
	 * @since 6/19/08
	 */
	function getUnauthorizedMessage() {
		header("Content-Type: image/gif");
		header('Content-Disposition: filename="english.gif"');
			
		print file_get_contents(POLYPHONY.'/docs/images/unauthorized/english.gif');
		exit;
	}
	
	/**
	 * Output the content
	 * 
	 * @return null
	 * @access public
	 * @since 6/19/08
	 */
	public function outputContent () {
		$this->surrogate->outputContent();
	}
	
	/**
	 * Answer the last-modified timestamp for this action/id.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 6/19/08
	 */
	public function getModifiedDateAndTime () {
		return $this->surrogate->getModifiedDateAndTime();
	}
}

?>