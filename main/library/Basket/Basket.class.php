<?php
/**
 * @since 8/5/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Basket.class.php,v 1.8 2006/05/22 20:59:05 adamfranco Exp $
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
 * @version $Id: Basket.class.php,v 1.8 2006/05/22 20:59:05 adamfranco Exp $
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
	 * @param integer $level The level of component to return
	 * @return object Component
	 * @access public
	 * @since 8/5/05
	 */
	function &getSmallBasketBlock ($level = ALERT_BLOCK) {
		$block =& new Block(
			"<div id='basket_small'>\n".$this->getSmallBasketHtml()."\n</div>", 
			$level);
		
		// controlling JS
		ob_start();
		$harmoni =& Harmoni::instance();
		
		$placeHolderUrl = POLYPHONY_PATH."/main/library/Basket/icons/1x1.png";
		
		$harmoni->request->startNamespace("basket");
		$addBasketURL = str_replace("&amp;", "&", 
			$harmoni->request->quickURL("basket", "addAjax", array("assets" => "xxxxx")));
		$emptyBasketURL = str_replace("&amp;", "&", 
			$harmoni->request->quickURL("basket", "emptyAjax"));
		$harmoni->request->endNamespace();
		
		print<<<END

<script type='text/javascript'>
// <![CDATA[

	/**
	 * Basket class
	 * Contains functions for interacting with the basket
	 * 
	 * @since 5/5/06
	 */
	function Basket () {
		
	}
	
	/**
	 * Add the asset ids to the basket and refresh the basket contents
	 * 
	 * @param array idArray
	 * @return void
	 * @access public
	 * @since 5/2/06
	 */
	Basket.addAssets = function ( idArray ) {
		if (idArray.length == 0)
			return;
			
		
		// Get the basket element
		var basketElement = getElementFromDocument('basket_small');
		var basketContentsElement = getElementFromDocument('basket_small_contents');
		
		// Add placeholders to the basket
		for (var i = 0; i < idArray.length; i++) {
			var elem = document.createElement('img');
			elem.style.border = '1px solid';
			elem.style.margin = '5px';
			elem.style.height = '30px';
			elem.style.width = '30px';
			elem.style.verticalAlign = 'middle';
			elem.src = '$placeHolderUrl';
			basketContentsElement.appendChild(elem);
			basketContentsElement.appendChild(document.createTextNode("\\n"));
		}
		
		// Build the destination url
		var addBasketURL = new String('$addBasketURL');
		var regex = new RegExp("xxxxx");
		addBasketURL = addBasketURL.replace(regex, idArray.join(','));
		Basket.reload(addBasketURL);
	}
	
	/**
	 * Empty the basket and refresh the small basket contents
	 * 
	 * @return void
	 * @access public
	 * @since 5/5/06
	 */
	Basket.empty = function () {
		Basket.reload('$emptyBasketURL');
	}
	
	/**
	 * Reload the small basket display via AJAX
	 * 
	 * @param string url
	 * @return void
	 * @access public
	 * @since 5/5/06
	 */
	Basket.reload = function ( url ) {
		/*********************************************************
		 * Do the AJAX request and repopulate the basket with 
		 * the contents of the result
		 *********************************************************/
					
		// branch for native XMLHttpRequest object (Mozilla, Safari, etc)
		if (window.XMLHttpRequest)
			var req = new XMLHttpRequest();
			
		// branch for IE/Windows ActiveX version
		else if (window.ActiveXObject)
			var req = new ActiveXObject("Microsoft.XMLHTTP");
		
		
		if (req) {
			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
						var basketElement = getElementFromDocument('basket_small');
						basketElement.innerHTML = req.responseText;
					} else {
						alert("There was a problem retrieving the XML data:\\n" +
							req.statusText);
					}
				}
			}
			
			req.open("GET", url, true);
			req.send(null);
		}
	}
	
	/**
	 * Answer the element of the document by id.
	 * 
	 * @param string id
	 * @return object The html element
	 * @access public
	 * @since 8/25/05
	 */
	function getElementFromDocument(id) {
		// Gecko, KHTML, Opera, IE6+
		if (document.getElementById) {
			return document.getElementById(id);
		}
		// IE 4-5
		if (document.all) {
			return document.all[id];
		}			
	}
	
// ]]>
</script>
END;
		
		$block->setPostHTML(ob_get_clean());
		
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
		print "\n\t<a href='";
		print $harmoni->request->quickURL("basket", "view");
		print "'>";
		print _("Selection: ");
		print "(".$this->count()." "._("items").")";
		print "</a>";
		
		print "\n\t<div id='basket_small_contents' style='text-align: left;'>";
		$this->reset();
		if ($this->hasNext()) {
			while ($this->hasNext()) {
				$id =& $this->next();
				$thumbnailURL = RepositoryInputOutputModuleManager::getThumbnailUrlForAsset($id);
				if ($thumbnailURL !== FALSE) {				
	// 				print "\n\t<br />";
					print "\n\t\t<img src='$thumbnailURL' alt='Thumbnail Image' border='0' style='max-height: 50px; max-width: 50px; vertical-align: middle; margin: 5px;' />";
				}
			}
		}
		print "\n\t</div>";
		
		if ($this->count()) {
			print "\n\t<div style='text-align: right; font-size: small;'>";
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
	function getAddLink ( &$assetId ) {
		$harmoni =& Harmoni::instance();
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
	
}

?>