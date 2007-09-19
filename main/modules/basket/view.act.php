<?php
/**
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.15 2007/09/19 14:04:54 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Basket/Basket.class.php");

require_once(HARMONI."GUIManager/StyleProperties/TextAlignSP.class.php");
require_once(HARMONI."GUIManager/StyleProperties/MinHeightSP.class.php");
require_once(HARMONI."/Primitives/Collections-Text/HtmlString.class.php");

/**
 * 
 * 
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.15 2007/09/19 14:04:54 adamfranco Exp $
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
		return _("View Your Selection");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =$this->getActionRows();
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("basket");
		
		$idManager = Services::getService("Id");
		$authZ = Services::getService("AuthZ");
		
		$basket = Basket::instance();
		$basket->clean();
		$basket->reset();

	
		//***********************************
		// Things to do with your basket
		//***********************************
		ob_start();
		print _("Basket").": ";
//      perhaps there will be enough links sometime for this function
//		BasketPrinter::printBasketFunctionLinks($harmoni, $basket);

		print "<a href=\"".$harmoni->request->quickURL("basket", "export").
			"\">"._("Export Selection(<em>Assets</em>)")."</a>";
		print " | ";
		print "<a href=\"".$harmoni->request->quickURL("basket", "empty").
			"\">"._("Empty Selection")."</a>";

		$layout = new Block(ob_get_contents(), STANDARD_BLOCK);
		ob_end_clean();
		$actionRows->add($layout, "100%", null, CENTER, CENTER);
		
		//***********************************
		// print the results
		//***********************************
		$resultPrinter = new IteratorResultPrinter($basket, 3, 6, "printAssetShort");
		$resultLayout =$resultPrinter->getLayout("viewAction::canView");
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		$harmoni->request->endNamespace();
	}
	
	// Callback function for checking authorizations
	function canView( $assetId ) {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");

		if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $assetId))
		{
			return TRUE;
		} else {
			$basket = Basket::instance();
			$basket->removeItem($assetId);
			return FALSE;
		}
	}
	
}

function printAssetShort($assetId, $num) {
	$harmoni = Harmoni::instance();
	$repositoryManager = Services::getService("Repository");
	$asset =$repositoryManager->getAsset($assetId);
	
	$container = new Container(new YLayout, BLOCK, EMPHASIZED_BLOCK);
	$fillContainerSC = new StyleCollection("*.fillcontainer", "fillcontainer", "Fill Container", "Elements with this style will fill their container.");
	$fillContainerSC->addSP(new MinHeightSP("88%"));
	$container->addStyle($fillContainerSC);
	
	$centered = new StyleCollection("*.centered", "centered", "Centered", "Centered Text");
	$centered->addSP(new TextAlignSP("center"));	
	
	$assetId =$asset->getId();
	
	if ($_SESSION["show_thumbnail"] == 'true') {
		$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($asset);
		if ($thumbnailURL !== FALSE) {
			$xmlStart = $num - 1;
			
			$thumbSize = $_SESSION["thumbnail_size"]."px";
	
			ob_start();
			print "\n<div style='height: $thumbSize; width: $thumbSize; margin: auto;'>";
			print "\n\t<a style='cursor: pointer;'";
			print " onclick='Javascript:window.open(";
			print '"'.VIEWER_URL."?&amp;source=";
			print urlencode($harmoni->request->quickURL("basket", "browse_xml"));
			print '&amp;start='.$xmlStart.'", ';
			print '"_blank", ';
			print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
			print ")'>";
			print "\n\t\t<img src='$thumbnailURL' class='thumbnail thumbnail_image' alt='Thumbnail Image' style='max-height: $thumbSize; max-width: $thumbSize;' />";
			print "\n\t</a>";
			print "\n</div>";
			$component = new UnstyledBlock(ob_get_contents());
			$component->addStyle($centered);
			ob_end_clean();
			$container->add($component, "100%", null, CENTER, CENTER);
		}
	}
	
	ob_start();
	if ($_SESSION["show_displayName"] == 'true')
		print "\n\t<div style='font-weight: bold; height: 50px; overflow: auto;'>".htmlspecialchars($asset->getDisplayName())."</div>";
	if ($_SESSION["show_id"] == 'true')
		print "\n\t<div>"._("ID#").": ".$assetId->getIdString()."</div>";
	if ($_SESSION["show_description"] == 'true') {
		$description = HtmlString::withValue($asset->getDescription());
		$description->trim(25);
		print  "\n\t<div style='font-size: smaller; height: 50px; overflow: auto;'>".$description->asString()."</div>";	
	}
	
	$component = new UnstyledBlock(ob_get_contents());
	ob_end_clean();
	$container->add($component, "100%", null, LEFT, TOP);
	
	
	ob_start();
	print "\n<a href='";
	print $harmoni->request->quickURL("basket", "remove", array('asset_id' => $assetId->getIdString()));
	print "' title='"._("Remove from Selection")."'>";
	print _('remove');
	print "</a>";
	
	print "\n | <a href='";
	print $harmoni->request->quickURL("basket", "up", array('asset_id' => $assetId->getIdString()));
	print "' title='". _('move up')."'>";
// 	print "<img src='".POLYPHONY_PATH."/icons/basket/arrowleft.png' width='25px' border='0' alt='"._("Move Up")."' />";
	print "&lt;--";
	print "</a>";
	
	print "\n | <a href='";
	print $harmoni->request->quickURL("basket", "down", array('asset_id' => $assetId->getIdString()));
	print "' title='". _('move down')."'>";
// 	print "<img src='".POLYPHONY_PATH."/icons/basket/arrowright.png' width='25px' border='0'  alt='"._("Move Down")."'  />";
	print "--&gt;";
	print "</a>";
	
	$container->add(new UnstyledBlock(ob_get_clean()), "100%", null, RIGHT, BOTTOM);
	
	return $container;
}