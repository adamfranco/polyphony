<?php

/**
 * Polyphony include file. Sets up Polyphony environment and includes necessary
 * classes.
 *
 * @package polyphony
 * @version $Id: polyphony.inc.php,v 1.1.1.1 2003/08/05 20:59:31 gabeschine Exp $
 * @copyright 2003 
 **/

define(POLYPHONY,dirname(__FILE__)."/");

/**
 * Include some required classes. 
 **/
require_once(POLYPHONY."core/api/shareable/Shareable.abstract.php");

?>