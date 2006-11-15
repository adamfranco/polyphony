/**
 * @since 11/9/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Tagger.js,v 1.1.2.5 2006/11/15 18:08:53 adamfranco Exp $
 */

/**
 * Tagger is a library for generating an ajax tagging interface.
 * 
 * @since 11/9/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Tagger.js,v 1.1.2.5 2006/11/15 18:08:53 adamfranco Exp $
 */
function Tagger ( itemId, system, positionElement ) {
	if ( arguments.length > 0 ) {
		this.init( itemId, system, positionElement );
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
	Tagger.prototype.init = function ( itemId, system, positionElement ) {
		this.itemId = itemId;
		this.system = system;
		this.positionElement = positionElement;
		
		this.panelHeight = 200;
		this.panelWidth = 300;
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
	 */
	Tagger.run = function ( itemId, system, positionElement ) {
		var panel;
		if (panel = document.get_element_by_id('tagger_' + itemId)) {
			panel.style.display = 'block';
			// update any tags added through other taggers
			panel.tagger.writeTagCloud(document.allUserTags, panel.tagger.usersTagsArea, 'add');
		} else {
			var tagger = new Tagger(itemId, system, positionElement);
			tagger.createPanel();
		}
	}
	
	/**
	 * Create a panel for doing the tagging. This will be absolutely positioned
	 * such that it is over the position element passed in launching it.
	 * 
	 * @return void
	 * @access public
	 * @since 11/9/06
	 */
	Tagger.prototype.createPanel = function () {
		this.panel = document.createElement("div");
		this.panel.tagger = this;
		
		
		this.panel.className = 'tagging_panel';
		this.panel.id = "tagger_" + this.itemId;
		
// 		this.panel.style.height = this.panelHeight + 'px';
		this.panel.style.width = this.panelWidth + 'px';
		this.panel.style.position = 'absolute';
		this.panel.style.overflow = 'auto';
		
		var top = (Tagger.getOffsetTop(this.positionElement) 
						- Math.round(this.panelHeight / 2) 
						+ Math.round(this.positionElement.offsetHeight / 2));
		if (top < 5)
			top = 5;
		
		var left = (Tagger.getOffsetLeft(this.positionElement) 
						- Math.round(this.panelWidth / 2) 
						+ Math.round(this.positionElement.offsetWidth / 2));
		if (left < 5)
			left = 5;
		this.panel.style.top = top + "px";
		this.panel.style.left = left + "px";
		
		document.body.appendChild(this.panel);
		
		
		// Top bar
		this.topBar = document.createElement("div");
		this.topBar.className = 'topbar container';
		this.panel.appendChild(this.topBar);
		var html = "<div class='title'>Add Tags</div>";
		html += "<div class='close'></div>";
		this.topBar.innerHTML = html;
		
		var topRight = this.topBar.childNodes[1];
		var cancel = document.createElement("a");
		topRight.appendChild(cancel);
		var panel = this.panel;	// define a variable for panel that will be in the
								// scope of the onclick.
		cancel.onclick = function () {panel.tagger.reloadSourceCloud(); panel.style.display = 'none'}
		cancel.innerHTML = 'Close';
		
		
		// Other's tags
		this.tagRow = document.createElement("div");
		this.panel.appendChild(this.tagRow);
		this.tagRow.className = 'container';
		
		var element = document.createElement("div");
		element.className = 'heading';
		this.tagRow.appendChild(element);
		element.innerHTML = "Others' Tags on this item:";
		
		
		this.othersTagsArea = document.createElement("div");
		this.othersTagsArea.className = 'tag_area';
		this.tagRow.appendChild(this.othersTagsArea);
		this.othersTagsArea.innerHTML = "<div class='loading'>loading...</div>";
		this.loadTagsNotByUser(this.othersTagsArea);
		
		
		// User's tags on this item
		var element = document.createElement("div");
		this.tagRow.appendChild(element);
		element.className = 'heading';
		element.innerHTML = "Your Tags on this item:";
		
		this.currentTagsArea = document.createElement("div");
		this.currentTagsArea.className = 'tag_area';
		this.tagRow.appendChild(this.currentTagsArea);
		this.currentTagsArea.innerHTML = "<div class='loading'>loading...</div>";
		this.loadCurrentTags(this.currentTagsArea);
		
		this.newTagForm = document.createElement("form");
		this.tagRow.appendChild(this.newTagForm);
		
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
		this.tagRow.appendChild(element);
		element.className = 'heading';
		element.innerHTML = "All of your Tags:";
		
		this.usersTagsArea = document.createElement("div");
		this.usersTagsArea.className = 'tag_area';
		this.tagRow.appendChild(this.usersTagsArea);
		this.usersTagsArea.innerHTML = "<div class='loading'>loading...</div>";
		this.loadAllUserTags(this.usersTagsArea);
		
	}
	
	/**
	 * Recursively add up the offsets of the parent elements.
	 * 
	 * @param object element
	 * @return integer
	 * @access public
	 * @since 11/9/06
	 */
	Tagger.getOffsetTop = function ( element ) {
		if (element.offsetParent)
			return element.offsetTop + Tagger.getOffsetTop(element.offsetParent);
		else
			return element.offsetTop;
	}
	
	/**
	 * Recursively add up the offsets of the parent elements.
	 * 
	 * @param object element
	 * @return integer
	 * @access public
	 * @since 11/9/06
	 */
	Tagger.getOffsetLeft = function ( element ) {
		if (element.offsetParent)
			return element.offsetLeft + Tagger.getOffsetLeft(element.offsetParent);
		else
			return element.offsetLeft;
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
				tagElement.onclick = function () {tagger.addTag(this.text);}
			else if (operation == 'remove')
				tagElement.onclick = function () {tagger.removeTag(this.text);}
			
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
	Tagger.prototype.loadAllUserTags = function (element) {
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
			var container = this.positionElement.parentNode;
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
			for (var i = 0; i < tags.length; i++) {
				var element = document.createElement('a');
				element.innerHTML = tags[i].value;
				element.setAttribute('rel', 'tag');
 				element.setAttribute('title', "View items tagged with '" + tags[i].value + "'");
				element.setAttribute('href', 
						Harmoni.quickUrl('tags', this.positionElement.viewAction, 
							{'tag': tags[i].value}, 'polyphony-tags'));
				
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
				container.insertBefore(element, this.positionElement);
				container.insertBefore(document.createTextNode(' '), this.positionElement);
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
	 * Add a tag.
	 * 
	 * @param string value
	 * @return void
	 * @access public
	 * @since 11/13/06
	 */
	Tagger.prototype.addTag = function (value) {
		var newTag = new Tag(value, 1);
		
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
			req.open("GET", url, true);
			req.send(null);
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
		
		this.loadAllUserTags(this.usersTagsArea);
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
		this.loadAllUserTags(this.usersTagsArea);
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
 * @version $Id: Tagger.js,v 1.1.2.5 2006/11/15 18:08:53 adamfranco Exp $
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
