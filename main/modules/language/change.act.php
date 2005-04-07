<?php
/**
 * @package polyphony.modules.language
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change.act.php,v 1.3 2005/04/07 17:07:55 adamfranco Exp $
 */

// Set the new language
$langLoc =& Services::getService('Lang');
$langLoc->setLanguage($_REQUEST['language']);

debug::output("Setting the language to ".$_REQUEST['language']);
debug::output("SESSION: ".printpre($_SESSION, TRUE));

// Send us back to where we were
$currentPathInfo = array();
for ($i = 2; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
}

header("Location: ".MYURL."/".implode("/",$currentPathInfo));