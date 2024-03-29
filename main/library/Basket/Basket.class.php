<?php
/**
 * @since 8/5/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Basket.class.php,v 1.19 2007/10/12 20:54:57 adamfranco Exp $
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
 * @version $Id: Basket.class.php,v 1.19 2007/10/12 20:54:57 adamfranco Exp $
 */
class Basket 
	extends OrderedSet
{
		
/*********************************************************
 * Class Methods - Instance-Creation/Singlton
 *********************************************************/
 	
	/**
	 * This class implements the Singleton pattern. There is only ever
	 * one instance of the this class and it is accessed only via the 
	 * ClassName::instance() method.
	 * 
	 * @return object 
	 * @access public
	 * @since 5/26/05
	 * @static
	 */
	public static function instance () {
		if (!isset($_SESSION['__basket'])) {
			$_SESSION['__basket'] = new Basket();
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
	public function __construct() {
		$idManager = Services::getService("Id");
		parent::__construct($idManager->getId("__basket"));	
	}
	
	/**
	 * removes unauthorized assets from the basket
	 * 
	 * @return void
	 * @access public
	 * @since 12/14/05
	 */
	function clean () {
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");

		$this->reset();
		while ($this->hasNext()) {	
			$id =$this->next();
			
			try {
				if (!$authZ->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"), $id))
				{
					$this->removeItem($id);
					$this->reset();
				}
			} catch (UnknownIdException $e) {
				// Let assets out of the purvue of our authorization manager slide.
			}
		}
		$this->reset();
	}
	
	/**
	 * Return an XHTML string of a small version of the basket for use in a header. 
	 * Includes a link and the number of items in it.
	 * 
	 * @param integer $level The level of component to return
	 * @return object Component
	 * @access public
	 * @since 8/5/05
	 */
	function getSmallBasketBlock ($level = ALERT_BLOCK) {
		return new Block(
			"<div id='basket_small'>\n".$this->getSmallBasketHtml()."\n</div>", 
			$level);
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
		$this->addHeadJavascript();
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("basket");
		
		$this->clean();
		
		ob_start();
		print "\n\t<a href='";
		print $harmoni->request->quickURL("basket", "view");
		print "'>";
		print _("Selection: ");
		print "(".$this->count()." "._("items").")";
		print "</a>";
		
		print "\n\t<div id='basket_small_contents' style='text-align: left; min-width: 200px;'>";
		$this->reset();
		$i = 0;
		if ($this->hasNext()) {
			while ($this->hasNext()) {
				$id =$this->next();
				$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($id);
				if ($thumbnailURL !== FALSE) {				
					print "\n\t<div style='border: 1px solid; height: 60px; width: 60px; float: left; text-align: center; vertical-align: middle; padding: 0px; margin: 2px;'>";
					
					//  The image
					print "\n\t\t<img class='thumbnail_image' \n\t\t\tsrc='$thumbnailURL' \n\t\t\talt='Thumbnail Image'";
					print " \n\t\t\tstyle='max-height: 50px; max-width: 50px; vertical-align: middle; margin: 5px; cursor: pointer;'";
					
					// border removal
					print " \n\t\t\tonload=\"if (this.parentNode) { this.parentNode.style.border='0px'; this.parentNode.style.margin='3px'; } /* Resize images for IE */ if (this.height > 50 || this.width > 50) {this.width = 50;}\" ";
					
					// Viewer Link
					print " \n\t\t\tonclick='window.open(";
					print '"'.VIEWER_URL."?&amp;source=";
					print urlencode($harmoni->request->quickURL("basket", "browse_xml"));
					print '&amp;start='.$i.'", ';
					print '"_blank", ';
					print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
					print ");'";
					print "\n\t\t/>";					
					print "\n\t</div>";
					
					$i++;
				}
			}
		}
		print "\n\t</div>";
		
		print <<< END
	
	<script type='text/javascript'>
	// <![CDATA[
	
		Basket.removeBorders();
	
	// ]]>
	</script>
		
END;
		
		if ($this->count()) {
			print "\n\t<div style='text-align: right; font-size: small; clear: both;'>";
			print "<a onclick='Basket.empty()'>"._("Empty")."</a>";
			print "\n\t</div>";
		}
		
		
		$harmoni->request->endNamespace();		
		return ob_get_clean();
	}
	
	/**
	 * Answer the link to add a particular id to the basket
	 * 
	 * @param object Id $assetId
	 * @return string XHTML
	 * @access public
	 * @since 5/2/06
	 */
	function getAddLink ( $assetId ) {
		$this->addHeadJavascript();
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("basket");
		ob_start();
		
		print "<a ";
		print " style='cursor: pointer;'";
		print " onclick='Basket.addAssets(new Array(\"".$assetId->getIdString()."\"));'";
		print ">"._('+ Selection');
		print "</a>";
		
		$harmoni->request->endNamespace();
		$harmoni->history->markReturnURL("polyphony/basket",
			$harmoni->request->mkURLWithPassthrough());
				
		return ob_get_clean();
	}

	/**
	 * Add the javascript to the document head
	 * 
	 * @return void
	 * @access protected
	 * @since 7/31/08
	 */
	protected function addHeadJavascript () {
		static $headJsAdded = false;
		if (!$headJsAdded) {
			$harmoni = Harmoni::instance();
			$outputHandler = $harmoni->getOutputHandler();
			$outputHandler->setHead(
				$outputHandler->getHead()
				."\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/Basket.js'></script>");
			
			$headJsAdded = true;
		}
	}	
}

?>