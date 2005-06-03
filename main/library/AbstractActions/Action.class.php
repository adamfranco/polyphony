<?php
/**
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Action.class.php,v 1.1 2005/06/03 15:22:28 adamfranco Exp $
 */ 

/**
 * This class is the most simple abstraction of an action. It provides a structure
 * for common methods
 * 
 * @package concerto.modules
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Action.class.php,v 1.1 2005/06/03 15:22:28 adamfranco Exp $
 * @since 4/28/05
 */
class Action {
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		throwError(new Error(__CLASS__."::".__FUNCTION__."() must be overridded in child classes."));
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return '';
	}
	
	/**
	 * Answer the requested module, maybe other than this action's module if this
	 * action was chained onto another's request.
	 * 
	 * @return string
	 * @access public
	 * @since 6/3/05
	 */
	function requestedModule () {
		$harmoni =& Harmoni::instance();
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		return $module;
	}
	
	/**
	 * Answer the requested action, maybe other than this action's action if this
	 * action was chained onto another's request.
	 * 
	 * @return string
	 * @access public
	 * @since 6/3/05
	 */
	function requestedAction () {
		$harmoni =& Harmoni::instance();
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		return $action;
	}
}

?>