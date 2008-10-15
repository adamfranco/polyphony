<?php
/**
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: username_password_form.act.php,v 1.12 2007/09/19 14:04:53 adamfranco Exp $
 */

require_once(HARMONI."GUIManager/Components/Block.class.php");
require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * 
 * @package polyphony.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: username_password_form.act.php,v 1.12 2007/09/19 14:04:53 adamfranco Exp $
 */
class username_password_formAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return TRUE;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Login");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$actionRows =$this->getActionRows();
		$harmoni = Harmoni::instance();

		// Set our textdomain
		$defaultTextDomain = textdomain("polyphony");
		ob_start();
		
		$harmoni->request->startNamespace("harmoni-authentication");
		
		$action = $harmoni->history->getReturnURL("polyphony/authentication");
		$usernameField = $harmoni->request->getName("username");
		$passwordField = $harmoni->request->getName("password");
		$usernameText = _("Username/Email");
		$passwordText = _("Password");
		$submitLabel = _('Log In');
		print<<<END
		
		<center><form name='login' action='$action' method='post'>
			$usernameText: <input type='text' name='$usernameField' />
			<br />$passwordText: <input type='password' name='$passwordField' />
			<br /><input type='submit' value='$submitLabel'/>
		</form></center>
		
END;
		
		
		$actionRows->add(new Block(ob_get_contents(), 2), "100%", null, CENTER, CENTER);
		ob_end_clean();
		
		$harmoni->request->endNamespace();
		
		// go back to the default text domain
		textdomain($defaultTextDomain);
	}
}