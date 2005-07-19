<?php
/**
 * @package polyphony.modules.language
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: change.act.php,v 1.6 2005/07/19 15:57:51 adamfranco Exp $
 */

// Set the new language
$langLoc =& Services::getService('Lang');
$harmoni->request->startNamespace("polyphony");
$langLoc->setLanguage($harmoni->request->get("language"));
$harmoni->request->endNamespace();

debug::output("Setting the language to ".$harmoni->request->get("polyphony/language"));
debug::output("SESSION: ".printpre($_SESSION, TRUE));

$harmoni->history->goBack("polyphony/language/change");