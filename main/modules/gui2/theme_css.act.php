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


/**
 * Answer a css file for a theme
 * 
 * @since 5/6/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class theme_cssAction
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
		
		// Enable browser-based HTTP caching
		// @todo
		
		$css = $theme->getCSS();
		
		header("Content-Type: text/plain");
		header("Content-Length: ".strlen($css));
		print $css;
		exit;
	}
	
}

?>