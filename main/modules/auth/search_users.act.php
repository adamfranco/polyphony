<?php
/**
 * @since 7/21/05
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout_type.act.php,v 1.11 2007/10/16 21:13:15 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * Change the language to the one specified by the user
 * 
 * @since 7/21/05
 * @package polyphony.language
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout_type.act.php,v 1.11 2007/10/16 21:13:15 adamfranco Exp $
 */
class search_usersAction
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
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		return $authZ->isUserAuthorized(
			$idManager->getId('edu.middlebury.authorization.change_user'),
			$idManager->getId('edu.middlebury.authorization.root'));
	}
	
	/**
	 * Execute this action.
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute () {
		if (!$this->isAuthorizedToExecute()) {
			throw new PermissionDeniedException();
		}
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("harmoni-authentication");
		$query = RequestContext::value("user");
		$harmoni->request->endNamespace();
		
		header('Content-type: text/plain');
		
		if (!strlen($query))
			exit;
		
		$authNMethodManager = Services::getService("AuthNMethods");
		$types = $authNMethodManager->getAuthNTypes();
		while ($types->hasNext()) {
			$type = $types->next();
			$method = $authNMethodManager->getAuthNMethodForType($type);
			$foundTokens = $method->getTokensBySearch($query);
			while ($foundTokens->hasNext()) {
				$tokens = $foundTokens->next();
				print $method->getDisplayNameForTokens($tokens)." (".$type->getKeyword().")|type=\"".$type->asString()."\" id=\"".$tokens->getIdentifier()."\"";
			}
		}
		exit;
	}
}