<?php
/**
 * @since 11/10/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: getAllUserTags.act.php,v 1.2 2006/11/30 22:02:46 adamfranco Exp $
 */ 
 
require_once(dirname(__FILE__)."/TagXmlAction.abstract.php");

/**
 * <##>
 * 
 * @since 11/10/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: getAllUserTags.act.php,v 1.2 2006/11/30 22:02:46 adamfranco Exp $
 */
class getAllUserTagsAction
	extends TagXmlAction
{
		
	/**
	 * Execute this action.
	 * 
	 * @param object Harmoni $harmoni
	 * @return mixed
	 * @access public
	 * @since 11/10/06
	 */
	function &execute () {
// 		$harmoni =& Harmoni::instance();
// 		$harmoni->request->startNamespace("polyphony-tags");
// 		$itemId = RequestContext::value('item_id');
// 		$harmoni->request->endNamespace();

		$tagManager =& Services::getService("Tagging");
		$this->writeXmlResponse($tagManager->getUserTags());
	}
	
}

?>