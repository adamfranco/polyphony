/**
 * @since 7/31/08
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

FixedPanel.prototype = new Panel();
FixedPanel.prototype.constructor = Panel;
FixedPanel.superclass = Panel.prototype;

/**
 * FixedPanel provides a panel that is fixed on the screen as the user scrolls
 * the content.
 * 
 * @since 7/31/08
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function FixedPanel ( title, position, height, width, classNames ) {
	if ( arguments.length > 0 ) {
		this.init( title, position, height, width, classNames );
	}
}

	/**
	 * Initialize this panel
	 * 
	 * @param string 	title
	 * @param object 	position Example: {top: '0px', left: '50px'}
	 * @param integer	height
	 * @param integer	width
	 * @param string	classNames	Names of CSS classes to apply to the panel.
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	FixedPanel.prototype.init = function ( title, position, height, width, classNames ) {
		this.position = position;
		FixedPanel.superclass.init.call(this, 
								title,
								height,
								width,
								document.body,
								classNames);
	}
	
	/**
	 * Position the main element
	 * 
	 * @return void
	 * @access public
	 * @since 7/31/08
	 */
	FixedPanel.prototype.positionMainElement = function () {
// 		this.mainElement.style.height = this.height + 'px';
		this.mainElement.style.width = this.width + 'px';
		this.mainElement.style.position = 'fixed';
		this.mainElement.style.overflow = 'auto';
		if (this.position.top)
			this.mainElement.style.top = this.position.top
		if (this.position.left)
			this.mainElement.style.left = this.position.left
		if (this.position.right)
			this.mainElement.style.right = this.position.right
		if (this.position.bottom)
			this.mainElement.style.bottom = this.position.bottom	
	}
	
	/**
	 * Reposition the main element based on the rendered height
	 * 
	 * @return void
	 * @access public
	 * @since 1/26/07
	 */
	FixedPanel.prototype.centerOnHeight = function () {
		// Do nothing since we are fixed.
	}
