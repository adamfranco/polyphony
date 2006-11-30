<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewRepository.act.php,v 1.2 2006/11/30 22:02:47 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(dirname(__FILE__)."/TagAction.abstract.php");


/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewRepository.act.php,v 1.2 2006/11/30 22:02:47 adamfranco Exp $
 */
class viewRepositoryAction 
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
		$heading = dgettext("polyphony", "Items Tagged with '%1' in %2");
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		$heading = str_replace('%1', RequestContext::value('tag'), $heading);
		$repository =& $this->getRepository();
		$heading = str_replace('%2', $repository->getDisplayName(), $heading);
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
		
		$actionRows->add(new Block(TagAction::getTagMenu(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		$items =& $this->getItems();
		$resultPrinter =& new IteratorResultPrinter($items, 1, 5, 
									'getTaggedItemComponent', $this->getViewAction());
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
		$repository =& $this->getRepository();
		
		$ids = array();
		$assets =& $repository->getAssets();
		while($assets->hasNext()) {
			$asset =& $assets->next();
			$ids[] =& $asset->getId();
		}
		
		$tag =& new Tag(RequestContext::value('tag'));
		return $tag->getItemsWithIdsInSystem(new HarmoniIterator($ids), RequestContext::value('system'));
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
	 * Answer the repository
	 * 
	 * @return object Repository
	 * @access public
	 * @since 11/14/06
	 */
	function &getRepository () {
		if (!isset($this->_repository)) {
			$repositoryManager =& Services::getService('Repository');
			$idManager =& Services::getService('Id');
			
			$this->_repositoryId =& $idManager->getId(RequestContext::value('repository_id'));
			$this->_repository =& $repositoryManager->getRepository($this->_repositoryId);
		}
		return $this->_repository;
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
function getTaggedItemComponent ( &$item, $viewAction) {
	if (defined('POLYPHONY_TAGGEDITEM_PRINTING_CALLBACK'))
		$printFunction = POLYPHONY_TAGGEDITEM_PRINTING_CALLBACK;
	else
		$printFunction = 'printTaggedItem';
	
	ob_start();
	
	$printFunction($item, $viewAction);
	
	$component = & new Block(ob_get_clean(), EMPHASIZED_BLOCK);
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
function printTaggedItem ( &$item, $viewAction) {	
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
	print "\n\t<p><strong>"._('System').":</strong> ";
	if ($item->getSystem() == ARBITRARY_URL)
		print _("The Internet");
	else
		print ucFirst($item->getSystem());
	print "</p>";
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