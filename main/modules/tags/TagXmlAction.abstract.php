<?php
/**
 * @since 11/10/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagXmlAction.abstract.php,v 1.3 2007/09/04 20:28:14 adamfranco Exp $
 */ 

require_once(POLYPHONY_DIR.'/main/library/AbstractActions/XmlAction.class.php');

/**
 * <##>
 * 
 * @since 11/10/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagXmlAction.abstract.php,v 1.3 2007/09/04 20:28:14 adamfranco Exp $
 */
class TagXmlAction
	extends XmlAction
{
	
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * write the tags passed into an xml document
	 * 
	 * @param object TagIterator $tags
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	function writeXmlResponse ( $tags ) {
		$this->start();
		while ($tags->hasNext()) {
			$tag =$tags->next();
			print "\n\t<tag value='".$tag->getValue()."' occurances='".$tag->getOccurances()."'/>";
		}
		$this->end();
	}
}

?>