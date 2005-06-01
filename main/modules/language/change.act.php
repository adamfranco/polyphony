<?php
/**
 * @package polyphony.modules.language
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change.act.php,v 1.4 2005/06/01 19:33:51 gabeschine Exp $
 */

// Set the new language
$langLoc =& Services::getService('Lang');
$langLoc->setLanguage($harmoni->request->get("polyphony/language"));

debug::output("Setting the language to ".$harmoni->request->get("polyphony/language"));
debug::output("SESSION: ".printpre($_SESSION, TRUE));

$harmoni->history->goBack("polyphony/language/change");
//header("Location: ".urldecode($harmoni->request->get("return")));