<?php
/**
 * @since 5/14/08
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This class performs the work of the conditional-GET system to be used by other
 * actions since we don't have multiple-inheritance to add in this functionality.
 * 
 * @since 5/14/08
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class ConditionalGetHelper {
		
	/**
	 * Constructor. Takes three callback functions for accessing time settings
	 * and outputing data
	 * 
	 * @param mixed $outputDataCallback			A callback function that will output data
	 *											and then exit. Called when the cache needs
	 *											to be refreshed.
	 * @param mixed $getModifiedTimeCallback	A callback function that will return
	 *											a DateAndTime object that represents the
	 *											time when the current item was last modified.
	 * @param mixed $getValidDurationCallback 	A callback function that will return
	 *											a Duration object that represents the
	 *											length of time before the browser should
	 *											expire its cache of the current item.
	 * @return null
	 * @access public
	 * @since 5/14/08
	 */
	public function __construct ($outputDataCallback, $getModifiedTimeCallback, $getValidDurationCallback) {
		$this->outputDataCallback = $outputDataCallback;
		$this->getModifiedTimeCallback = $getModifiedTimeCallback;
		$this->getValidDurationCallback = $getValidDurationCallback;
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	public function execute () {
		// Override the default 'no_cache' headers send with the session_limiter
		// option at session-start
		header('Cache-Control: private');
		header('Pragma: ');
		
		// Firefox will display the cached version of an image while it waits to 
		// get a new version or a 304. Safari and IE however, will not show an image
		// from cache until they get a 304. 
		//
		// If you really do want to force the  browser to check for a 304 every 
		// page load, then set a negative Duration of a few years so that clock
		// skew will not have an impact.
		// 
		// To avoid checking for a 304 every load, use a reasonable positive duration.
		// For something like theme images that do not have any authorizations associated
		// with them,  a few hours or days would be fine. 
		// To restrict access on public computers to authorizable files after a user
		// has logged out, use a shorter duration of a few minutes. While another
		// user coming to the computer after logout might see authorized images via cache,
		// they could accomplish the same thing via direct viewing of the browser cache anyway.
		header('Expires: '.$this->getTimestampString(DateAndTime::now()->plus($this->getValidDuration())));

		// Send our Last-Modified and ETag headers as they will always be needed.
		$lastModified = $this->getLastModifiedTime();
		$lastModifiedString = $this->getTimestampString($lastModified);
		$eTag = '"'.md5($lastModifiedString).'"';
		header("Last-Modified: ".$lastModifiedString);
		header('ETag: '.$eTag);
		
		
		// Send a HTTP 304 Not Modified Header if the data hasn't changed.
		try {
			if (!$this->changed($lastModified, $eTag)) {
				header('HTTP/1.0 304 Not Modified');
				exit;
			}
		} catch (OperationFailedException $e) {
			HarmonErrorHandler::logException($e);
		}
		
		// Output the content
		call_user_func($this->outputDataCallback);
	}
	
	/**
	 * Answer the later of the modified date and time and the user-login.
	 * 
	 * @return object DateAndTime
	 * @access private
	 * @since 5/13/08
	 */
	private function getLastModifiedTime () {
		$authN = Services::getService('AuthN');
		$userId = $authN->getFirstUserId();
		if (!isset($_SESSION['COND_GET_USER']) || !$userId->isEqual($_SESSION['COND_GET_USER']['userId'])) {
			$_SESSION['COND_GET_USER'] = array('userId' => $userId, 'login_time' => DateAndTime::now());
		}
		
		// Get the time from the call back and validate it.
		$lastModified = call_user_func($this->getModifiedTimeCallback);
		ArgumentValidator::validate($lastModified, ExtendsValidatorRule::getRule('DateAndTime'));
		
		$lastModified = $lastModified->max($_SESSION['COND_GET_USER']['login_time']);
		
		// Ensure that it isn't in the future
		if ($lastModified->isGreaterThan(DateAndTime::now()))
			return DateAndTime::now();
		else
			return $lastModified;
	}
	
	/**
	 * Answer the length of time that this item should be cached for before checking
	 * for a 304.
	 * 
	 * @return object Duration
	 * @access private
	 * @since 5/14/08
	 */
	private function getValidDuration () {
		// Get the time from the call back and validate it.
		$valid = call_user_func($this->getValidDurationCallback);
		ArgumentValidator::validate($valid, ExtendsValidatorRule::getRule('Duration'));
		return $valid;
	}
	
	/**
	 * Answer true if the content has modified since the cache was last 
	 * updated, false otherwise.
	 *
	 * Based on info from:
	 *		Simon Willison - http://simonwillison.net/2003/Apr/23/conditionalGet/
	 *		http://fishbowl.pastiche.org/archives/001132.html
	 *		
	 * 
	 * @param object DateAndTime $timestamp
	 * @param string $eTag
	 * @return boolean
	 * @access private
	 * @since 5/13/08
	 */
	private function changed (DateAndTime $timestamp, $eTag) {
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
			$clientTimestamp = DateAndTime::fromString(stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']));
		if (isset($_SERVER['HTTP_IF_NONE_MATCH'])) {
			$clientETag = trim($_SERVER['HTTP_IF_NONE_MATCH']);
			$eTag = trim($eTag);
		}
		
		// Check the HTTP 1.1 ETag/If None Match parts first.
		if (isset($clientETag) && $clientETag == $eTag) {
			return false;
			
		}
		
		// Check the HTTP 1.0 Modification date
		if (isset($clientTimestamp) && $timestamp->isLessThanOrEqual($clientTimestamp)) {
			return false;
		}
		
		return true;
	}
	
	/**
	 * Answer a formatted Last-ModifiedString.
	 * 
	 * @param object DateAndTime $timestamp
	 * @return string
	 * @access private
	 * @since 5/13/08
	 */
	private function getTimestampString (DateAndTime $timestamp) {
		$timestamp = $timestamp->asUTC();
		return $timestamp->dayOfWeekAbbreviation().', '
			.sprintf('%02d', $timestamp->dayOfMonth()).' '
			.$timestamp->monthAbbreviation().' '
			.$timestamp->year().' '
			.sprintf('%02d', $timestamp->hour24()).':'
			.sprintf('%02d', $timestamp->minute()).':'
			.sprintf('%02d', $timestamp->second()).' '
			.'GMT';
	}
	
}

?>