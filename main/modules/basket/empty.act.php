<?php
/**
 * @since 12/14/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: empty.act.php,v 1.1 2005/12/14 21:05:59 cws-midd Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * empties the basket
 * 
 * @since 12/14/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: empty.act.php,v 1.1 2005/12/14 21:05:59 cws-midd Exp $
 */
class emptyAction 
	extends MainWindowAction {

	function isAuthorizedToExecute() {
		return true;
	}

	function buildContent() {
		$harmoni =& Harmoni::Instance();
		
		$basket =& BasketManager::getBasket();
		$basket->removeAllItems();
		
		RequestContext::locationHeader(
			$harmoni->request->quickURL("home", "welcome"));
	}
}

?>