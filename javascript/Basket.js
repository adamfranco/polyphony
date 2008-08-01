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
		var basketElement = document.get_element_by_id('basket_small');
		var basketContentsElement = document.get_element_by_id('basket_small_contents');
		
		// Add placeholders to the basket
		for (var i = 0; i < idArray.length; i++) {
			var elem = document.createElement('img');
			elem.style.border = '1px solid';
			elem.style.margin = '2px';
			elem.style.marginRight = '1px';
			elem.style.height = '60px';
			elem.style.width = '60px';
			elem.style.verticalAlign = 'middle';
			elem.src = Harmoni.POLYPHONY_PATH + "/icons/1x1.png";
			basketContentsElement.appendChild(elem);
			basketContentsElement.appendChild(document.createTextNode("\\n"));
		}
		
		// Build the destination url
		var addBasketURL = Harmoni.quickUrl('basket', 'addAjax', 
			{assets: idArray.join(',')}, 'basket');
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
		var emptyBasketURL = Harmoni.quickUrl('basket', 'emptyAjax', {}, 'basket');
		Basket.reload(emptyBasketURL);
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
						var basketElement = document.get_element_by_id('basket_small');
						basketElement.innerHTML = req.responseText;
						Basket.removeBorders();
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
	 * Remove borders from div's surrounding images (in the basket) if the 
	 * images are loaded.
	 * 
	 * @param element parentNode
	 * @return boolean True if all are loaded
	 * @access public
	 * @since 6/15/06
	 */
	Basket.removeBoardersForCompletedImages = function (parentNode) {
		var allLoaded = true;
		for (var i in parentNode.childNodes) {
			var child = parentNode.childNodes[i];
			if (child.nodeType == 1 && child.tagName.toLowerCase() == "div") {
				for (var j in child.childNodes) {
					var grandchild = child.childNodes[j];
					if (grandchild.nodeType == 1 && grandchild.tagName.toLowerCase() == "img") {
						if (grandchild.height > 0 && grandchild.width > 0) {
							child.style.border='0px';
							child.style.margin='3px';
							
							/* Resize images for IE */ 
							if (grandchild.height > 50 || grandchild.width > 50) {
								grandchild.width = 50;
							}
						} else {
							allLoaded = false;
						}
					}
				}
			}
		}
		
		return allLoaded;
	}
	
	/**
	 * Loop waiting for images to load and then remove their boarders if they are
	 * loaded.
	 * 
	 * @return void
	 * @access public
	 * @since 6/15/06
	 */
	Basket.removeBorders = function () {
		if (!Basket.removeBoardersForCompletedImages(
					document.get_element_by_id('basket_small_contents')))
		{
			window.setTimeout('Basket.removeBorders()', 100);
		}
	}