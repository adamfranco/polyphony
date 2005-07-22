<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WVerticalRadioList.class.php,v 1.1 2005/07/22 15:42:34 gabeschine Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WRadioList.class.php");

/**
 * This adds a vertical (separated by <br/> elements) list of input type='radio' elements.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WVerticalRadioList.class.php,v 1.1 2005/07/22 15:42:34 gabeschine Exp $
 */
class WVerticalRadioList extends WRadioList {
	function WVeriticalRadioList() {
		parent::WRadioList();
		
		$this->_glue = "<br/>\n";
	}
}

?>