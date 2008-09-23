<?php
/**
 * @since 09/23/08
 * @package polyphony.userdata
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/XmlAction.class.php');

/**
 * Set a user preference value.
 * 
 * @since 09/23/08
 * @package polyphony.userdata
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class setPreferenceAction
	extends XmlAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 09/23/08
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		return TRUE;
	}
	
	/**
	 * Execute
	 * 
	 * @return void
	 * @access public
	 * @since 09/23/08
	 */
	public function execute () {
		
		try {
			$this->start();
			$userData = UserData::instance();
			$userData->setPreference(
				RequestContext::value('key'),
				RequestContext::value('val'));
			
			print "\n\t<preference key=\"".RequestContext::value('key').'">';
			print $userData->getPreference(RequestContext::value('key'));
			print '</preference>';
			$this->end();
		} catch (Exception $e) {
			HarmoniErrorHandler::logException($e);
			$this->error($e->getMessage(), get_class($e));
		}
		
	}
}

?>