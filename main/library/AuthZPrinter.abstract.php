<?php
/**
 * @since 11/29/06
 * @package polyphony.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AuthZPrinter.abstract.php,v 1.6 2007/10/10 22:58:41 adamfranco Exp $
 */ 

/**
 * A static class for printing Authorization information
 * 
 * @since 11/29/06
 * @package polyphony.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AuthZPrinter.abstract.php,v 1.6 2007/10/10 22:58:41 adamfranco Exp $
 */
abstract class AuthZPrinter {
		
	/**
	 * Answer the html string for an icon that displays authorization state
	 * 
	 * @param object Id $qualifierId
	 * @return string
	 * @access public
	 * @static
	 * @since 11/29/06
	 */
	static function getAZIcon ($qualifierId) {
		ob_start();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		try {
			$isAuthorized = $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
				$qualifierId);
		} catch (UnknownIdException $e) {
			$isAuthorized = $authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
				$idManager->getId("edu.middlebury.authorization.root"));
		}
		if ($isAuthorized) {
			$onclick = "onclick=\"AuthZViewer.run('".addslashes($qualifierId->getIdString())."', this);\" style='cursor: pointer;' ";
		} else {
			$onclick = '';
		}
		
		try {
			$isPublicAuthorized = $authZ->isAuthorized(
				$idManager->getId("edu.middlebury.agents.everyone"),
				$idManager->getId("edu.middlebury.authorization.view"), 
				$qualifierId);
		} catch (UnknownIdException $e) {
			$isPublicAuthorized = true;
		}
		if ($isPublicAuthorized) {
			print "\n<img src='".POLYPHONY_PATH."/icons/view_public.gif' alt='".("Public-Viewable")."' title='".("Public-Viewable")."' ".$onclick."/> ";
		} else if ($authZ->isAuthorized(
				$idManager->getId("edu.middlebury.agents.users"),
				$idManager->getId("edu.middlebury.authorization.view"), 
				$qualifierId))
		{
			print "\n<img src='".POLYPHONY_PATH."/icons/view_institute.gif' alt='".("Institution-Viewable")."' title='".("Institution-Viewable")."' ".$onclick."/> ";
		} else {
			print "\n<img src='".POLYPHONY_PATH."/icons/view_limited.gif' alt='".("Viewable by some people")."' title='".("Viewable by some people")."' ".$onclick."/> ";
		}
		
		return ob_get_clean();
	}
	
}

?>