<?php
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: username_password_form.act.php,v 1.7 2005/06/07 12:29:15 gabeschine Exp $
 */

require_once(HARMONI."GUIManager/Components/Block.class.php");


// Set our textdomain
$defaultTextDomain = textdomain("polyphony");
ob_start();

$harmoni->request->startNamespace("harmoni-authentication");

$action = $harmoni->history->getReturnURL("polyphony/authentication");
$usernameField = $harmoni->request->getName("username");
$passwordField = $harmoni->request->getName("password");
$usernameText = _("Username");
$passwordText = _("Password");
print<<<END

<center><form name='login' action='$action' method='post'>
	$usernameText: <input type='text' name='$usernameField' />
	<br />$passwordText: <input type='password' name='$passwordField' />
	<br /><input type='submit' />
</form></center>

END;


$introText =& new Block(ob_get_contents(), 2);
ob_end_clean();

$harmoni->request->endNamespace();

// go back to the default text domain
textdomain($defaultTextDomain);

// return the main layout.
return $introText;