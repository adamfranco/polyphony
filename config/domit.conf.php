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
 * @version $Id: domit.conf.php,v 1.1 2006/06/02 16:00:28 cws-midd Exp $
 */
 
if (!defined("DOMIT"))
	define("DOMIT", POLYPHONY."/../domit_1_1/xml_domit_include.php");
