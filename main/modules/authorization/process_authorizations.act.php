<?

/**
* process_authorizations.act.php
* This action will create or delete authorizations as specified by edit_authorizations.act.php
* 11/18/04 Ryan Richards
* copyright 2004 MIddlebury College
*/



$shared =& Services::getService("Shared");

printpre($_REQUEST);
printpre($harmoni->pathInfoparts);

// Process authorizations








// Send us back to where we were (edit_authorizations.act.php)
$currentPathInfo = array_slice($harmoni->pathInfoParts, 6);

header("Location: ".MYURL."/".implode("/",$currentPathInfo)."?selection=".$_GET['selection']);
