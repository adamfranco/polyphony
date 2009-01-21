/**
 * @since 11/9/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Tagger.js,v 1.6 2008/04/10 19:18:15 achapin Exp $
 */

Tagger.prototype = new Panel();
Tagger.prototype.constructor = Tagger;
Tagger.superclass = Panel.prototype;

/**
 * Tagger is a library for generating an ajax tagging interface.
 * 
 * @since 11/9/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Tagger.js,v 1.6 2008/04/10 19:18:15 achapin Exp $
 */
function Tagger ( itemId, system, positionElement, containerElement ) {
	if ( arguments.length > 0 ) {
		this.init( itemId, system, positionElement, containerElement );
	}
}

	/**
	 * Initialize the Tagger
	 * 
	 * @param string itemId
	 * @param object positionElement
	 * @return void
	 * @access public
	 * @since 11/9/06
	 */
	Tagger.prototype.init = function ( itemId, system, positionElement, containerElement ) {
		this.itemId = itemId;
		this.system = system;
		this.containerElement = containerElement;
		
		TagRenameDialog.superclass.init.call(this, 
								"Add Tags",
								200,
								300,
								positionElement);
		
		
		
			
		// Other's tags
		var element = document.createElement("div");
		element.className = 'heading';
		this.contentElement.appendChild(element);
		element.innerHTML = "Others' Tags on this item:";
		
		
		this.othersTagsArea = document.createElement("div");
		this.othersTagsArea.className = 'tag_area';
		this.contentElement.appendChild(this.othersTagsArea);
		this.othersTagsArea.innerHTML = "<div class='loading'>loading...</div>";
		this.loadTagsNotByUser(this.othersTagsArea);
		
		
		// User's tags on this item
		var element = document.createElement("div");
		this.contentElement.appendChild(element);
		element.className = 'heading';
		element.innerHTML = "Your Tags on this item:";
		
		this.currentTagsArea = document.createElement("div");
		this.currentTagsArea.className = 'tag_area';
		this.contentElement.appendChild(this.currentTagsArea);
		this.currentTagsArea.innerHTML = "<div class='loading'>loading...</div>";
		this.loadCurrentTags(this.currentTagsArea);
		
		this.newTagForm = document.createElement("form");
		this.contentElement.appendChild(this.newTagForm);
		
		this.newTagField = document.createElement("input");
		this.newTagField.type = 'text';
		this.newTagForm.appendChild(this.newTagField);
		
		this.newTagSubmit = document.createElement("input");
		this.newTagSubmit.type = 'submit';
		this.newTagSubmit.value = 'Add Tag';
		this.newTagForm.appendChild(this.newTagSubmit);
		
		var tagger = this;
		this.newTagForm.onsubmit = function () {
			tagger.addTag(tagger.newTagField.value);
			tagger.newTagField.value = '';
			tagger.newTagField.focus();
			return false;
		}
		this.newTagField.focus();
		
		// All users's tags
		var element = document.createElement("div");
		this.contentElement.appendChild(element);
		element.className = 'heading';
		element.innerHTML = "All of your Tags:";
		
		this.usersTagsArea = document.createElement("div");
		this.usersTagsArea.className = 'tag_area';
		this.contentElement.appendChild(this.usersTagsArea);
		this.usersTagsArea.innerHTML = "<div class='loading'>loading...</div>";
		this.loadAllUserTags(this.usersTagsArea);
		
		
		
		this.newTagField.focus();
	}
	
	/**
	 * Initialize and run the Tagger
	 * 
	 * @param string itemId
	 * @param string system
	 * @param object positionElement
	 * @return void
	 * @access public
	 * @since 11/9/06
	 * @static
	 */
	Tagger.run = function ( itemId, system, positionElement, containerElement, additionalParams ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tagger = new Tagger(itemId, system, positionElement, containerElement);
			if (additionalParams)
				tagger.additionalParams = additionalParams;
		}
	}
	
	/**
	 * Actions to take when reopening the panel
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	Tagger.prototype.onOpen = function () {
		// update any tags added through other taggers
		this.writeTagCloud(document.allUserTags, this.usersTagsArea, 'add');
		this.newTagField.focus();
	}
	
	/**
	 * Actions to take when closing the panel
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	Tagger.prototype.onClose = function () {
		this.reloadSourceCloud();
	}
	
	/**
	 * Write the tag-cloud HTML for the tags passed into the given element.
	 * 
	 * @param <##>
	 * @param object element
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	Tagger.prototype.writeTagCloud = function (tags, element, operation) {
		// Clear out the current contents of the element
		element.innerHTML = '';
		
		// return if there is nothing to do
		if (!tags.length)
			return;
		
		// Find the range of occurances
		var minFreq;
		var maxFreq;
		minFreq = maxFreq = tags[0].occurances;
		for (var i = 0; i < tags.length; i++) {
			if (tags[i].occurances < minFreq)
				minFreq = tags[i].occurances;
			else if (tags[i].occurances > maxFreq)
				maxFreq = tags[i].occurances;
		}
		
		// Define our styles that will be applied to each group.
		var styles = new Array();
		styles.push({"fontSize": "70%"});
		styles.push({"fontSize": "80%"});
		styles.push({"fontSize": "90%"});
		styles.push({"fontSize": "100%"});
		
		var incrementSize = Math.ceil((maxFreq - minFreq)/styles.length);
		
		// add the tags
		for (var i = 0; i < tags.length; i++) {
			// Locate the proper group for the tag
			var group = 0;
			for (var o=0; o < tags[i].occurances && group < styles.length; o = o + incrementSize) {
				var style = styles[group];
				group++;
			}
			
			// Create the element
			var tagElement = document.createElement("a");
			// add the styles to it
			for (var property in style)
				eval('tagElement.style.'+property+' = "'+style[property]+'";');
			// add it to the element
			tagElement.innerHTML = tags[i].value;
			
			var tagger = this;
			if (operation == 'add')
				tagElement.onclick = function () {
					tagger.addTag(this.innerHTML); 
					tagger.newTagField.focus();
				}
			else if (operation == 'remove')
				tagElement.onclick = function () {
					tagger.removeTag(this.innerHTML); 
					tagger.newTagField.focus();
				}
			
			element.appendChild(tagElement);
			element.appendChild(document.createTextNode(' '));
		}
	}
	
	/**
	 * Load the tag cloud of all of the user's tags
	 * 
	 * @param object element The destination for the tag cloud
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	Tagger.prototype.loadAllUserTags = function (element, forceReload) {
		// use cache if possible
		if (document.allUserTags && !forceReload) {
			this.writeTagCloud(document.allUserTags, element, 'add');
			return;
		}
		
		var req = Harmoni.createRequest();
		
		var url = Harmoni.quickUrl('tags', 'getAllUserTags', null, 'polyphony-tags');
				
// 		var newWindow = window.open(url);
		
		if (req) {
			// Define a variable to point at this Tagger that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var tagger = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
						tagger.displayErrors(req.responseXML);
						// Cache all user tags in the document so that we only
						// have to fetch them once and can update the cache o
						// on tag addition.
						document.allUserTags = tagger.getTagsFromXml(req.responseXML);
						tagger.writeTagCloud(document.allUserTags, element, 'add');
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			}
			
			req.open("GET", url, true);
			req.send(null);
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
	}
	
	/**
	 * Load the tag cloud of all of other users tags on this item
	 * 
	 * @param object element The destination for the tag cloud
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	Tagger.prototype.loadTagsNotByUser = function (element) {
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('tags', 'getTagsNotByUser', 
						{'item_id': this.itemId, 'system': this.system}, 
						'polyphony-tags');
// 		var newWindow = window.open(url);
		if (req) {
			// Define a variable to point at this Tagger that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var tagger = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
						tagger.displayErrors(req.responseXML);
						tagger.writeTagCloud(tagger.getTagsFromXml(req.responseXML), element, 'add');
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			}
			
			req.open("GET", url, true);
			req.send(null);
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
	}
	
	/**
	 * Load the tag cloud of all of the user's tags on this item
	 * 
	 * @param object element The destination for the tag cloud
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	Tagger.prototype.loadCurrentTags = function (element) {
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('tags', 'getTagsByUser', 
						{'item_id': this.itemId, 'system': this.system}, 
						'polyphony-tags');
		if (req) {
			// Define a variable to point at this Tagger that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var tagger = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
						tagger.displayErrors(req.responseXML);
						tagger.currentTags = tagger.getTagsFromXml(req.responseXML);
						tagger.writeTagCloud(tagger.currentTags, element, 'remove');
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			} 
			
			req.open("GET", url, true);
			req.send(null);
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
	}
	
	/**
	 * Reload the source tag cloud that was in place before tag editing started
	 * 
	 * @return void
	 * @access public
	 * @since 11/14/06
	 */
	Tagger.prototype.reloadSourceCloud = function (tags) {
		if (tags) {
			// Remove the existing tags. They will be siblings of the position
			// element with rel="tag"
			var container = this.containerElement;
			var current = container.firstChild;
			var toRemove = new Array();
			while (current) {
				if (current.nodeType == 1) {
					if (current.getAttribute('rel') == 'tag') {
						toRemove.push(current);
					}
				} else
					toRemove.push(current);
					
				current = current.nextSibling;
			}
			for (var i = 0; i < toRemove.length; i++)
				container.removeChild(toRemove[i]);
			
			var elementToInsertBefore = null;
			if (container.firstChild)
				elementToInsertBefore = container.firstChild;
			
			// Return if we don't have any new tags
			if (!tags.length)
				return;
			
			// Determine the StyleGroup increment size;
			var styles = this.positionElement.styles;
			var minFreq, maxFreq;
			minFreq = maxFreq = tags[0].occurances;
			for (var i = 1; i < tags.length; i++) {
				if (tags[i].occurances < minFreq)
					minFreq = tags[i].occurances;
				if (tags[i].occurances > maxFreq)
					maxFreq = tags[i].occurances;
			}
			var incrementSize = Math.ceil((maxFreq - minFreq)/styles.length);
			if (!incrementSize)
				incrementSize = 1;
			
			// Add in new tags
			var parameters = {};
			if (this.additionalParams) {
				for (var key in this.additionalParams) {
					parameters[key] = this.additionalParams[key];
				}
			}
			
			for (var i = 0; i < tags.length; i++) {
				var element = document.createElement('a');
				element.innerHTML = tags[i].value;
				element.setAttribute('rel', 'tag');
 				element.setAttribute('title', "View items tagged with '" + tags[i].value + "'");
 				
 				parameters.tag = tags[i].value;
				element.setAttribute('href', 
						Harmoni.quickUrl('tags', this.positionElement.viewAction, 
							parameters, 'polyphony-tags'));
				
				// styles for the cloud
				var group = 0;
				for (var o = 0; o < tags[i].occurances && group < styles.length; o = o + incrementSize) {
					var currentStyle = styles[group];
					group++;
				}
				for (var styleName in currentStyle) {
					element.style[styleName] = currentStyle[styleName];
				}
				
				// insert the tag
				if (elementToInsertBefore) {
					container.insertBefore(element, elementToInsertBefore);
					container.insertBefore(document.createTextNode(' '), elementToInsertBefore);
				} else {
					container.appendChild(element);
					container.appendChild(document.createTextNode(' '));
				}
			}
		
		} else {
			// Get the new tags and re-call this method with the tags
			var req = Harmoni.createRequest();
			var url = Harmoni.quickUrl('tags', 'getTags', 
							{'item_id': this.itemId, 'system': this.system}, 
							'polyphony-tags');
			if (req) {
				// Define a variable to point at this Tagger that will be in the
				// scope of the request-processing function, since 'this' will (at that
				// point) be that function.
				var tagger = this;
	
				req.onreadystatechange = function () {
					// only if req shows "loaded"
					if (req.readyState == 4) {
						// only if we get a good load should we continue.
						if (req.status == 200) {
							tagger.displayErrors(req.responseXML);
							tagger.reloadSourceCloud(tagger.getTagsFromXml(req.responseXML));
						} else {
							alert("There was a problem retrieving the XML data:\n" +
								req.statusText);
						}
					}
				} 
				
				req.open("GET", url, true);
				req.send(null);
				
			} else {
				alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
			}
		}
	}
	
	/**
	 * Answer an array of Tags as defined in the xml document
	 * 
	 * 		<response>
	 *			<tag value='xxxxx' ocurrances='5'/>
	 *			<tag value='yyyyy' ocurrances='3'/>
	 * 		</response>
	 * 
	 * @param object xmldoc
	 * @return array
	 * @access public
	 * @since 11/10/06
	 */
	Tagger.prototype.getTagsFromXml = function (xmldoc) {
		this.displayErrors(xmldoc);
		var tags = new Array();
		var tagNodes = xmldoc.getElementsByTagName('tag');
		for (var i = 0; i < tagNodes.length; i++) {
			tags.push(
				new Tag(
					tagNodes[i].getAttribute('value'), 
					tagNodes[i].getAttribute('occurances')));			
		}
		return tags;
	}
	
	/**
	 * Display any errors that are written to the xml doc.
	 * 
	 * @param object xmldoc
	 * @return void
	 * @access public
	 * @since 12/4/06
	 */
	Tagger.prototype.displayErrors = function (xmldoc) {
		var errorString = "The following errors occured:";
		var hasErrors = false;
		var errorNodes = xmldoc.getElementsByTagName('error');
		for (var i = 0; i < errorNodes.length; i++) {
			hasErrors = true;			
			errorString += "\n\tError: " + errorNodes[i].firstChild.nodeValue;
		}
		
		if (hasErrors)
			alert(errorString);
	}
	
	/**
	 * Add a tag.
	 * 
	 * @param string value
	 * @return void
	 * @access public
	 * @since 11/13/06
	 */
	Tagger.prototype.addTag = function (value) {
		var newTag = new Tag(value, 1);
		
		if (!newTag.value)
			return;
		
		// If the current tags haven't loaded, wait a moment and then try again.
		if (!this.currentTags) {
			var tagger = this;
			window.setTimeout(
				function () {
					tagger.addTag(value);
				},
				500);
			return;
		}
		
		// check to see if it is in the current tags
		for (var i = 0; i < this.currentTags.length; i++) {
			if (this.currentTags[i].value == newTag.value) 
				return;
		}
		
		this.currentTags.push(newTag);
		this.writeTagCloud(this.currentTags, this.currentTagsArea, 'remove');
		
		// send of an AJAX request to record the addition of this tag.
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('tags', 'addTag', 
						{'item_id': this.itemId, 'system': this.system, 'tag': newTag.value}, 
						'polyphony-tags');
		if (req) {
			// Define a variable to point at this Tagger that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var tagger = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
						tagger.displayErrors(req.responseXML);
						tagger.loadAllUserTags(tagger.usersTagsArea, true);
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			} 
			req.open("GET", url, true);
			req.send(null);
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
	}
	
	/**
	 * Remove a tag.
	 * 
	 * @param string value
	 * @return void
	 * @access public
	 * @since 11/13/06
	 */
	Tagger.prototype.removeTag = function (value) {
		// check to see if it is in the current tags
		var newTags = new Array();
		var newTag = new Tag(value, 1);
		var tagExists = false;
		for (var i = 0; i < this.currentTags.length; i++) {
			if (this.currentTags[i].value == newTag.value) {
				// send of an AJAX request to record the removal of this tag.
				var req = Harmoni.createRequest();
				var url = Harmoni.quickUrl('tags', 'removeTag', 
								{'item_id': this.itemId, 'system': this.system, 'tag': newTag.value}, 
								'polyphony-tags');
				if (req) {
					// Define a variable to point at this Tagger that will be in the
					// scope of the request-processing function, since 'this' will (at that
					// point) be that function.
					var tagger = this;
		
					req.onreadystatechange = function () {
						// only if req shows "loaded"
						if (req.readyState == 4) {
							// only if we get a good load should we continue.
							if (req.status == 200) {
								tagger.displayErrors(req.responseXML);
								tagger.loadAllUserTags(tagger.usersTagsArea, true);
							} else {
								alert("There was a problem retrieving the XML data:\n" +
									req.statusText);
							}
						}
					}
					
					req.open("GET", url, true);
					req.send(null);
				} else {
					alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
				}
			} else {
				newTags.push(this.currentTags[i]);
			}
		}
		
		this.currentTags = newTags;
		this.writeTagCloud(this.currentTags, this.currentTagsArea, 'remove');
	}

