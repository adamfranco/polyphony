<?php
/**
 * @since 8/9/06
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: cookies_required.act.php,v 1.2 2006/11/30 22:02:42 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");

/**
 * <##>
 * 
 * @since 8/9/06
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: cookies_required.act.php,v 1.2 2006/11/30 22:02:42 adamfranco Exp $
 */
class cookies_requiredAction {
		
	/**
	 * Execute this action
	 * 
	 * @return void
	 * @access public
	 * @since 8/9/06
	 */
	function &execute () {
		print _("<h2>Error: You must have cookies enabled in your browser.</h2>
			<ul >
				<li style='margin-bottom: 15px;'><strong>FireFox/Mozilla:</strong>
					<ol>
						<li>Open the FireFox Preferences
							<ul>
								<li>Windows: <strong>Tools</strong> -&gt; <strong>Options...</strong></li>
								<li>Mac: <strong>FireFox</strong> -&gt; <strong>Preferences...</strong></li>
							</ul>
						</li>
						<li>In the preferences window, go to <strong>Privacy</strong> -&gt; <strong>Cookies</strong></li>
						
						<li>Check the <strong>Allow sites to set Cookies/Enable Cookies</strong> box.</li>
						<li>Refresh this page at least once, this error should go away.</li>
					</ol>
				</li>
				
				<li style='margin-bottom: 15px;'><strong>Internet Explorer (IE):</strong>
					<ol>
						<li>Open the Internet Options: <strong>Tools</strong> -&gt; <strong>Internet Options</strong></li>
						<li>Click on the <strong>Privacy</strong> tab.</li>
						<li>Change the privacy setting to <strong>Medium High</strong> or less</li>
						<li>Refresh this page at least once, this error should go away.</li>
					</ol>
				</li>
				<li style='margin-bottom: 15px;'><strong>Safari:</strong>
					<ol>
						<li>Open the Safari Preferences: <strong>Safari</strong> -&gt; <strong>Preferences...</strong></li>
						<li>In the preferences window, go to the <strong>Security</strong> tab.</li>
						
						<li>Select <strong>Only from sites you navigate to</strong> option under <strong>Accept Cookies</strong>.</li>
						<li>Refresh this page at least once, this error should go away.</li>
					</ol>
				</li>
			</ul>");
		
		$result = ob_get_clean();
		return $result;
	}
	
}

?>