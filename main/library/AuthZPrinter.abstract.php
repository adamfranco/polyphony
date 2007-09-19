<?php
/**
 * @since 11/29/06
 * @package polyphony.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: AuthZPrinter.abstract.php,v 1.4 2007/09/19 14:04:40 adamfranco Exp $
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
 * @version $Id: AuthZPrinter.abstract.php,v 1.4 2007/09/19 14:04:40 adamfranco Exp $
 */
class AuthZPrinter {
		
	/**
	 * Answer the html string for an icon that displays authorization state
	 * 
	 * @param object Id $qualifierId
	 * @return string
	 * @access public
	 * @since 11/29/06
	 */
	function getAZIcon ($qualifierId) {
		ob_start();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");
		
		if ($authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view_authorizations"), 
				$qualifierId))
		{
			$onclick = "onclick=\"AuthZViewer.run('".addslashes($qualifierId->getIdString())."', this);\" style='cursor: pointer;' ";
		} else {
			$onclick = '';
		}
	
		if ($authZ->isAuthorized(
				$idManager->getId("edu.middlebury.agents.everyone"),
				$idManager->getId("edu.middlebury.authorization.view"), 
				$qualifierId))
		{
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