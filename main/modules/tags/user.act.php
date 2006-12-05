<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: user.act.php,v 1.3 2006/12/05 17:44:49 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/all.act.php");

/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: user.act.php,v 1.3 2006/12/05 17:44:49 adamfranco Exp $
 */
class userAction 
	extends allAction
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
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		$heading = dgettext("polyphony", "Tags for user: %1");
		
		$idManager =& Services::getService('Id');
		$agentManager =& Services::getService('Agent');
		$tagManager =& Services::getService("Tagging");
		
		if (RequestContext::value('agent_id'))
			$agentId =& $idManager->getId(RequestContext::value('agent_id'));
		else
			$agentId =& $tagManager->getCurrentUserId();
		
		if ($agentManager->isAgent($agentId)) {
			$agent =& $agentManager->getAgent($agentId);
			$heading = str_replace('%1', $agent->getDisplayName(), $heading);
		} else		
			$heading = str_replace('%1', $agentId->getIdString(), $heading);
		$harmoni->request->endNamespace();
		return $heading;
	}
	
	/**
	 * Answer the tags
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function &getTags () {
		$tagManager =& Services::getService("Tagging");
		$idManager =& Services::getService("Id");
		
		if (RequestContext::value('agent_id'))
			$agentId =& $idManager->getId(RequestContext::value('agent_id'));
		else
			$agentId =& $tagManager->getCurrentUserId();
		
		$tags =& $tagManager->getTagsByAgent($agentId, TAG_SORT_ALFA, $this->getNumTags());
// 		printpre($tags);
		return $tags;
	}
	
	/**
	 * Answer the action to use for viewing tags
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getViewAction () {
		return 'viewuser';
	}
}

?>