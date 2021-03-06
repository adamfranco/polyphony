<?php
/**
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Action.class.php,v 1.10 2007/10/10 22:58:42 adamfranco Exp $
 */ 

/**
 * This class is the most simple abstraction of an action. It provides a structure
 * for common methods
 * 
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Action.class.php,v 1.10 2007/10/10 22:58:42 adamfranco Exp $
 * @since 4/28/05
 */
abstract class Action {
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	abstract function isAuthorizedToExecute () ;
	
	/**
	 * Answer the message to print when the user is not authorized to execute
	 * this action
	 * 
	 * @return string
	 * @access public
	 * @since 7/18/05
	 */
	function getUnauthorizedMessage () {
		
		// Default implementation. Override as necessary.
		
		$harmoni = Harmoni::instance();
		$message = _("You are not authorized to ");
		if ($this->getHeadingText() == '') {
			$message .= _("execute this action, ");
			$message .= $harmoni->getCurrentAction();
		} else {
			$message .= $this->getHeadingText();
		}
		$message .= _(".");
		return $message;
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
	 * Return the title-text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 2/9/09
	 */
	function getTitleText () {
		return $this->getHeadingText();
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	abstract function execute () ;
	
	/**
	 * Answer the requested module, maybe other than this action's module if this
	 * action was chained onto another's request.
	 * 
	 * @return string
	 * @access public
	 * @since 6/3/05
	 */
	function requestedModule () {
		$harmoni = Harmoni::instance();
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
		$harmoni = Harmoni::instance();
		list($module, $action) = explode(".", $harmoni->request->getRequestedModuleAction());
		return $action;
	}
}

?>