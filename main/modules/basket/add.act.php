<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add.act.php,v 1.1 2005/08/05 21:38:29 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Basket/BasketManager.class.php");

/**
 * 
 * 
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: add.act.php,v 1.1 2005/08/05 21:38:29 adamfranco Exp $
 */
class addAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		// Check that the user can access this collection
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Add an Item to Your Basket");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("basket");
		
		$idManager =& Services::getService("Id");
		$authZ =& Services::getService("AuthZ");
		
		$basket =& BasketManager::getBasket();
		$viewAZ =& $idManager->getId("edu.middlebury.authorization.view");
		$assetId =& $idManager->getId(RequestContext::value("asset_id"));
		if ($authZ->isUserAuthorized(
			$viewAZ, 
			$assetId)) 
		{
			$basket->addItem($assetId);
		}
		$harmoni->request->endNamespace();
		$harmoni->history->goBack("polyphony/basket");
	}
}
