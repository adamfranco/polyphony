<?php
/**
 * @since 12/14/05
 * @package polyphony.library.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: empty.act.php,v 1.5 2007/09/04 20:28:12 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * empties the basket
 * 
 * @since 12/14/05
 * @package polyphony.library.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: empty.act.php,v 1.5 2007/09/04 20:28:12 adamfranco Exp $
 */
class emptyAction 
	extends MainWindowAction {

	function isAuthorizedToExecute() {
		return true;
	}

	function buildContent() {
		$harmoni = Harmoni::Instance();
		
		$basket = Basket::instance();
		$basket->removeAllItems();
		
		RequestContext::locationHeader(
			$harmoni->request->quickURL("basket", "view"));
	}
}

?>