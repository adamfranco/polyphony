<?php
/**
 * This is just going to ensure that we haven't added any protected info to
 * the layout already. We will start a new execution cycle with everything fresh.
 *
 * @package polyphony.modules.authentication
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: fail_redirect.act.php,v 1.4 2005/04/07 17:07:53 adamfranco Exp $
 */

header("Location: ".MYURL."/auth/fail/".implode("/",$harmoni->pathInfoParts));