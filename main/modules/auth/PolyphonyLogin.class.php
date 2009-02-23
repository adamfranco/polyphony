<?php
/**
 * @since 2/23/09
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This is a static class to provide access to login state information from client apps.
 * 
 * @since 2/23/09
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class PolyphonyLogin {
	
	/**
 	 * @var object  $instance;  
 	 * @access private
 	 * @since 10/10/07
 	 * @static
 	 */
 	private static $instance;

	/**
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the this class and it is accessed only via the 
	 * ClassName::instance() method.
	 *
	 * After access by a client, state will be destroyed at shutdown.
	 * 
	 * @return object 
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	public static function instance () {
		if (!isset(self::$instance))
			self::$instance = new PolyphonyLogin;
		
		return self::$instance;
	}
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access private
	 * @since 2/23/09
	 */
	private function __construct () {
	}
	
	/**
	 * Destructor, clean up our session vars.
	 * 
	 * @return void
	 * @access private
	 * @since 2/23/09
	 */
	function __destruct () {
		unset($_SESSION['polyphony/login_failed']);
	}
	
	/**
	 * Answer true if a login attempt has just failed.
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/23/09
	 */
	public function hasLoginFailed () {
		return (isset($_SESSION['polyphony/login_failed']) && $_SESSION['polyphony/login_failed']);
	}
	
	
	
}

?>