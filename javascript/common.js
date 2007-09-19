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
 * @version $Id: common.js,v 1.8 2007/09/19 20:49:48 adamfranco Exp $
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
 * $Id: common.js,v 1.8 2007/09/19 20:49:48 adamfranco Exp $
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
 * Answer offset of an element from the top of the document.
 * 
 * @param string id
 * @return object The html element
 * @access public
 * @since 5/3/07
 */
document.getOffsetTop = function (element) {
	var offset = 0;
	
	if (element.offsetTop)
		offset = offset + element.offsetTop;
	
	if (element.offsetParent)
		offset = offset + document.getOffsetTop(element.offsetParent);	
		
	return offset;
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
 * Answer the height of the document
 * 
 * @return integer
 * @access public
 * @since 5/3/07
 */
document.getHeight = function () {
	var maxFound = 0;
	if (typeof( document.height ) == 'number')
		maxFound = Math.max(maxFound, document.height);
	
	// Firefox, some cases
	if (document.body && typeof( document.body.scrollHeight ) == 'number')
		maxFound = Math.max(maxFound, document.body.scrollHeight);
	
	// IE
	if (document.body && typeof( document.body.offsetHeight ) == 'number')
		maxFound = Math.max(maxFound, document.body.offsetHeight);
	
	return maxFound;
	
	throw new Error("Undefined Height");
}

/**
 * Answer the width of the document
 * 
 * @return integer
 * @access public
 * @since 5/3/07
 */
document.getWidth = function () {
	if (typeof( document.width ) == 'number')
		return document.width;
	
	// IE
	if (document.body && typeof( document.body.offsetWidth ) == 'number')
		return document.body.offsetWidth;
	
	throw new Error("Undefined Width");
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

/**
 * Answer the amount of scroll in the vertical direction.
 * 
 * @return integer
 * @access public
 * @since 5/3/07
 */
window.getScrollY = function () {
	// Non-IE
	if (typeof( window.pageYOffset ) == 'number')
		return window.pageYOffset;
	
	// Some IE
	else if (document.body && document.body.scrollTop)
		return document.body.scrollTop;
	
	// IE 6 - standards complient mode
	else if (document.documentElement && document.documentElement.scrollTop)
		return document.documentElement.scrollTop;
	
	else
		return 0;
}

/**
 * Answer the amount of scroll in the horizontal direction.
 * 
 * @return integer
 * @access public
 * @since 5/3/07
 */
window.getScrollX = function () {
	// Non-IE
	if (typeof( window.pageXOffset ) == 'number')
		return window.pageXOffset;
	
	// Some IE
	else if (document.body && document.body.scrollLeft)
		return document.body.scrollLeft;
	
	// IE 6 - standards complient mode
	else if (document.documentElement && document.documentElement.scrollLeft)
		return document.documentElement.scrollLeft;
	
	else
		return 0;
}


/*********************************************************
 * Date Functions - Start
 *********************************************************/

// ===================================================================
// Author: Matt Kruse <matt@mattkruse.com>
// WWW: http://www.mattkruse.com/
//
// NOTICE: You may use this code for any purpose, commercial or
// private, without any further permission from the author. You may
// remove this notice from your final code if you wish, however it is
// appreciated by the author if at least my web site address is kept.
//
// You may *NOT* re-distribute this code in any way except through its
// use. That means, you can include it in your product, or your web
// site, or any other form where the code is actually being used. You
// may not put the plain javascript up on your site for download or
// include it in your javascript libraries for download. 
// If you wish to share this code with others, please just point them
// to the URL instead.
// Please DO NOT link directly to my .js files from your site. Copy
// the files to your server and use them there. Thank you.
// ===================================================================

// HISTORY
// ------------------------------------------------------------------
// May 17, 2003: Fixed bug in parseDate() for dates <1970
// March 11, 2003: Added parseDate() function
// March 11, 2003: Added "NNN" formatting option. Doesn't match up
//                 perfectly with SimpleDateFormat formats, but 
//                 backwards-compatability was required.

// ------------------------------------------------------------------
// These functions use the same 'format' strings as the 
// java.text.SimpleDateFormat class, with minor exceptions.
// The format string consists of the following abbreviations:
// 
// Field        | Full Form          | Short Form
// -------------+--------------------+-----------------------
// Year         | yyyy (4 digits)    | yy (2 digits), y (2 or 4 digits)
// Month        | MMM (name or abbr.)| MM (2 digits), M (1 or 2 digits)
//              | NNN (abbr.)        |
// Day of Month | dd (2 digits)      | d (1 or 2 digits)
// Day of Week  | EE (name)          | E (abbr)
// Hour (1-12)  | hh (2 digits)      | h (1 or 2 digits)
// Hour (0-23)  | HH (2 digits)      | H (1 or 2 digits)
// Hour (0-11)  | KK (2 digits)      | K (1 or 2 digits)
// Hour (1-24)  | kk (2 digits)      | k (1 or 2 digits)
// Minute       | mm (2 digits)      | m (1 or 2 digits)
// Second       | ss (2 digits)      | s (1 or 2 digits)
// AM/PM        | a                  |
//
// NOTE THE DIFFERENCE BETWEEN MM and mm! Month=MM, not mm!
// Examples:
//  "MMM d, y" matches: January 01, 2000
//                      Dec 1, 1900
//                      Nov 20, 00
//  "M/d/yy"   matches: 01/20/00
//                      9/2/00
//  "MMM dd, yyyy hh:mm:ssa" matches: "January 01, 2000 12:30:45AM"
// ------------------------------------------------------------------

var MONTH_NAMES=new Array('January','February','March','April','May','June','July','August','September','October','November','December','Jan','Feb','Mar','Apr','May','Jun','Jul','Aug','Sep','Oct','Nov','Dec');
var DAY_NAMES=new Array('Sunday','Monday','Tuesday','Wednesday','Thursday','Friday','Saturday','Sun','Mon','Tue','Wed','Thu','Fri','Sat');
function LZ(x) {return(x<0||x>9?"":"0")+x}

// ------------------------------------------------------------------
// isDate ( date_string, format_string )
// Returns true if date string matches format of format string and
// is a valid date. Else returns false.
// It is recommended that you trim whitespace around the value before
// passing it to this function, as whitespace is NOT ignored!
// ------------------------------------------------------------------
function isDate(val,format) {
	var date=getDateFromFormat(val,format);
	if (date==0) { return false; }
	return true;
	}

// -------------------------------------------------------------------
// compareDates(date1,date1format,date2,date2format)
//   Compare two date strings to see which is greater.
//   Returns:
//   1 if date1 is greater than date2
//   0 if date2 is greater than date1 of if they are the same
//  -1 if either of the dates is in an invalid format
// -------------------------------------------------------------------
function compareDates(date1,dateformat1,date2,dateformat2) {
	var d1=getDateFromFormat(date1,dateformat1);
	var d2=getDateFromFormat(date2,dateformat2);
	if (d1==0 || d2==0) {
		return -1;
		}
	else if (d1 > d2) {
		return 1;
		}
	return 0;
	}

// ------------------------------------------------------------------
// formatDate (date_object, format)
// Returns a date in the output format specified.
// The format string uses the same abbreviations as in getDateFromFormat()
// ------------------------------------------------------------------
function formatDate(date,format) {
	format=format+"";
	var result="";
	var i_format=0;
	var c="";
	var token="";
	var y=date.getYear()+"";
	var M=date.getMonth()+1;
	var d=date.getDate();
	var E=date.getDay();
	var H=date.getHours();
	var m=date.getMinutes();
	var s=date.getSeconds();
	var yyyy,yy,MMM,MM,dd,hh,h,mm,ss,ampm,HH,H,KK,K,kk,k;
	// Convert real date parts into formatted versions
	var value=new Object();
	if (y.length < 4) {y=""+(y-0+1900);}
	value["y"]=""+y;
	value["yyyy"]=y;
	value["yy"]=y.substring(2,4);
	value["M"]=M;
	value["MM"]=LZ(M);
	value["MMM"]=MONTH_NAMES[M-1];
	value["NNN"]=MONTH_NAMES[M+11];
	value["d"]=d;
	value["dd"]=LZ(d);
	value["E"]=DAY_NAMES[E+7];
	value["EE"]=DAY_NAMES[E];
	value["H"]=H;
	value["HH"]=LZ(H);
	if (H==0){value["h"]=12;}
	else if (H>12){value["h"]=H-12;}
	else {value["h"]=H;}
	value["hh"]=LZ(value["h"]);
	if (H>11){value["K"]=H-12;} else {value["K"]=H;}
	value["k"]=H+1;
	value["KK"]=LZ(value["K"]);
	value["kk"]=LZ(value["k"]);
	if (H > 11) { value["a"]="PM"; }
	else { value["a"]="AM"; }
	value["m"]=m;
	value["mm"]=LZ(m);
	value["s"]=s;
	value["ss"]=LZ(s);
	while (i_format < format.length) {
		c=format.charAt(i_format);
		token="";
		while ((format.charAt(i_format)==c) && (i_format < format.length)) {
			token += format.charAt(i_format++);
			}
		if (value[token] != null) { result=result + value[token]; }
		else { result=result + token; }
		}
	return result;
	}
	
// ------------------------------------------------------------------
// Utility functions for parsing in getDateFromFormat()
// ------------------------------------------------------------------
function _isInteger(val) {
	var digits="1234567890";
	for (var i=0; i < val.length; i++) {
		if (digits.indexOf(val.charAt(i))==-1) { return false; }
		}
	return true;
	}
function _getInt(str,i,minlength,maxlength) {
	for (var x=maxlength; x>=minlength; x--) {
		var token=str.substring(i,i+x);
		if (token.length < minlength) { return null; }
		if (_isInteger(token)) { return token; }
		}
	return null;
	}
	
// ------------------------------------------------------------------
// getDateFromFormat( date_string , format_string )
//
// This function takes a date string and a format string. It matches
// If the date string matches the format string, it returns the 
// getTime() of the date. If it does not match, it returns 0.
// ------------------------------------------------------------------
function getDateFromFormat(val,format) {
	val=val+"";
	format=format+"";
	var i_val=0;
	var i_format=0;
	var c="";
	var token="";
	var token2="";
	var x,y;
	var now=new Date();
	var year=now.getYear();
	var month=now.getMonth()+1;
	var date=1;
	var hh=now.getHours();
	var mm=now.getMinutes();
	var ss=now.getSeconds();
	var ampm="";
	
	while (i_format < format.length) {
		// Get next token from format string
		c=format.charAt(i_format);
		token="";
		while ((format.charAt(i_format)==c) && (i_format < format.length)) {
			token += format.charAt(i_format++);
			}
		// Extract contents of value based on format token
		if (token=="yyyy" || token=="yy" || token=="y") {
			if (token=="yyyy") { x=4;y=4; }
			if (token=="yy")   { x=2;y=2; }
			if (token=="y")    { x=2;y=4; }
			year=_getInt(val,i_val,x,y);
			if (year==null) { return 0; }
			i_val += year.length;
			if (year.length==2) {
				if (year > 70) { year=1900+(year-0); }
				else { year=2000+(year-0); }
				}
			}
		else if (token=="MMM"||token=="NNN"){
			month=0;
			for (var i=0; i<MONTH_NAMES.length; i++) {
				var month_name=MONTH_NAMES[i];
				if (val.substring(i_val,i_val+month_name.length).toLowerCase()==month_name.toLowerCase()) {
					if (token=="MMM"||(token=="NNN"&&i>11)) {
						month=i+1;
						if (month>12) { month -= 12; }
						i_val += month_name.length;
						break;
						}
					}
				}
			if ((month < 1)||(month>12)){return 0;}
			}
		else if (token=="EE"||token=="E"){
			for (var i=0; i<DAY_NAMES.length; i++) {
				var day_name=DAY_NAMES[i];
				if (val.substring(i_val,i_val+day_name.length).toLowerCase()==day_name.toLowerCase()) {
					i_val += day_name.length;
					break;
					}
				}
			}
		else if (token=="MM"||token=="M") {
			month=_getInt(val,i_val,token.length,2);
			if(month==null||(month<1)||(month>12)){return 0;}
			i_val+=month.length;}
		else if (token=="dd"||token=="d") {
			date=_getInt(val,i_val,token.length,2);
			if(date==null||(date<1)||(date>31)){return 0;}
			i_val+=date.length;}
		else if (token=="hh"||token=="h") {
			hh=_getInt(val,i_val,token.length,2);
			if(hh==null||(hh<1)||(hh>12)){return 0;}
			i_val+=hh.length;}
		else if (token=="HH"||token=="H") {
			hh=_getInt(val,i_val,token.length,2);
			if(hh==null||(hh<0)||(hh>23)){return 0;}
			i_val+=hh.length;}
		else if (token=="KK"||token=="K") {
			hh=_getInt(val,i_val,token.length,2);
			if(hh==null||(hh<0)||(hh>11)){return 0;}
			i_val+=hh.length;}
		else if (token=="kk"||token=="k") {
			hh=_getInt(val,i_val,token.length,2);
			if(hh==null||(hh<1)||(hh>24)){return 0;}
			i_val+=hh.length;hh--;}
		else if (token=="mm"||token=="m") {
			mm=_getInt(val,i_val,token.length,2);
			if(mm==null||(mm<0)||(mm>59)){return 0;}
			i_val+=mm.length;}
		else if (token=="ss"||token=="s") {
			ss=_getInt(val,i_val,token.length,2);
			if(ss==null||(ss<0)||(ss>59)){return 0;}
			i_val+=ss.length;}
		else if (token=="a") {
			if (val.substring(i_val,i_val+2).toLowerCase()=="am") {ampm="AM";}
			else if (val.substring(i_val,i_val+2).toLowerCase()=="pm") {ampm="PM";}
			else {return 0;}
			i_val+=2;}
		else {
			if (val.substring(i_val,i_val+token.length)!=token) {return 0;}
			else {i_val+=token.length;}
			}
		}
	// If there are any trailing characters left in the value, it doesn't match
	if (i_val != val.length) { return 0; }
	// Is date valid for month?
	if (month==2) {
		// Check for leap year
		if ( ( (year%4==0)&&(year%100 != 0) ) || (year%400==0) ) { // leap year
			if (date > 29){ return 0; }
			}
		else { if (date > 28) { return 0; } }
		}
	if ((month==4)||(month==6)||(month==9)||(month==11)) {
		if (date > 30) { return 0; }
		}
	// Correct hours value
	if (hh<12 && ampm=="PM") { hh=hh-0+12; }
	else if (hh>11 && ampm=="AM") { hh-=12; }
	var newdate=new Date(year,month-1,date,hh,mm,ss);
	return newdate.getTime();
	}

// ------------------------------------------------------------------
// parseDate( date_string [, prefer_euro_format] )
//
// This function takes a date string and tries to match it to a
// number of possible date formats to get the value. It will try to
// match against the following international formats, in this order:
// y-M-d   MMM d, y   MMM d,y   y-MMM-d   d-MMM-y  MMM d
// M/d/y   M-d-y      M.d.y     MMM-d     M/d      M-d
// d/M/y   d-M-y      d.M.y     d-MMM     d/M      d-M
// A second argument may be passed to instruct the method to search
// for formats like d/M/y (european format) before M/d/y (American).
// Returns a Date object or null if no patterns match.
// ------------------------------------------------------------------
function parseDate(val) {
	var preferEuro=(arguments.length==2)?arguments[1]:false;
	generalFormats=new Array('y-M-d','MMM d, y','MMM d,y','y-MMM-d','d-MMM-y','MMM d');
	monthFirst=new Array('M/d/y','M-d-y','M.d.y','MMM-d','M/d','M-d');
	dateFirst =new Array('d/M/y','d-M-y','d.M.y','d-MMM','d/M','d-M');
	var checkList=new Array('generalFormats',preferEuro?'dateFirst':'monthFirst',preferEuro?'monthFirst':'dateFirst');
	var d=null;
	for (var i=0; i<checkList.length; i++) {
		var l=window[checkList[i]];
		for (var j=0; j<l.length; j++) {
			d=getDateFromFormat(val,l[j]);
			if (d!=0) { return new Date(d); }
			}
		}
	return null;
	}
	
	/**
	 * Parse an ISO 8601 date/time and return a new date object.
	 *
	 * ISO 8601 Format: {@link http://www.cl.cam.ac.uk/~mgk25/iso-time.html}
	 *		yyyy-MM-ddTHH:mm:ssZ
	 * Example:
	 *		2007-01-25T15:30:00-4:00
	 * 
	 * @param string dateString
	 * @return object Date
	 * @access public
	 * @since 2/12/07
	 */
	Date.fromISO8601 = function (dateString) {
		if (!dateString)
			throw new Error("Empty date string, '" + dateString +"' passed.");
		
		/*********************************************************
		 * Since Javascript doesn't allow ignoring whitespace
		 * The following expression is condensed in the actual
		 * code.
		 *********************************************************
/
^											# Start of the line

#-----------------------------------------------------------------------------
	(?:										# The date component
		([0-9]{4})							# Four-digit year
		
		[\-\/:]?							# Optional Hyphen, slash, or colon delimiter
		
		(?:									# Two-digit month
			(
			(?:  0[1-9])
			|
			(?:  1[0-2])
			)
		
			[\-\/:]?						# Optional Hyphen, slash, or colon delimiter
			
			(?:									# Two-digit day
				(
				(?:  0[1-9])
				|
				(?:  (?: 1|2)[0-9])
				|
				(?:  3[0-1])
				)
				
		
		
				[\sT]?									# Optional delimiter
			
			#-----------------------------------------------------------------------------		
				(?:										# The time component
				
					(									# Two-digit hour
						(?:  [0-1][0-9])
						|
						(?: 2[0-4])
					)
					
					(?:
						:?									# Optional Colon
						
						([0-5][0-9])?						# Two-digit minute
						
						(?:
							:?									# Optional Colon
							
							(									# Two-digit second 
								[0-5][0-9]
								(?: \.[0-9]+)?						# followed by an optional decimal.
							)?
					
					#-----------------------------------------------------------------------------
							(									# Offset component
							
								Z								# Zero offset (UTC)
								|								# OR
								(?:								# Offset from UTC
									([+\-])						# Sign of the offset
								
									(							# Two-digit offset hour
										(?:  [0-1][0-9])
										|
										(?:  2[0-4])
									)			
						
									:?							# Optional Colon
									
									([0-5][0-9])?				# Two-digit offset minute
								)
							)?
						)?
					)?
				)?
			)?
		)?
	)?

$
/

		*********************************************************/
		
		var regex = /^(?:([0-9]{4})[\-\/:]?(?:((?:0[1-9])|(?:1[0-2]))[\-\/:]?(?:((?:0[1-9])|(?:(?:1|2)[0-9])|(?:3[0-1]))[\sT]?(?:((?:[0-1][0-9])|(?:2[0-4]))(?::?([0-5][0-9])?(?::?([0-5][0-9](?:\.[0-9]+)?)?(Z|(?:([+\-])((?:[0-1][0-9])|(?:2[0-4])):?([0-5][0-9])?))?)?)?)?)?)?)?$/;
		
		var matches = dateString.match(regex);
		if (!matches.length)
			throw new Error("Could not match date string, '" + dateString +"', to ISO 8601 format: .");
		
		// Matches:
		//     [0] => 2005-05-23T15:25:10-04:00
		//     [1] => 2005
		//     [2] => 05
		//     [3] => 23
		//     [4] => 15
		//     [5] => 25
		//     [6] => 10
		//     [7] => -04:00
		//     [8] => -
		//     [9] => 04
		//     [10] => 00
		
		if (typeof(matches[1]) == 'string' && matches[1].length)
			var year = parseInt(matches[1]);
		else
			throw new Error("No year specified");
		
		if (typeof(matches[2]) == 'string' && matches[2].length)
			var month = parseInt(matches[2]) - 1;
		else
			throw new Error("No month specified");
		
		if (typeof(matches[3]) == 'string' && matches[3].length)
			var day = parseInt(matches[3]);
		else
			throw new Error("No day specified");
		
		if (typeof(matches[4]) == 'string' && matches[4].length)
			var hour = parseInt(matches[4]);
		else
			var hour = 0;
			
		if (typeof(matches[5]) == 'string' && matches[5].length)
			var minute = parseInt(matches[5]);
		else
			var minute = 0;
		
		if (typeof(matches[6]) == 'string' && matches[6].length)
			var second = parseInt(matches[6]);
		else
			var second = 0;
		
		// A value of Z indicates UTC time or 'zero-offset'0
		if (typeof(matches[7]) == 'string' && matches[7] == 'Z') {
			
		} else if (typeof(matches[7]) == 'string' && matches[7].length) {
			if (matches[8] == '-' || matches[8] == '+')
				var offsetSign = matches[8];
			else
				throw new Error("Invalid timezone offset sign, '" + matches[8] + "'.");
			
			if (typeof(matches[9]) == 'string' && matches[9].length) {
				var offsetHour = parseInt(matches[9]);
				
				if (offsetSign == '-') {
					hour = hour + offsetHour;
				} else {
					hour = hour - offsetHour;
				}
			} else
				throw new Error("Invalid timezone offset hour, '" + matches[9] + "'.");
			
			if (typeof(matches[10]) == 'string' && matches[10].length) {
				var offsetMinute = parseInt(matches[10]);
				
				if (offsetSign == '-') {
					minute = minute + offsetMinute;
				} else {
					minute = minute - offsetMinute;
				}
			}
		}
		
// 		alert("Year:\t" + year
// 			+ "\nMonth:\t" + month
// 			+ "\nDay:\t" + day
// 			+ "\nHour:\t" + hour
// 			+ "\nMinute:\t" + minute
// 			+ "\nSecond:\t" + second);
		
		return new Date(Date.UTC(year, month, day, hour, minute, second));
	}
	
	/**
	 * Return a string with the format specified
	 * 
	 * @param string format
	 * @return string
	 * @access public
	 * @since 2/12/07
	 */
	Date.prototype.toFormatedString = function (format) {
		return formatDate(this, format);
	}
	
/*********************************************************
 * Date Functions - End
 *********************************************************/
 
 /**
  * Add a javascript script url to the head of the document.
  *
  * By Moshe Moskowitz on 06/21/2002
  * http://www.codehouse.com/javascript/articles/external/
  * 
  * @return void
  * @access public
  * @since 2/21/07
  */
function loadScript(url) {
	var e = document.createElement("script");
	e.src = url;
	e.type="text/javascript";
	document.getElementsByTagName("head")[0].appendChild(e);
}

/**
 * 
 * 
 * @param <##>
 * @return <##>
 * @access public
 * @since 4/30/07
 */
function isValidMouseOut (source, event) {
	
// 	var debug = '';
// 	for (var key in event) {
// 		debug += "\n" + key + "\t" + event[key];
// 	}
// 	alert(debug);
// 	

	// IE sometimes doesn't include a target in the event.
	if (!event.target) {
		return true;
	}
	
	// opened SELECT menus in dialogs also trigger invalid mouse-out events
	// in Firefox
	if (event.target.nodeName == "SELECT" && event.target.nodeName == "OPTION") {
		return false;
	}
	
	return true;
}