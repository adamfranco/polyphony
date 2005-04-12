<?php
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: username_password_form.act.php,v 1.5 2005/04/12 21:18:05 adamfranco Exp $
 */

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');


// Get info to send back to where we were on login
$currentPathInfo = array();
for ($i = 2; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
} 

// Set our textdomain
$defaultTextDomain = textdomain(NULL);
textdomain("polyphony");
ob_start();



$action = MYURL."/".implode("/",array_slice($harmoni->pathInfoParts, 2));
if (count($_GET)) {
	$action .= "?";
	foreach ($_GET as $name => $value) {
		$action .= "&".$name."=".$value;
	}
}
$usernameText = _("Username");
$passwordText = _("Password");
print<<<END

<center><form name='login' action='$action' method='post'>
	$usernameText: <input type='text' name='username' />
	<br />$passwordText: <input type='password' name='password' />
	<br /><input type='submit' />
</form></center>

END;


$introText =& new Block(ob_get_contents(), 2);
ob_end_clean();
$centerPane->add($introText, null, null, CENTER, CENTER);

// go back to the default text domain
textdomain($defaultTextDomain);

// return the main layout.
return $mainScreen;