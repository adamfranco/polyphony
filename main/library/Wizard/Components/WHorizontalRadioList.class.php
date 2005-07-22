<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WHorizontalRadioList.class.php,v 1.1 2005/07/22 15:42:32 gabeschine Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WRadioList.class.php");

/**
 * This adds a hoizontal (separated by spaces) list of input type='radio' elements.
 * 
 * @since Jul 20, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WHorizontalRadioList.class.php,v 1.1 2005/07/22 15:42:32 gabeschine Exp $
 */
class WHorizontalRadioList extends WRadioList {
	function WHorizontalRadioList() {
		parent::WRadioList();
		
		$this->_glue = "\n";
	}
}

?>