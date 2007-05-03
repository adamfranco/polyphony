/**
 * @since 1/26/07
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CenteredPanel.js,v 1.4 2007/05/03 18:44:19 adamfranco Exp $
 */

CenteredPanel.prototype = new Panel();
CenteredPanel.prototype.constructor = CenteredPanel;
CenteredPanel.superclass = Panel.prototype;

/**
 * The centered panel is a panel that is centered on the browser window rather 
 * than a particular element.
 * 
 * @since 1/26/07
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: CenteredPanel.js,v 1.4 2007/05/03 18:44:19 adamfranco Exp $
 */
function CenteredPanel ( title, height, width, callingElement, classNames ) {
	if ( arguments.length > 0 ) {
		this.init( title, height, width, callingElement, classNames );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param string 	title
	 * @param integer	height
	 * @param integer	width
	 * @param object DOM_Element	callingElement 
	 *		A unique element that this panel is associated with. An element can 
	 *		only have one panel associated with it, which will be cached with 
	 *		this element.
	 * @param string	classNames	Names of CSS classes to apply to the panel.
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	CenteredPanel.prototype.init = function (  title, height, width, 
		callingElement,	classNames ) 
	{
		AuthZViewer.superclass.init.call(this, 
								title,
								height,
								width,
								callingElement,
								classNames);
	}
	
	/**
	 * Initialize and run this Panel
	 * 
	 * @param string 	title
	 * @param integer	height
	 * @param integer	width
	 * @param object DOM_Element	callingElement 
	 *		A unique element that this panel is associated with. An element can 
	 *		only have one panel associated with it, which will be cached with 
	 *		this element.
	 * @param string	classNames	Names of CSS classes to apply to the panel.
	 * @return object CenteredPanel
	 * @static
	 * @access public
	 * @since 1/26/07
	 */
	CenteredPanel.run = function ( title, height, width, 
		callingElement, classNames ) 
	{
		if (positionElement.panel) {
			var panel = positionElement.panel;
			panel.open();
			
		} else {
			var panel = new CenteredPanel( title, height, width, callingElement,
				classNames );
		}
		
		if (panel.onOpen) {
			panel.onOpen();
		}
		
		return panel;
	}
	
	/**
	 * Answer the number of pixels from the top of the screen to position the
	 * panel.
	 * 
	 * @return integer
	 * @access public
	 * @since 1/26/07
	 */
	CenteredPanel.prototype.getTop = function () {
		try {
			var height = Math.min(window.getInnerHeight(), this.height);
			var top = Math.round(window.getInnerHeight() / 2) - Math.round(height / 2);
			top = top + window.pageYOffset;
		} catch (error) {
			var top = 5;
		}
		
		if (top - window.pageYOffset < 5)
			top = top + 5;
		
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
	CenteredPanel.prototype.getLeft = function () {
		try {
			var width = Math.min(window.getInnerWidth(), this.width);
			var left = Math.round(window.getInnerWidth() / 2) - Math.round(width / 2);
		} catch (error) {
			var left = 5;
		}
		
		if (left -  - window.pageXOffset < 5)
			left = left + 5;
		
		return left;
	}
	
	/**
	 * Answer the height of the screen that maskes the background. This will be
	 * the larger of the document height or the window height
	 * 
	 * @return int
	 * @access public
	 * @since 2/2/07
	 */
	CenteredPanel.prototype.getScreenHeight = function () {
		if (window.getInnerHeight() > document.height) {
			return window.getInnerHeight();
		} else {
			return document.height;
			
		}
	}
	
	/**
	 * Create the panel elements
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	CenteredPanel.prototype.createElements = function () {
		CenteredPanel.superclass.createElements.call(this);
		
		this.mainElement.style.zIndex = '100';
		
		this.screen = document.createElement("div");
		this.screen.style.zIndex = '99';
		this.screen.style.position = 'absolute';
		this.screen.style.top = '0px';
		this.screen.style.left = '0px';
		this.screen.style.width = '100%';
		this.screen.style.height = this.getScreenHeight() + 'px';
		this.screen.style.backgroundColor = '#AAA';
		this.screen.style.filter = "alpha(opacity=70)";
		this.screen.style.MozOpacity = ".70";
		this.screen.style.opacity = ".70";
		document.body.appendChild(this.screen);
		
		var panel = this;
		window.onresize = function () {
			panel.center();
			panel.screen.style.height = panel.getScreenHeight() + 'px';
		}
	}
	
	/**
	 * Actions to execute on open
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	CenteredPanel.prototype.onOpen = function () {
		this.screen.style.display = 'block';
		this.centerOnHeight();
		this.centerOnWidth();
	}
	
	/**
	 * Actions to execute on close
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	CenteredPanel.prototype.onClose = function () {
		this.screen.style.display = 'none';
	}