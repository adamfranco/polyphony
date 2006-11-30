/**
 * This file contains some common functions and extentions to native Javascript objects
 * needed by many scripts.
 *
 * @since 11/29/06
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: common.js,v 1.2 2006/11/30 22:02:36 adamfranco Exp $
 */

/**
 * Add a method to string to allow it to replace all occurances
 * of an expression
 * 
 * @param RegExp regExp
 * @param string replaceValue
 * @return String
 * @access public
 * @since 11/10/06
 */
String.prototype.replaceAll = function (regExp, replaceValue) {
	var newString = this;
	var matches;
	while (matches = newString.match(regExp)) {
		newString = newString.replace(regExp, replaceValue);
	}
	return newString;
}

/**
 * Replace '&amp;' in URLs with '&'
 * 
 * @return string
 * @access public
 * @since 6/12/06
 */
String.prototype.urlDecodeAmpersands = function () {
	return this.replaceAll(/&amp;/, '&');
}

/**
 * Search the array and return true if the passed parameter is an element.
 * 
 * @param mixed value
 * @return boolean
 * @access public
 * @since 11/30/06
 */
Array.prototype.elementExists = function (value) {
	for (var i = 0; i < this.length; i++) {
		if (this[i] == value)
			return true;
	}
	
	return false;
}

/**
 * Answer the element of the document by id.
 * 
 * @param string id
 * @return object The html element
 * @access public
 * @since 8/25/05
 */
document.get_element_by_id = function (id) {
	// Gecko, KHTML, Opera, IE6+
	if (document.getElementById) {
		return document.getElementById(id);
	}
	// IE 4-5
	if (document.all) {
		return document.all[id];
	}			
}