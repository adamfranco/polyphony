<?php
/**
 * @since 9/23/08
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

/**
 * This class contains helper functions for working with userdata.
 * 
 * @since 9/23/08
 * @package polyphony.user
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class UserDataHelper {
	
	private static $headJsWritten = false;
	/**
	 * Write Javascript needed for supporting user-data to the page head.
	 * 
	 * @return void
	 * @access public
	 * @since 9/23/08
	 * @static
	 */
	public static function writeHeadJs () {
		if (self::$headJsWritten) 
			return;
		
		$harmoni = Harmoni::instance();		

		$outputHandler = $harmoni->getOutputHandler();
		$outputHandler->setHead($outputHandler->getHead().self::getHeadJs());
		
		self::$headJsWritten = true;
	}
	
	/**
	 * Answer the head JS
	 * 
	 * @return string
	 * @access public
	 * @since 1/13/09
	 * @static
	 */
	public static function getHeadJs () {
		ob_start();
		print "\n\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."/javascript/UserData.js'></script>";
		
		print "
		<script type='text/javascript'>
		// <![CDATA[
		
			var userData = UserData.instance();
";
		$userData = UserData::instance();
		
		foreach ($userData->getPreferenceKeys() as $key) {
			print "\n\t\t\tuserData.prefs['$key'] = '".addslashes($userData->getPreference($key))."';";
		}
		

		print "
		// ]]>
		</script>
";
		return ob_get_clean();
	}
	
}

?>