<?
/**
 * @package polyphony.modules.authentication
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
$defaultTextDomain = textdomain();
textdomain("polyphony");


$text = "<p>";
$text .= _("Your are not logged in.");
$text .= " <a href='".MYURL."/auth/login/".implode("/",$currentPathInfo)."'>";
$text .= _("Click here to log in.");
$text .= "</a></p>";

// text
$introText =& new Block($text, 2);
$centerPane->addComponent($introText, null, null, CENTER, CENTER);

// go back to the default text domain
textdomain($defaultTextDomain);

// return the main layout.
return $mainScreen;