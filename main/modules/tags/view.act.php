<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.1.2.1 2006/11/08 20:45:55 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view.act.php,v 1.1.2.1 2006/11/08 20:45:55 adamfranco Exp $
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
		$harmoni =& Harmoni::instance();
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
		$actionRows =& $this->getActionRows();
// 		ob_start();
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		
		$items =& $this->getItems();
		$resultPrinter =& new IteratorResultPrinter($items, 1, 5, 
									"printItem", $this->getViewAction());
		$resultLayout =& $resultPrinter->getLayout("canViewItem");		
// 		$resultLayout =& $resultPrinter->getLayout();		
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
	function &getItems () {
// 		$tagManager =& Services::getService("Tagging");
		$tag =& new Tag(RequestContext::value('tag'));
		return $tag->getItems();
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
}

/**
 * Print out an Item
 * 
 * @param object $item
 * @return object GuiComponent
 * @access public
 * @since 11/8/06
 */
function &printItem ( &$item, $viewAction) {
	ob_start();
	
	print "\n\t<a href='".$item->getUrl()."'>";
	if ($item->getThumbnailUrl())
		print "\n\t\t<img src='".$item->getThumbnailUrl()."' style='border: 0px; float: right;' />";
	print "\n\t\t<strong>".$item->getDisplayName()."</strong>";
	print "\n\t</a>";
	print "\n\t<p>".$item->getDescription()."</p>";
	print "\n\t<p><strong>"._('Tags').":</strong> ";
	$tags =& $item->getTags();
	$harmoni =& Harmoni::instance();
	while ($tags->hasNext()) {
		$tag =& $tags->next();
		$parameters = array("tag" => $tag->getValue());
		if (RequestContext::value('agent_id'))
			$parameters['agent_id'] = RequestContext::value('agent_id');
		$url = $harmoni->request->quickURL('tags', $viewAction, $parameters);
		print "<a href='".$url."'>".$tag->getValue()."</a> ";
	}
	print "</p>";
	
	$component = & new Block(ob_get_clean(), STANDARD_BLOCK);
	return $component;
}

// Callback function for checking authorizations
function canViewItem( &$item ) {
	if ($item->getSystem() == ARBITRARY_URL)
		return true;
	
	$authZ =& Services::getService("AuthZ");
	$idManager =& Services::getService("Id");
	if ($authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.access"), $item->getId())
		|| $authZ->isUserAuthorized($idManager->getId("edu.middlebury.authorization.view"), $item->getId()))
	{
		return TRUE;
	} else {
		return FALSE;
	}
}

?>