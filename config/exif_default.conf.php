<?php

/**
 * Set up the EXIF location for EXIF metadata handling
 *
 * USAGE: Copy this file to exif.conf.php to set custom values.
 *
 * @package polyphony.config
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: exif_default.conf.php,v 1.2 2005/09/28 20:01:15 cws-midd Exp $
 */
 
if (!defined("EXIF"))
	define("EXIF", POLYPHONY."/../PHP_JPEG_Metadata_Toolkit_1.11/EXIF.php");
