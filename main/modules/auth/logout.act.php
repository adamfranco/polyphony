<?
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout.act.php,v 1.4 2005/04/07 17:07:53 adamfranco Exp $
 */
 
$authN =& Services::getService("AuthN");

// dethenticate. :-)
$authN->destroyAuthentication();

// Send us back to where we were
$currentPathInfo = array();
for ($i = 2; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
}

header("Location: ".MYURL."/".implode("/",$currentPathInfo));