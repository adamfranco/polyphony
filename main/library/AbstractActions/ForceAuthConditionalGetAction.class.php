<?php
/**
 * @since 5/13/08
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(dirname(__FILE__)."/ForceAuthAction.class.php");

/**
 * <##>
 * 
 * @since 5/13/08
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
abstract class ForceAuthConditionalGetAction
	extends ForceAuthAction
{
		
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	final public function execute () {
		// Force Authentication piece
		if (!$this->isAuthorizedToExecute())
			$this->getUnauthorizedMessage();
		
		try {
			$lastModified = $this->getLastModifiedString();
			
			// Override the default 'no_cache' headers send with the session_limiter
			// option at session-start
			header('Cache-Control: private');
			header('Pragma: ');
			header('Expires: ');
	// 		header('Expires: '.$this->getTimestampString(DateAndTime::now()->plus(Duration::withSeconds(60))));
	
			// Send our Last-Modified and ETag headers as they will always be needed.
			
			header("Last-Modified: ".$lastModified);
			header('ETag: "'.md5($lastModified).'"');
			
			
			// Send a HTTP 304 Not Modified Header if the data hasn't changed.
			try {
				if (!$this->changed($this->getLastModifiedTime())) {
					header('HTTP/1.0 304 Not Modified');
					exit;
				} 
			} catch (OperationFailedException $e) {
				HarmonErrorHandler::logException($e);
			}
		}
		// if the action doesn't support getting the modification date, just output
		// the content
		catch (UnimplementedException $e) {
		}
		
		// Output the content
		$this->outputContent();
	}
	
	/**
	 * Answer the last-modified timestamp for this action/id.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 5/13/08
	 */
	abstract public function getModifiedDateAndTime ();
	
	/**
	 * Output the content
	 * 
	 * @return null
	 * @access public
	 * @since 5/13/08
	 */
	abstract public function outputContent ();
	
	/**
	 * Answer a last-modified string for the request
	 * 
	 * @return string
	 * @access private
	 * @since 5/13/08
	 */
	private function getLastModifiedString () {
		return $this->getTimestampString($this->getLastModifiedTime());
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
		
		return $this->getModifiedDateAndTime()->max($_SESSION['COND_GET_USER']['login_time']);
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
	 * @return boolean
	 * @access private
	 * @since 5/13/08
	 */
	private function changed (DateAndTime $timestamp) {
		if (isset($_SERVER['HTTP_IF_MODIFIED_SINCE']))
			$clientTimestamp = DateAndTime::fromString(stripslashes($_SERVER['HTTP_IF_MODIFIED_SINCE']));
		if (isset($_SERVER['HTTP_IF_NONE_MATCH']))
			$clientETag = trim($_SERVER['HTTP_IF_NONE_MATCH'], '"');
		
		// Check the HTTP 1.1 ETag/If None Match parts first.
		$eTag = md5($this->getTimestampString($timestamp));
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
			.$timestamp->hour24().':'
			.$timestamp->minute().':'
			.$timestamp->second().' '
			.'GMT';
	}
}

?>