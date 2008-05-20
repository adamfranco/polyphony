<?php
/**
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WHorizontalRadioList.class.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Components/WRadioList.class.php");

/**
 * This adds a hoizontal (separated by spaces) list of input type='radio' elements.
 * 
 * @since Jul 20, 2005
 * @package polyphony.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WHorizontalRadioList.class.php,v 1.3 2007/09/19 14:04:51 adamfranco Exp $
 */
class WHorizontalRadioList extends WRadioList {
	function __construct() {
		parent::__construct();
		
		$this->_eachPost = "\n";
	}
}

?>