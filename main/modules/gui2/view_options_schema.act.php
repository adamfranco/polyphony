<?php
/**
 * @since 5/16/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/Action.class.php');

/**
 * Display the Theme Options schema.
 * 
 * @since 5/16/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class view_options_schemaAction
	extends Action
{
		
	/**
	 * AZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/16/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return null
	 * @access public
	 * @since 5/16/08
	 */
	public function execute () {
		header('Content-Type: text/xml');
		print file_get_contents(HARMONI.'/Gui2/theme_options.xsd');
		exit;
	}
}

?>