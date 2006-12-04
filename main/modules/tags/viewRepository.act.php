<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewRepository.act.php,v 1.3 2006/12/04 21:08:48 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(dirname(__FILE__)."/TagAction.abstract.php");
require_once(dirname(__FILE__)."/view.act.php");



/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewRepository.act.php,v 1.3 2006/12/04 21:08:48 adamfranco Exp $
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
		
		$harmoni->request->passthrough('collection_id');
		$harmoni->request->startNamespace("polyphony-tags");
		$harmoni->request->passthrough('repository_id');
		$harmoni->request->passthrough('system');
		
		$actionRows->add(new Block(TagAction::getTagMenu(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		$items =& $this->getItems();
		$resultPrinter =& new IteratorResultPrinter($items, 1, 5, 
									'getTaggedItemComponent', $this->getViewAction());
		$resultLayout =& $resultPrinter->getLayout("canViewItem");		
// 		$resultLayout =& $resultPrinter->getLayout();		
		$actionRows->add($resultLayout, "100%", null, LEFT, CENTER);
		
// 		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		$harmoni->request->forget('repository_id');
		$harmoni->request->forget('system');
		$harmoni->request->endNamespace();
		textdomain($defaultTextDomain);
		$harmoni->request->passthrough('collection_id');
		
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
		return 'viewRepository';
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

?>