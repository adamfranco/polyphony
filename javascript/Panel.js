/**
 * @since 11/29/06
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Panel.js,v 1.3 2007/01/30 15:46:57 adamfranco Exp $
 */

/**
 * The panel is an absolutely positioned element that can be positioned relative
 * to a calling element. The panel has a title-bar with close button and a content
 * area. The panel caches itself on the page and can be closed and reopened without
 * loosing its state.
 * 
 * @since 11/29/06
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Panel.js,v 1.3 2007/01/30 15:46:57 adamfranco Exp $
 */
function Panel ( title, height, width, positionElement, classNames ) {
	if ( arguments.length > 0 ) {
		this.init( title, height, width, positionElement, classNames);
	}
}

	/**
	 * Initialize this panel
	 * 
	 * @param string 	title
	 * @param integer	height
	 * @param integer	width
	 * @param optional DOM_Element positionElement
	 *		A unique element that this panel is associated with. An element can 
	 *		only have one panel associated with it, which will be cached with 
	 *		this element.
	 * @param string	classNames	Names of CSS classes to apply to the panel.
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	Panel.prototype.init = function ( title, height, width, positionElement, 
		classNames ) 
	{
		this.positionElement = positionElement;
		this.positionElement.panel = this;
		
		this.title = title;
		this.height = height;
		this.width = width;
		this.classNames = classNames;
		
		this.createElements();
	}
	
	/**
	 * Open or create the panel as needed
	 * 
	 * @return object Panel
	 * @access public
	 * @since 11/29/06
	 * @static
	 */
	Panel.run = function ( title, height, width, positionElement, classNames ) {
		if (positionElement.panel) {
			var panel = positionElement.panel;
			panel.open();
			
		} else {
			var panel = new Panel( title, height, width, positionElement, classNames );
		}
		
		if (panel.onOpen) {
			panel.onOpen();
		}
		
		return panel;
	}
	
	/**
	 * Create the panel elements
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	Panel.prototype.createElements = function () {
		this.mainElement = document.createElement("div");
		this.mainElement.panel = this;
		if (this.classNames)
			this.mainElement.className = 'panel ' + this.classNames;
		else
			this.mainElement.className = 'panel';
		
// 		this.mainElement.style.height = this.height + 'px';
		this.mainElement.style.width = this.width + 'px';
		this.mainElement.style.position = 'absolute';
		this.mainElement.style.overflow = 'auto';
		
		this.mainElement.style.top = this.getTop() + "px";
		this.mainElement.style.left = this.getLeft() + "px";
		
		// Top bar
		this.topBar = this.mainElement.appendChild(document.createElement("table"));
		this.topBar.className = 'topbar';
		this.topBar.style.width = '100%';
		
		var tbody = this.topBar.appendChild(document.createElement('tbody'));
		var row1 = tbody.appendChild(document.createElement('tr'));
		
		var topLeft = row1.appendChild(document.createElement('td'));
		topLeft.className = 'title';
		topLeft.innerHTML = this.title;
		
		var topRight = row1.appendChild(document.createElement('td'));
		topRight.className = 'close';
		var cancel = topRight.appendChild(document.createElement("a"));
		var panel = this;	// define a variable for panel that will be in the
							// scope of the onclick.
		cancel.onclick = function () {panel.close();}
		cancel.innerHTML = 'Close';
		
		// Content container
		this.contentElement = document.createElement("div");
		this.mainElement.appendChild(this.contentElement);
		this.contentElement.className = 'container';
		
		document.body.appendChild(this.mainElement);
	}
	
	/**
	 * Answer the number of pixels from the top of the screen to position the
	 * panel.
	 * 
	 * @return integer
	 * @access public
	 * @since 1/26/07
	 */
	Panel.prototype.getTop = function () {
		var top = (Panel.getOffsetTop(this.positionElement) 
						- Math.round(this.height / 2) 
						+ Math.round(this.positionElement.offsetHeight / 2));
		if (top < 5)
			top = 5;
		
		return top;
	}
	
	/**
	 * Answer the number of pixels from the left of the screen to position the
	 * panel.
	 * 
	 * @return integer
	 * @access public
	 * @since 1/26/07
	 */
	Panel.prototype.getLeft = function () {
		var left = (Panel.getOffsetLeft(this.positionElement) 
						- Math.round(this.width / 2) 
						+ Math.round(this.positionElement.offsetWidth / 2));
		if (left < 5)
			left = 5;
		
		return left;
	}
	
	/**
	 * Close the panel. Override the onClose() method of this object to add other
	 * actions to do on close.
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	Panel.prototype.close = function () {
		this.mainElement.style.display = 'none';
		if (this.onClose) {
			this.onClose();
		}
	}
	
	/**
	 * Open the panel. Override the onOpen() method of this object to add other
	 * actions to do on open.
	 * 
	 * @return void
	 * @access public
	 * @since 11/29/06
	 */
	Panel.prototype.open = function () {
		this.mainElement.style.display = 'block';
		if (this.onOpen) {
			this.onOpen();
		}
	}
	
	/**
	 * Reposition the main element based on the rendered height
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	Panel.prototype.centerOnHeight = function () {
		this.height = this.mainElement.offsetHeight;
		this.mainElement.style.top = this.getTop() + "px";
	}
	
	/**
	 * Reposition the main element based on the rendered width
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	Panel.prototype.centerOnWidth = function () {
		this.width = this.mainElement.offsetWidth;
		this.mainElement.style.left = this.getLeft() + "px";
	}
	
	/**
	 * Reposition the main element based on the rendered height and width
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	Panel.prototype.center = function () {
		this.centerOnHeight();
		this.centerOnWidth();
	}

	/**
	 * Recursively add up the offsets of the parent elements.
	 * 
	 * @param object element
	 * @return integer
	 * @access public
	 * @since 11/9/06
	 */
	Panel.getOffsetTop = function ( element ) {
		if (element.offsetParent)
			return element.offsetTop + Panel.getOffsetTop(element.offsetParent);
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
	Panel.getOffsetLeft = function ( element ) {
		if (element.offsetParent)
			return element.offsetLeft + Panel.getOffsetLeft(element.offsetParent);
		else
			return element.offsetLeft;
	}