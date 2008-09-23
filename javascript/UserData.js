/**
 * @since 9/23/08
 * @package polyphony.userdata
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */

/**
 * The UserData class provides access to user preferences 
 * 
 * @since 9/23/08
 * @package polyphony.userdata
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
function UserData () {
	this.prefs = {};
}

	/**
	 * Answer the instance of the Selection
	 * 
	 * @return object Segue_Selection
	 * @access public
	 * @since 7/31/08
	 */
	UserData.instance = function () {
		if (!window.polyphony_UserData) {
			window.polyphony_UserData = new UserData();
		}
		
		return window.polyphony_UserData;
	}
	
	/**
	 * Answer a user preference
	 * 
	 * @param string key
	 * @return string
	 * @access public
	 * @since 9/23/08
	 */
	UserData.prototype.getPreference = function (key) {
		if (this.prefs[key])
			return this.prefs[key];
		else
			return null;
	}
	
	/**
	 * Set a user preference
	 * 
	 * @param string key
	 * @param string val
	 * @return string
	 * @access public
	 * @since 9/23/08
	 */
	UserData.prototype.setPreference = function (key, val) {
		this.prefs[key] = val;
		
		// Send off an asynchronous request to set the preference.
		var url = Harmoni.quickUrl('user', 'setPreference', {key: key, val: val});
		var req = Harmoni.createRequest();
		if (req) {
			var userData = this;
			// Set a callback for reloading the list.
			req.onreadystatechange = function () {
				
				// only if req shows 'loaded'
				if (req.readyState == 4) {
					// only if we get a good load should we continue.
					if (req.status == 200 && req.responseXML) {
						
					} else {
						alert("There was a problem retrieving the data:\n" +
							req.statusText);
					}
				}
			} 
		
			req.open('GET', url, true);
			req.send(null);
		} else {
			alert("Error: Unable to execute AJAX request. \nPlease upgrade your browser.");
		}
		
	}