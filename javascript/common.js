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
 * @version $Id: common.js,v 1.4 2007/01/30 15:46:07 adamfranco Exp $
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
 * wrap on a word:
 * 
 * Originally by Jonas Raoni Soares Silva
 * from http://jsfromhell.com/string/wordwrap
 *
 * With modifications by Nick Kallen on Jul 15, 2006
 * from http://www.bigbold.com/snippets/posts/show/869
 * 
 * @param int maxLength 	The maximum amount of characters per line.
 * @param string breakWith 	The string that will be added whenever it's needed 
 *							to break the line, e.g. "\n" or "<br/>".
 * @param boolean cutWords 	If true, the words will be cut, so the line will 
 *							have exactly "maxLength" characters, otherwise 
 *							the words won't be cut
 * @return string
 * @access public
 * @since 1/23/07
 */
String.prototype.wordWrap = function(maxLength, breakWith, cutWords){
	var i, j, s, r = this.split("\n");
	if(maxLength > 0) for(i in r){
		for(s = r[i], r[i] = ""; s.length > maxLength;
			j = cutWords ? maxLength : (j = s.substr(0, maxLength).match(/\S*$/)).input.length - j[0].length
			|| maxLength,
			r[i] += s.substr(0, j) + ((s = s.substr(j)).length ? breakWith : "")
		);
		r[i] += s;
	}
	return r.join("\n");
}

/**
 * +-------------------------------------------------------------------------+
 * | jsPro - String                                                          |
 * +-------------------------------------------------------------------------+
 * | Copyright (C) 2001-2003 Stuart Wigley                                   |
 * +-------------------------------------------------------------------------+
 * | This library is free software; you can redistribute it and/or modify it |
 * | under the terms of the GNU Lesser General Public License as published by|
 * | the Free Software Foundation; either version 2.1 of the License, or (at |
 * | your option) any later version.                                         |
 * |                                                                         |
 * | This library is distributed in the hope that it will be useful, but     |
 * | WITHOUT ANY WARRANTY; without even the implied warranty of              |
 * | MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE. See the GNU Lesser |
 * | General Public License for more details.                                |
 * |                                                                         |
 * | You should have received a copy of the GNU Lesser General Public License|
 * | along with this library; if not, write to the Free Software Foundation, |
 * | Inc., 59 Temple Place, Suite 330, Boston, MA 02111-1307 USA             |
 * +-------------------------------------------------------------------------+
 * | Authors:   Stuart Wigley <stuartwigley@yahoo.co.uk>                     |
 * |            Randolph Fielding <gator4life@cinci.rr.com>                  |
 * +-------------------------------------------------------------------------+
 * $Id: common.js,v 1.4 2007/01/30 15:46:07 adamfranco Exp $
 *
 *
 * Replaces a small group of characters in this string defined in the HTML
 * 4.01 Special Characters Set with their Character Entity References.
 *
 * Changes by Adam Franco on 2007-01-23:
 * 		- removed exception usage.
 *
 * @summary             encode subset of HTML special characters
 * @author              Stuart Wigley
 * @author              Randolph Fielding
 * @version             1.1, 08/04/03
 * @interface           <code>String.htmlSpecialChars()</code>
 * @return              a modified string
 * @return              <code>null</code> if an exception is encountered
 * @throws              IllegalArgumentException
 */
String.prototype.htmlSpecialChars = function() {
	var iStringLength = this.length;
	var sModifiedString = '';
	
	for (var i = 0; i < iStringLength; i++) {
		switch (this.charCodeAt(i)) {
			case 34 : sModifiedString += '&quot;'; break;
			case 38 : sModifiedString += '&amp;' ; break;
			case 39 : sModifiedString += '&#39;' ; break;
			case 60 : sModifiedString += '&lt;'  ; break;
			case 62 : sModifiedString += '&gt;'  ; break;
			default : sModifiedString += this.charAt(i);
		}
	}
	
	return sModifiedString;
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

/**
 * Answer offset of an element from the left side of the document.
 * 
 * @param string id
 * @return object The html element
 * @access public
 * @since 8/25/05
 */
document.getOffsetLeft = function (element) {
	var offset = 0;
	
	if (element.offsetLeft)
		offset = offset + element.offsetLeft;
	
	if (element.offsetParent)
		offset = offset + document.getOffsetLeft(element.offsetParent);	
		
	return offset;
}

/**
 * Answer the inner height in pixels of the window in a cross-browser way
 * 
 * @return integer
 * @access public
 * @since 1/26/07
 */
window.getInnerHeight = function () {
	// Non-IE
	if (typeof( window.innerHeight ) == 'number')
		return window.innerHeight;
	
	// IE 6+ in 'strict-mode'
	if (document.documentElement 
		&& (document.documentElement.clientHeight || document.documentElement.clientWidth)) 
	{
		return document.documentElement.clientHeight;
	}
	
	if (document.body && (document.body.clientWidth || document.body.clientHeight)) 
	{
		return document.body.clientHeight;
	}
	
	throw new Error("Height of the window could not be determined. Browser may be too old.");
}

/**
 * Answer the inner width in pixels of the window in a cross-browser way
 * 
 * @return integer
 * @access public
 * @since 1/26/07
 */
window.getInnerWidth = function () {
	// Non-IE
	if (typeof( window.innerWidth ) == 'number')
		return window.innerWidth;
	
	// IE 6+ in 'strict-mode'
	if (document.documentElement 
		&& (document.documentElement.clientHeight || document.documentElement.clientWidth)) 
	{
		return document.documentElement.clientWidth;
	}
	
	if (document.body && (document.body.clientWidth || document.body.clientHeight)) 
	{
		return document.body.clientWidth;
	}
	
	throw new Error("Width of the window could not be determined. Browser may be too old.");
}