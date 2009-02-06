/**
 * @since 2/26/07
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabbedContent.js,v 1.1 2007/02/26 20:13:25 adamfranco Exp $
 */

/**
 * <##>
 * 
 * @since 2/26/07
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabbedContent.js,v 1.1 2007/02/26 20:13:25 adamfranco Exp $
 */
function TabbedContent () {
		this.tabOrder = [];
		this.tabs = {};
		this.selectedTabName = null;
		this.tabMenuItems = {};
		
		this.tabList = document.createElement('ul');
		this.tabList.className = 'tab_menu';
		this.tabContainer = document.createElement('div');
		this.tabContainer.className = 'tab_container';
}
	
	/**
	 * Add the tab menu and content to the container element
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	TabbedContent.prototype.appendToContainer = function (container) {
		container.appendChild(this.tabList);
		container.appendChild(this.tabContainer);
	}
	
	/**
	 * Add a tab.
	 * 
	 * @param string name
	 * @param string title
	 * @return object Tab
	 * @access public
	 * @since 2/26/07
	 */
	TabbedContent.prototype.addTab = function ( name, title ) {
		this.tabs[name] = new Tab (this, name, title );
		this.tabOrder.push(name);
		
		this.tabMenuItems[name] = this.tabList.appendChild(document.createElement('li'));
		this.tabMenuItems[name].className = 'unselected';
		this.tabMenuItems[name].innerHTML = this.tabs[name].title;
		this.tabMenuItems[name].tab = this.tabs[name];
		this.tabMenuItems[name].onclick = function () {
			this.tab.select();
		}
		
		if (this.tabOrder.length == 1) {
			this.selectTab(name);
		}
		
		return this.tabs[name];
	}
	
	/**
	 * Answer a tab.
	 * 
	 * @param string name
	 * @param string title
	 * @return object Tab
	 * @access public
	 * @since 2/26/07
	 */
	TabbedContent.prototype.getTab = function ( name ) {
		if (!this.tabs[name]) {
			throw new Error("Tried to get a non-existant tab, '" + name + "'.");
		}
		
		return this.tabs[name];
	}
	
	/**
	 * Answer all tabs in order.
	 * 
	 * @return array
	 * @access public
	 * @since 2/26/07
	 */
	TabbedContent.prototype.getTabs = function () {
		var temp = [];
		for (var i = 0; i < tabOrder.length; i++) {
			temp.push(this.tabs[this.tabOrder[i]]);
		}
		return temp;
	}
	
	/**
	 * Select a given tab
	 * 
	 * @param string name
	 * @return object Tab
	 * @access public
	 * @since 2/26/07
	 */
	TabbedContent.prototype.selectTab = function ( name ) {
		if (!this.tabs[name]) {
			throw new Error("Tried to select a non-existant tab, '" + name + "'.");
		}
		
		if (this.selectedTabName) {
			this.tabs[this.selectedTabName].onClose();
		}
		
		this.selectedTabName = name;
		
		// Remove Existing elements from the container
		for (var i = 0; i < this.tabContainer.childNodes.length; i++) {
			this.tabContainer.removeChild(this.tabContainer.childNodes[i]);
		}
		
		for (var key in this.tabMenuItems) {
			if (key == name) {
				this.tabMenuItems[key].className = 'selected';
			} else {
				this.tabMenuItems[key].className = 'unselected';
			}
		}
		
		// Tell the tab we are opening it.
		this.tabs[name].onOpen();
		
		// Attach the tab's content into our container
		this.tabContainer.appendChild(this.tabs[name].wrapperElement);
		
		return this.tabs[name];
	}
	
	/**
	 * Answer the current tab
	 * 
	 * @return Tab
	 * @access public
	 * @since 2/2/09
	 */
	TabbedContent.prototype.getSelected = function () {
		if (this.selectedTabName) {
			if (!this.tabs[this.selectedTabName]) {
				throw new Error("Tried to get a non-existant tab, '" + this.selectedTabName + "'.");
			}
			return this.tabs[this.selectedTabName];
		}
		
		throw new Error("No tab selected");
	}


/**
 * A tab is a subdivision of a panel in a set of TabbedContent. One member of 
 * the TabbedContent is shown at any one time. Switching between tabs is done 
 * via a menu.
 * 
 * @since 2/26/07
 * @package <##>
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TabbedContent.js,v 1.1 2007/02/26 20:13:25 adamfranco Exp $
 */
function Tab (owner, name, title ) {
	if ( arguments.length > 0 ) {
		this.init( owner, name, title );
	}
}

	/**
	 * Initialize this object
	 * 
	 * @param object TabCenteredPanel owner
	 * @param string name
	 * @param string title
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	Tab.prototype.init = function ( owner, name, title ) {
		this.owner = owner;
		this.name = name;
		this.title = title;
		this.wrapperElement = document.createElement('div');
	}
	
	/**
	 * Select this tab
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	Tab.prototype.select = function () {
		this.owner.selectTab(this.name);
	}
	
	/**
	 * Actions to do on loading this tab
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	Tab.prototype.onOpen = function () {
		
	}
	
	/**
	 * Actions to do on closing this tab
	 * 
	 * @return void
	 * @access public
	 * @since 2/26/07
	 */
	Tab.prototype.onClose = function () {
		
	}
