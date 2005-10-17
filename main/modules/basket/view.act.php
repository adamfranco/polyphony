<?php
/**
 * @package concerto.modules.exhibitions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.4 2005/10/17 18:21:30 adamfranco Exp $
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
 * @version $Id: view.act.php,v 1.4 2005/10/17 18:21:30 adamfranco Exp $
 */
class viewAction 
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
		return _("View Your Basket");
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
		$basket->reset();
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter =& new IteratorResultPrinter($basket, 3, 6, "printAssetShort", $harmoni);
		$resultLayout =& $resultPrinter->getLayout($harmoni, "viewAction::canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		$harmoni->request->endNamespace();
	}
	
	// Callback function for checking authorizations
	function canView( & $assetId ) {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $assetId)
			|| $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $assetId))
		{
			return TRUE;
		} else {
			return FALSE;
		}
	}
}

// Callback function for printing Assets
function printAssetShort(&$assetId) {
	$harmoni =& Harmoni::instance();
	$repositoryManager =& Services::getService("Repository");
	ob_start();
	
	$asset =& $repositoryManager->getAsset($assetId);
	$repository =& $asset->getRepository();
	$repositoryId =& $repository->getId();
	
	$harmoni->request->endNamespace();
	$assetViewUrl = $harmoni->request->quickURL("asset", "view", array('asset_id' => $assetId->getIdString(), 'collection_id' => $repositoryId->getIdString()));
	$harmoni->request->startNamespace("basket");
	
	
	print  "\n\t<strong>".$asset->getDisplayName()."</strong> - "._("ID#").": ";
	print "<a href='".$assetViewUrl."'>".$assetId->getIdString()."</a>";
	print  "\n\t<br /><em>".$asset->getDescription()."</em>";	
	print  "\n\t<br />";
	
// 	ExhibitionPrinter::printFunctionLinks($asset);
	
	$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($assetId);
	if ($thumbnailURL !== FALSE) {
		
		print "\n\t<br /><a href='".$assetViewUrl."'>";
		print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' />";
		print "\n\t</a>";
	}
	
	$xLayout =& new XLayout();
	$layout =& new Container($xLayout, BLOCK, 4);
	$assetBlock =& new Block(ob_get_contents(), 3);
	$layout->add($assetBlock, null, null, CENTER, CENTER);
	ob_end_clean();
	
	ob_start();
	print "\n<a href='";
	print $harmoni->request->quickURL("basket", "remove", array('asset_id' => $assetId->getIdString()));
	print "' title='". _('remove')."'>";
	print "<img src='".POLYPHONY_PATH."/main/library/Basket/icons/basketminus.png' width='40px' border='0' alt='"._("Remove from Basket")."' />";
	print "</a>";
	
	print "\n<br/><a href='";
	print $harmoni->request->quickURL("basket", "up", array('asset_id' => $assetId->getIdString()));
	print "' title='". _('move up')."'>";
	print "<img src='".POLYPHONY_PATH."/main/library/Basket/icons/arrowleft.png' width='25px' border='0' alt='"._("Move Up")."' />";
	print "</a>";
	
	print "\n<br/><a href='";
	print $harmoni->request->quickURL("basket", "down", array('asset_id' => $assetId->getIdString()));
	print "' title='". _('move down')."'>";
	print "<img src='".POLYPHONY_PATH."/main/library/Basket/icons/arrowright.png' width='25px' border='0'  alt='"._("Move Down")."'  />";
	print "</a>";
	
	$functionsBlock =& new Block(ob_get_contents(), 3);
	$layout->add($functionsBlock, null, null, CENTER, TOP);
	ob_end_clean();
	
	return $layout;
}