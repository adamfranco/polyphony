<?php
/**
 * @since 11/7/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.8 2007/09/19 14:04:58 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(dirname(__FILE__)."/TagAction.abstract.php");


/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.8 2007/09/19 14:04:58 adamfranco Exp $
 */
class viewAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/07/06
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 11/07/06
	 */
	function getHeadingText () {
		$heading = dgettext("polyphony", "Items Tagged with '%1'");
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		$heading = str_replace('%1', RequestContext::value('tag'), $heading);
		$harmoni->request->endNamespace();
		return $heading;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/07/06
	 */
	function buildContent () {
		$defaultTextDomain = textdomain("polyphony");
		$actionRows =$this->getActionRows();
// 		ob_start();
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		
		$actionRows->add(new Block(TagAction::getTagMenu(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		// Related Tags
		ob_start();
		print "<h3 style='margin-top: 0px; margin-bottom: 0px;'>"._("Related Tags:")."</h3>";
		$tag =$this->getTag();
		print TagAction::getTagCloudDiv($tag->getRelatedTags(TAG_SORT_FREQ), 'view', 100);
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		$items =$this->getItems();
		$resultPrinter = new IteratorResultPrinter($items, 1, 5, 
									'getTaggedItemComponent', $this->getViewAction());
		$resultLayout =$resultPrinter->getLayout("canViewItem");		
// 		$resultLayout =$resultPrinter->getLayout();		
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
// 		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		$harmoni->request->endNamespace();
		textdomain($defaultTextDomain);
	}
	
	/**
	 * Answer the tags
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getItems () {
		$tag =$this->getTag();
		return $tag->getItems();
	}
	
	/**
	 * Answer this action's Tag
	 * 
	 * @return object Tag
	 * @access public
	 * @since 12/8/06
	 */
	function getTag () {
		if (!isset($this->_tag)) {
			$this->_tag = new Tag(RequestContext::value('tag'));
		}
		return $this->_tag;
	}
	
	/**
	 * Answer the action to use for viewing tags
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getViewAction () {
		return 'view';
	}
	
	/**
	 * Answer the item-printing callback function
	 * 
	 * @return string
	 * @access public
	 * @since 11/14/06
	 */
	function getItemPrintingCallback () {
		
	}
}

/**
 * Answer the tagged item GUI component
 * 
 * @param <##>
 * @return <##>
 * @access public
 * @since 11/14/06
 */
function getTaggedItemComponent ( $item, $viewAction) {
	if (defined('POLYPHONY_TAGGEDITEM_PRINTING_CALLBACK'))
		$printFunction = POLYPHONY_TAGGEDITEM_PRINTING_CALLBACK;
	else
		$printFunction = 'printTaggedItem';
	
	ob_start();
	
	$printFunction($item, $viewAction);
	
	$component =  new Block(ob_get_clean(), EMPHASIZED_BLOCK);
	return $component;
}

/**
 * Print out an Item
 * 
 * @param object $item
 * @param string $viewAction The action to choose when clicking on a tag.
 * @return string
 * @access public
 * @since 11/8/06
 */
function printTaggedItem ( $item, $viewAction) {	
	print "\n\t<a href='".$item->getUrl()."'>";
	if ($item->getThumbnailUrl())
		print "\n\t\t<img src='".$item->getThumbnailUrl()."' style=' float: right;' class='thumbnail_image' />";
	if ($item->getDisplayName())
		print "\n\t\t<strong>".$item->getDisplayName()."</strong>";
	else
		print "\n\t\t<strong>"._('untitled')."</strong>";
	print "\n\t</a>";
	print "\n\t<p>".$item->getDescription()."</p>";
	
	// Tags
	print "\n\t<p style='text-align: justify;'>";
	print "\n\t<strong>"._('Tags').":</strong> ";
	print TagAction::getTagCloudForItem($item, $viewAction,
			array(	'font-size: 90%;',
					'font-size: 100%;',
					'font-size: 110%;',
			));
	print "\n\t</p>";
	
	print "</p>";
	print "\n\t<p><strong>"._('System').":</strong> ";
	if ($item->getSystem() == ARBITRARY_URL)
		print _("The Internet");
	else
		print ucFirst($item->getSystem());
	print "</p>";
}

// Callback function for checking authorizations
function canViewItem( $item ) {
	if ($item->getSystem() == ARBITRARY_URL)
		return true;
	
	$authZ = Services::getService("AuthZ");
	$idManager = Services::getService("Id");
	if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $item->getId())
		|| $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $item->getId()))
	{
		return TRUE;
	} else {
		return FALSE;
	}
}

?>