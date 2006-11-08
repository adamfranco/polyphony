<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewuser.act.php,v 1.1.2.1 2006/11/08 20:45:55 adamfranco Exp $
 */ 

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
 * @version $Id: viewuser.act.php,v 1.1.2.1 2006/11/08 20:45:55 adamfranco Exp $
 */
class viewuserAction 
	extends viewAction
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
		$heading = dgettext("polyphony", "Items tagged with '%1' by %2");
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		$heading = str_replace('%1', RequestContext::value('tag'), $heading);

		$idManager =& Services::getService('Id');
		$agentManager =& Services::getService('Agent');
		if ($agentManager->isAgent($idManager->getId(RequestContext::value('agent_id')))) {
			$agent =& $agentManager->getAgent(
						$idManager->getId(RequestContext::value('agent_id')));
			$heading = str_replace('%2', $agent->getDisplayName(), $heading);
		} else
			$heading = str_replace('%2', RequestContext::value('agent_id'), $heading);

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
	function &getItems () {
// 		$tagManager =& Services::getService("Tagging");
		$tag =& new Tag(RequestContext::value('tag'));
		$idManager =& Services::getService('Id');
		return $tag->getItemsForAgent($idManager->getId(RequestContext::value('agent_id')));
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