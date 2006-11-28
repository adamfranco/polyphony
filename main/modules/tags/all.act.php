<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: all.act.php,v 1.1.2.4 2006/11/28 21:59:09 adamfranco Exp $
 */ 

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
 * @version $Id: all.act.php,v 1.1.2.4 2006/11/28 21:59:09 adamfranco Exp $
 */
class allAction 
	extends TagAction
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
		return dgettext("polyphony", "Browse Tags");
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
		$tags =& $tagManager->getTags(TAG_SORT_ALFA, 100);
// 		printpre($tags);
		return $tags;
	}
	
// 	/**
// 	 * This just adds an agent list for debugging purposes.
// 	 * 
// 	 * @return void
// 	 * @access public
// 	 * @since 11/07/06
// 	 */
// 	function buildContent () {
// 		parent::buildContent();
// 		
// 		$defaultTextDomain = textdomain("polyphony");
// 		$actionRows =& $this->getActionRows();
// 		ob_start();
// 		$harmoni =& Harmoni::instance();
// 		$harmoni->request->startNamespace("polyphony-tags");
// 		
// 		print "\n\t<h3>Debug: Top Agents</h3>";
// 		$tagManager =& Services::getService("Tagging");
// 		$agentManager =& Services::getService('Agent');
// 		$agentIds =& $tagManager->getAgentIds();
// 		$i=0;
// 		while ($agentIds->hasNext() && $i < 10) {
// 			$agentId =& $agentIds->next();
// 			
// 			if ($agentManager->isAgent($agentId)) {
// 				$agent =& $agentManager->getAgent($agentId);
// 				$name = $agent->getDisplayName();
// 			} else
// 				$name = $agentId->getIdString();
// 			
// 			$url = $harmoni->request->quickUrl('tags', 'user', 
// 				array('agent_id' => $agentId->getIdString()));
// 			print "\n\t<a href='".$url."'>".$name."</a> ";
// 		}
// 		
// 		
// 		$actionRows->add(new Block(ob_get_clean(), HIGHLIT_BLOCK), "100%", null, LEFT, TOP);
// 		$harmoni->request->endNamespace();
// 		textdomain($defaultTextDomain);
// 	}
	
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

?>