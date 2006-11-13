<?php
/**
 * @since 11/10/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagXmlAction.abstract.php,v 1.1.2.1 2006/11/13 21:55:20 adamfranco Exp $
 */ 

require_once(POLYPHONY_DIR.'/main/library/AbstractActions/Action.class.php');

/**
 * <##>
 * 
 * @since 11/10/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagXmlAction.abstract.php,v 1.1.2.1 2006/11/13 21:55:20 adamfranco Exp $
 */
class TagXmlAction
	extends Action
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
	function writeXmlResponse ( &$tags ) {
		$this->start();
		while ($tags->hasNext()) {
			$tag =& $tags->next();
			print "\n\t<tag value='".$tag->getValue()."' occurances='".$tag->getOccurances()."'/>";
		}
		$this->end();
	}
	
	/**
	 * Start the document
	 * 
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	function start () {
		if (isset($this->_started))
			return;
		
		$this->_started = true;
		header("Content-Type: text/xml; charset=utf-8");
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<response>
END;
	}
	
	/**
	 * End the document
	 * 
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	function end () {
		print "\n</response>";
		exit;
	}
}

?>