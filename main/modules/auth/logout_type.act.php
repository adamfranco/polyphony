<?php
/**
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: logout_type.act.php,v 1.4 2005/06/02 20:18:07 adamfranco Exp $
 */
 
$authN =& Services::getService("AuthN");
$harmoni->request->startNamespace("polyphony");
$authType =& HarmoniType::stringToType(urldecode($harmoni->request->get("type")));
$harmoni->request->endNamespace();

// Try authenticating with this type
$authN->destroyAuthenticationForType($authType);

// Send us back to where we were
$harmoni->history->goBack("polyphony/login");