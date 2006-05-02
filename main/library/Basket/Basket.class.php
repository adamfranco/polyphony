<?php
/**
 * @since 8/5/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Basket.class.php,v 1.1 2006/05/02 20:24:00 adamfranco Exp $
 */ 

/**
 * A Basket is a session-persistant ordered collection of Asset Ids. Items can be
 * added and removed from it, as well as its contents viewed.
 * 
 * @since 8/5/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Basket.class.php,v 1.1 2006/05/02 20:24:00 adamfranco Exp $
 */
class Basket 
	extends OrderedSet
{
		
/*********************************************************
 * Class Methods - Instance-Creation/Singlton
 *********************************************************/

	/**
	 * Get the instance of the Basket.
	 * The Basket class implements the Singleton pattern. There is only ever
	 * on instance of the Basket object and it is accessed only via the 
	 * Basket::instance() method.
	 * 
	 * @return object Basket
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	function &instance () {
		if (!isset($_SESSION['__basket'])) {
			$_SESSION['__basket'] =& new Basket();
		}
		
		return $_SESSION['__basket'];
	}

/*********************************************************
 * Instance Methods
 *********************************************************/	

	/**
	 * The constructor.
	 * @access public
	 * @return void
	 **/
	function Basket() {
		// Verify that there is only one instance of Harmoni.
		$backtrace = debug_backtrace();
		if (false && $GLOBALS['BASKET_INSTANTIATED'] 
			|| !(
				strtolower($backtrace[1]['class']) == strtolower('Basket')
				&& $backtrace[1]['function'] == 'instance'
				&& $backtrace[1]['type'] == '::'
			))
		{
			die("<br/><strong>Invalid Basket instantiation at...</strong>"
			."<br/> File: ".$backtrace[0]['file']
			."<br/> Line: ".$backtrace[0]['line']
			."<br/><strong>Access Basket with <em>Basket::instance()</em></strong>");
		}
		
		
		$idManager =& Services::getService("Id");
		$this->OrderedSet($idManager->getId("__basket"));	
	}
	
	/**
	 * removes unauthorized assets from the basket
	 * 
	 * @return void
	 * @access public
	 * @since 12/14/05
	 */
	function clean () {
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");

		$this->reset();
		while ($this->hasNext()) {	
			$id =& $this->next();
			if (!$authZ->isUserAuthorized(
				$idManager->getId("edu.middlebury.authorization.view"), $id))
			{
				$this->removeItem($id);
				$this->reset();
			}
		}
		$this->reset();
	}
	
	/**
	 * Return an XHTML string of a small version of the basket for use in a header. 
	 * Includes a link and the number of items in it.
	 * 
	 * @return object Component
	 * @access public
	 * @since 8/5/05
	 */
	function &getSmallBasketBlock () {
		$block = new Block($this->getSmallBasketHtml(), ALERT_BLOCK);
		
		return $block;
	}
	
	/**
	 * Answer the XHTML string of the small version of the basket contents
	 * 
	 * @param <##>
	 * @return <##>
	 * @access public
	 * @since 5/2/06
	 */
	function getSmallBasketHtml () {
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("basket");
		
		$this->clean();
		
		ob_start();
		print "<a href='";
		print $harmoni->request->quickURL("basket", "view");
		print "'>";
		print "<img src='".POLYPHONY_PATH."/main/library/Basket/icons/basket.png' height='25px' border='0' alt='"._("Basket")."' align='middle' /></a>";
		print "<a href='";
		print $harmoni->request->quickURL("basket", "view");
		print "'>";
		print "(".$this->count()." "._("items").")";
		print "</a>";
		
		if ($this->hasNext()) {
			print "\n\t\t<div style='text-align: left;'>";
			while ($this->hasNext()) {
				$id =& $this->next();
				$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($id);
				if ($thumbnailURL !== FALSE) {				
	// 				print "\n\t<br />";
					print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' style='max-height: 50px; max-width: 50px; vertical-align: middle;' />";
				}
			}
			print "\n\t\t</div>";
		}
		
		$harmoni->request->endNamespace();
		return ob_get_clean();
	}
	
}

?>