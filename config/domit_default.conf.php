<?php

/**
 * Set up the DOMIT location for XML handling
 *
 * USAGE: Copy this file to domit.conf.php to set custom values.
 *
 * @package polyphony.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: domit_default.conf.php,v 1.2 2005/09/28 20:01:15 cws-midd Exp $
 */
 
if (!defined("DOMIT"))
	define("DOMIT", POLYPHONY."/../domit/xml_domit_include.php");
