<?php
/**
 * @since 8/4/06
 * @package polyphony.library.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ForceAuthAction.class.php,v 1.3 2007/09/04 20:27:57 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");


/**
 * The ForceAuthAction forces token collection via HTTP Authentication to allow
 * authentication outside of the context of a browser Harmoni-Application 
 * environment. For instance, this can be used to authenticate an RSS reader for
 * an RSS feed, or to prompt for authentication for a file that is directly linked
 * from another website.
 * 
 * @since 8/4/06
 * @package polyphony.library.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ForceAuthAction.class.php,v 1.3 2007/09/04 20:27:57 adamfranco Exp $
 */
class ForceAuthAction 
	extends Action
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {		
		if ($this->isExecutionAuthorized()) {
			return true;	
		}
		
		// if we aren't authorized, check if we are authenticated
		// and try to re-authorize after that.
		if (!$this->isAuthenticated()) {
			$this->authenticate();
			if ($this->isAuthenticated())
				return $this->isExecutionAuthorized();
		} 
		
		return false;
	}
	
	/**
	 * Loop through the authentication types and see if the user is authenticated.
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/4/06
	 */
	function isAuthenticated () {
		$isAuthenticated = FALSE;
		$authN = Services::getService("AuthN");
		
		// authenticate.
		$authTypes =$authN->getAuthenticationTypes();
		while ($authTypes->hasNext()) {
			$authType =$authTypes->next();
						
			// If they are authenticated, quit
			if ($authN->isUserAuthenticated($authType)) {
				return TRUE;
			}
		}
		
		return FALSE;
	}
	
	/**
	 * Loop through the authentication types and try to authenticate the user.
	 * 
	 * @return void
	 * @access public
	 * @since 8/4/06
	 */
	function authenticate () {
		$authN = Services::getService("AuthN");
		
		// Reconfigure the AuthNManager to use HTTP Auth rather than forms
		// :: Start the AuthenticationManager OSID Impl.
		$configuration = new ConfigurationProperties;
		$tokenCollectors = array();
		$authNTypes =$authN->getAuthenticationTypes();
		while ($authNTypes->hasNext()) {
			$tokenCollectors[serialize($authNTypes->next())] = 
				new HTTPAuthNamePassTokenCollector($this->getRelm(),
					$this->getCancelFunction());
		}
		$configuration->addProperty('token_collectors', $tokenCollectors);
		$authN->assignConfiguration($configuration);
		
		
		// Authenticate with HTTP Authentication.
		$harmoni = Harmoni::instance();
		$isAuthenticated = FALSE;
		$authTypes =$authN->getAuthenticationTypes();
		while ($authTypes->hasNext() && !$isAuthenticated) {
			$authType =$authTypes->next();
			
			// Try authenticating with this type
			$authN->authenticateUser($authType);
			
			// If they are authenticated, quit
			if ($authN->isUserAuthenticated($authType)) {
				$isAuthenticated = TRUE;
			}
		}
	}
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 8/4/06
	 */
	function isExecutionAuthorized () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
	
	/**
	 * Answer the HTTP Authentication 'Relm' to present to the user for authentication.
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 8/7/06
	 */
	function getRelm () {
		return null; // Override for custom relm.
	}
	
	/**
	 * Answer the cancel function for this action, to use if the user hits
	 * the 'cancel' button in the http authentication dialog.
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 8/7/06
	 */
	function getCancelFunction () {
		return null; // Override for custom functions.
	}
}

?>