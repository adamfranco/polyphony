<?
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout.act.php,v 1.5 2005/06/07 13:41:54 adamfranco Exp $
 */
 
$authN =& Services::getService("AuthN");

// dethenticate. :-)
$authN->destroyAuthentication();

// Send us back to where we were
$currentPathInfo = array();
for ($i = 2; $i < count($harmoni->pathInfoParts); $i++) {
	$currentPathInfo[] = $harmoni->pathInfoParts[$i];
}

$harmoni->history->goBack("polyphony/login");