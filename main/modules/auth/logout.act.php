<?
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout.act.php,v 1.6 2005/06/07 21:35:56 adamfranco Exp $
 */
 
$authN =& Services::getService("AuthN");

// dethenticate. :-)
$authN->destroyAuthentication();

// Send us back to where we were
$harmoni->history->goBack("polyphony/login");