/**
 * A representation of a tag
 * 
 * @since 11/10/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Tagger.js,v 1.6 2008/04/10 19:18:15 achapin Exp $
 */
function Tag ( value, occurances ) {
	if ( arguments.length > 0 ) {
		this.init( value, occurances );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param string value
	 * @param integer occurances
	 * @return void
	 * @access public
	 * @since 11/10/06
	 */
	Tag.prototype.init = function ( value, occurances ) {
		this.value = value.toLowerCase();
		
		// drop any quotes
		this.value = this.value.replaceAll(/['"]/, '');
		// convert any non-allowed characters to underscores
		this.value = this.value.replaceAll(/[^a-z0-9_\-:]/, '_');
		// remove any multiple underscores
		this.value = this.value.replaceAll(/_{2,}/, '_');
		// Trim off any leading or trailing underscores
		this.value = this.value.replaceAll(/^_/, '');
		this.value = this.value.replaceAll(/_$/, '');
		this.value = this.value.toString();
		
		this.occurances = occurances;
	}
	

/**
 * This object represents a tag cloud. It can reorder the tag cloud based on item
 * frequency or alphabetically
 * 
 * @since 11/20/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Tagger.js,v 1.6 2008/04/10 19:18:15 achapin Exp $
 */
function TagCloud ( container ) {
	if ( arguments.length > 0 ) {
		this.init( container );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param DOM_Element container
	 * @return void
	 * @access public
	 * @since 11/20/06
	 */
	TagCloud.prototype.init = function ( container ) {
		this.container = container;
		
		this.collapsedList = 1;
		this.hideAfter = 15;

		this.dataNode = null;
		this.loadData();
		/* We do updateTagDisplay here, this may cause various
		things to be repeated*/
	}

	/** 
	 * Attempt to load data from a data node, which is indicated
	 * by the presence of an attribute named 'collapsedList'. This
	 * data node is used to simplify data storage across instances of
	 * TagClouds (as a new instance is created each time that a function
	 * on the cloud is called).
 	 *
	 * If the node isn't found, the defaults of collapsedList = 1, and 
	 * isDisplayedAsCloud = 1 are loaded and the node is created. 
	 * 
	 * If the node is found, then the values in it are found.
	 * @return void
	 * @access public
	 * @since 9/29/08
	 */
	TagCloud.prototype.loadData = function(){
		var item = this.container.firstChild;
		var targetNode = null;
		while(item){
			if(item.getAttribute){
				/* item.hasAttribute(attr) isn't supported
				by ie 6 and ie 7, so instead we'll use try
				and catch with getAttribute */
				try{
					if(item.getAttribute('collapsedList')){
						targetNode = item;
						break;
					}
				} catch(err) {
				 }
			}	
			item = item.nextSibling;
		}
		if(targetNode == null){
			this.collapsedList = 1;
			this.isDisplayedAsCloud = 1;
			this.dataNode = document.createElement("span");
			this.dataNode.setAttribute("collapsedList",1);
			this.dataNode.setAttribute("isDisplayedAsCloud",1);
			return;
		}
		this.dataNode = targetNode;
		this.collapsedList = parseInt(this.dataNode.getAttribute('collapsedList'));
		this.isDisplayedAsCloud = parseInt(this.dataNode.getAttribute('isDisplayedAsCloud'));
	}


	/**
	 * Display the tags in cloud format
	 *
	 * @return void
	 * @acess public
	 * @since 9/25/08
	 */
	TagCloud.prototype.displayAsCloud = function () {
		this.isDisplayedAsCloud = 1;
		this.dataNode.setAttribute('isDisplayedAsCloud','1');
		this.updateTagDisplay();
	}

	/** 
	 * Display the tags in list format
	 * @return void
	 * @access public
	 * @since 9/25/08
	 */
	TagCloud.prototype.displayAsList = function () {
		this.isDisplayedAsCloud = 0;
		this.dataNode.setAttribute('isDisplayedAsCloud','0');
		this.updateTagDisplay();
	}

	/**
	 * Update our entire display. 
	 * First eliminate the control (i.e. less/more) tags
	 * Then, depending on whether we're a cloud or list,
	 * change the font size and some other stuff.
	 * Then, call alter visibility to change how much
	 * of a list we're showing (if we're showing a list at all).
	 * @return void
	 * @access public
	 * @since 9/29/08
	 */
	TagCloud.prototype.updateTagDisplay = function() {
		this.clearControlNodes();
		this.restoreInnerHTML();
		var item = this.container.firstChild;
		while (item) {
			if(item.getAttribute){
				if(item.getAttribute('rel') == 'tag'){
					item.style.fontSize = (this.isDisplayedAsCloud) ? item.getAttribute('cloudStyle') : "100%";	
				}	
				if(item.getAttribute('rel') == 'list'){
					item.innerHTML = (this.isDisplayedAsCloud) ? "" : ("("+item.getAttribute('frequency')+")<br/>");
					item.style.fontSize = "100%";
				}
			}
			item = item.nextSibling;
		}
		this.alterVisibility();
	}	

	/**
	 * Restores the inner html of tags that have had their inner html
	 * stripped in order to hide them.
	 * @return void
	 * @access public
	 * @since 9/29/08
	 */ 
	TagCloud.prototype.restoreInnerHTML = function(){
		var item = this.container.firstChild;
		while(item){
			if(item.innerHTML == "" && item.getAttribute('data')){
				item.innerHTML = item.getAttribute('data');	
				item.setAttribute('data','');
			}
			item = item.nextSibling;
		}
	}

	/**
	 * Alters how much of the list we're showing, if
	 * we're displaying a list.
	 * First, if our list isn't collapsed (i.e. we're showing
	 *    all items), go through the list and figure out how many
	 *    tags we have. Then if the list can even be collapsed, we'll
	 *    output a control for hiding more of the list. We then return.
	 * Then, (i.e. if we're hiding stuff), go through the list, and hide
	 *    all of the items that are after our hideAfter point. Then, stick
	 *    in a control to show those items.
	 * @return void
	 * @access public
	 * @since 9/29/08
	 */
	TagCloud.prototype.alterVisibility = function() {
		if(this.isDisplayedAsCloud){
			return;
		}
		var item = this.container.firstChild;
		var i = 0;
		if(this.collapsedList == 0){
			var positionElement;
			while(item) {
				if(item.getAttribute && item.getAttribute('rel') == 'tag'){
					if(item.innerHTML == "" && item.getAttribute('data')){
						item.innerHTML = item.getAttribute('data');	
						item.setAttribute('data','');
					}
				}
				if(item.getAttribute && item.getAttribute('rel') == 'list'){
					i++;	
				}
				if(item.nodeType == 1){
					positionElement = item;
				}
				item = item.nextSibling;
			}
			if(i >= this.hideAfter){
				var newNode = document.createElement("a");
				newNode.setAttribute('rel','control');
				newNode.setAttribute('onClick','var cloud = new TagCloud(this.parentNode); cloud.showLess();');
				newNode.onclick = function(){ var cloud = new TagCloud(this.parentNode); cloud.showLess();};
				var newNodeText = document.createTextNode("-less-");
				newNode.appendChild(newNodeText);
				this.container.insertBefore(newNode,positionElement);
			}
			return;
		}
		var moreShown = 0;
		while(item) {
			if(item.getAttribute){
				if(item.getAttribute('rel') == 'tag'){
					if( i >= this.hideAfter){
						/* unfortunately, ie 6 and 7 don't have hasAttribute
						which makes this code more complicated than it should
						be */
						try{
							var data = item.getAttribute('data');
							if(data == '' || data == null){
								item.setAttribute("data",item.innerHTML);
								item.innerHTML = "";
							}
						} catch(err){
							item.setAttribute("data",item.innerHTML);
							item.innerHTML = "";
						}
					} 
				}
				if(item.getAttribute('rel') == 'list'){
					if( i >= this.hideAfter){
						item.style.fontSize = "0%";
						item.innerHTML = "";
					}  else {
						item.style.fontSize = "100%";
					}
					i++; 
				}
				if((i == this.hideAfter) && (moreShown == 0)){
					var newNode = document.createElement("a");
					newNode.setAttribute('rel','control');
					newNode.setAttribute('onClick','var cloud = new TagCloud(this.parentNode); cloud.showMore();');
					newNode.onclick = function(){ var cloud = new TagCloud(this.parentNode); cloud.showMore();};
					var newNodeText = document.createTextNode("-more-");
					newNode.appendChild(newNodeText);
					this.container.insertBefore(newNode,item.nextSibling);	
					moreShown = 1;
				}
			}
			item = item.nextSibling;
		}
	}

	/**
	 * Remove all of the controls (i.e. more or less in the tag cloud
	 * @return void
	 * @access public
	 * @since 9/29/08
	 */
	TagCloud.prototype.clearControlNodes = function(){
		var item = this.container.firstChild;
		while(item){
			if(item.getAttribute){
				if(item.getAttribute('rel') == 'control'){
					this.container.removeChild(item);
				}	
			}
			item = item.nextSibling;
		}
	}

	/**
	 * Show more of a collapsed list.	
	 * Set the variable for the cloud and the data node and
	 * then update the display of the cloud.
	 *
	 * @return void
	 * @access public
	 * @since 9/29/08
	 */
	TagCloud.prototype.showMore = function(){
		this.collapsedList = 0;	
		this.dataNode.setAttribute("collapsedList","0");
		this.updateTagDisplay();
	}

	/**
	 * Show less of a collapsed list.
	 * Set the variable for the cloud and the data node and
	 * then update the display of the cloud.
	 *
	 * @return void
	 * @access public
	 * @since 9/29/08
	 */
	TagCloud.prototype.showLess = function(){
		this.collapsedList = 1;
		this.dataNode.setAttribute("collapsedList","1");
		this.updateTagDisplay();
	}


	/**
	 * Order the tag cloud in alphabetical order.
	 * 
	 * @return void
	 * @access public
	 * @since 11/20/06
	 */
	TagCloud.prototype.orderAlpha = function () {
		this.restoreInnerHTML();
		var elements = new Array();
		var keys = new Array();
		var item = this.container.firstChild;
		var positionElement = null;
		while (item) {
			if (item.nodeName == 'A' && item.getAttribute && item.getAttribute('rel') == 'tag') {
				var oldItem = item;
				keys.push(oldItem.innerHTML);
				var newItem = item.nextSibling;
				while(newItem){
					if(newItem.getAttribute && newItem.getAttribute('rel') == 'list'){
						break;
					}
					newItem = newItem.nextSibling;
				}
				elements[oldItem.innerHTML] = new Array(oldItem,newItem);
			} else if (item.nodeType == 1){
				positionElement = item;
			}
			item = item.nextSibling;
		}
		this.dataNode = positionElement;	
		quick_sort(keys);
		for (var i = 0; i < keys.length; i++){
			this.container.insertBefore(elements[keys[i]][0], positionElement);
			this.container.insertBefore(elements[keys[i]][1], positionElement);
			this.container.insertBefore(document.createTextNode(' '), positionElement);
		}	
		this.updateTagDisplay();
	}
		

	/**
	 * Order the tag cloud in frequency order.
	 * 
	 * @return void
	 * @access public
	 * @since 11/20/06
	 */
	TagCloud.prototype.orderFreq = function () {
		this.restoreInnerHTML();
		var elements = new Array();
		var relation = new Array();
		var item = this.container.firstChild;
		var positionElement = null;
		
		while (item) {
			if (item.nodeName == 'A' && item.getAttribute && item.getAttribute('rel') == 'tag' && item.innerHTML) {
				var oldItem = item;
				var matches = item.getAttribute('title').match( /\(.+: ([0-9]+)\)/ );
				relation.push({'key': item.innerHTML, 'value': Math.round(matches[1])});
				var newItem = item;
				while(newItem){
					if(newItem.getAttribute && newItem.getAttribute('rel') == 'list'){
						break;
					}
					newItem = newItem.nextSibling;
				}
				elements[oldItem.innerHTML] = new Array(oldItem,newItem);
//			} else if (item.nodeType == 1 && !positionElement) {
			} else if (item.nodeType == 1){
				positionElement = item;
			}
			item = item.nextSibling;
		}

		this.dataNode = positionElement;	

		quick_sortValue(relation);
		
		for (var i = relation.length - 1; i >= 0; i--) {
			this.container.insertBefore(elements[relation[i].key][0], positionElement);
			this.container.insertBefore(elements[relation[i].key][1], positionElement);
			this.container.insertBefore(document.createTextNode(' '), positionElement);
		}		
		this.updateTagDisplay();
	}




TagRenameDialog.prototype = new Panel();
TagRenameDialog.prototype.constructor = TagRenameDialog;
TagRenameDialog.superclass = Panel.prototype;

/**
 * A dialog for renaming and deleting tags
 * 
 * @since 11/27/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Tagger.js,v 1.6 2008/04/10 19:18:15 achapin Exp $
 */
function TagRenameDialog ( tag, positionElement, viewAction ) {
	if ( arguments.length > 0 ) {
		this.init( tag, positionElement, viewAction );
	}
}

	/**
	 * Initialize and run the Tagger
	 * 
	 * @param string itemId
	 * @param string system
	 * @param object positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	TagRenameDialog.run = function (tag, positionElement, viewAction ) {
		if (positionElement.panel) {
			positionElement.panel.open();
		} else {
			var tagger = new TagRenameDialog(tag, positionElement, viewAction );
		}
	}

	/**
	 * Initialize the object
	 * 
	 * @param object tag
	 * @param object positionElement
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	TagRenameDialog.prototype.init = function ( tag, positionElement, viewAction ) {
		this.origTag = tag;
		
		TagRenameDialog.superclass.init.call(this, 
								"Rename '" + this.origTag.value +  "'",
								200,
								300,
								positionElement);
		
		if (!(viewAction))
			this.viewAction = 'viewuser';
		else
			this.viewAction = viewAction;
		
		// rename form
		this.renameForm = document.createElement("form");
		this.contentElement.appendChild(this.renameForm);
		
		var text = document.createElement("div");
		text.innerHTML = "All tags you have created with the name '" + this.origTag.value +  "' will be renamed. <br/>Others' tags will not be changed. <br/><br/><strong>New Tag name:</strong>";
		this.renameForm.appendChild(text);
		
		this.newTagField = document.createElement("input");
		this.newTagField.type = 'text';
		this.renameForm.appendChild(this.newTagField);
		
		this.newTagSubmit = document.createElement("input");
		this.newTagSubmit.type = 'submit';
		this.newTagSubmit.value = 'Rename';
		this.renameForm.appendChild(this.newTagSubmit);
		
		var tagRenameDialog = this;
		this.renameForm.onsubmit = function () {
			tagRenameDialog.rename(tagRenameDialog.newTagField.value);
			tagRenameDialog.renameForm.style.textDecoration = 'blink';
			tagRenameDialog.renameForm.style.textAlign = 'center';
			tagRenameDialog.renameForm.innerHTML = 'working...';
			return false;
		}
		
		this.newTagField.focus();
	}
	
	/**
	 * Rename a Tag
	 * 
	 * @param string newTagValue
	 * @return void
	 * @access public
	 * @since 11/27/06
	 */
	TagRenameDialog.prototype.rename = function (newTagValue) {
		var newTag = new Tag(newTagValue, 1);
		
		// Get the new tags and re-call this method with the tags
		var req = Harmoni.createRequest();
		var url = Harmoni.quickUrl('tags', 'renameUser', 
						{'tag': this.origTag.value, 'newTag': newTag.value}, 
						'polyphony-tags');
		if (req) {
			// Define a variable to point at this Tagger that will be in the
			// scope of the request-processing function, since 'this' will (at that
			// point) be that function.
			var tagRenameDialog = this;

			req.onreadystatechange = function () {
				// only if req shows "loaded"
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200) {
// 						alert('All occurances of tag ' + tagRenameDialog.origTag.value + ' have been renamed to ' + newTag.value);
						window.location =  Harmoni.quickUrl('tags', tagRenameDialog.viewAction, 
							{'tag': newTag.value}, 'polyphony-tags');
					} else {
						alert("There was a problem retrieving the XML data:\n" +
							req.statusText);
					}
				}
			} 
			
			req.open("GET", url, true);
			req.send(null);
			
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}		
	}
