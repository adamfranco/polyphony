<?php
/**
 * @since 10/5/07
 * @package 
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PlainTextModule.class.php,v 1.1 2007/10/05 14:04:25 adamfranco Exp $
 */ 

/**
 * Used when all part values are strings.
 * 
 * @since 10/5/07
 * @package <##>
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PlainTextModule.class.php,v 1.1 2007/10/05 14:04:25 adamfranco Exp $
 */
class PlainTextModule 
	extends DataManagerPrimativesModule 
{
		
	/**
	 * Answer the string value of a part for display
	 * 
	 * @param object Part
	 * @return string
	 * @access protected
	 * @since 10/5/07
	 */
	protected function getPartStringValue (Part $part) {
		return $part->getValue();
	}
	
}

?>