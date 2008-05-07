<?php
/**
 * @since 5/6/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/Action.class.php');

/**
 * Answer a thumbnail image for a theme.
 * 
 * @since 5/6/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class theme_thumbnailAction
	extends Action
{
		
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/6/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function execute () {
		$guiMgr = Services::getService("GUIManager");
		$theme = $guiMgr->getTheme(RequestContext::value('theme'));
		$image = $theme->getThumbnail();
		
		// Enable browser-based HTTP caching
		// @todo
		
		header("Content-Type: ".$image->getMimeType());
		header("Content-Length: ".$image->getSize());
		print $image->getContents();
		exit;
	}
	
}

?>