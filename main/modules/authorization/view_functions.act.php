<?php
/**
 * @package polyphony.modules.authorization
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: view_functions.act.php,v 1.5 2005/04/11 20:03:08 adamfranco Exp $
 */

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Layout
$yLayout =& new YLayout();
$actionRows =& new Container($yLayout, OTHER, 1);
$centerPane->add($actionRows, null, null, CENTER, CENTER);

/// Intro
$introHeader =& new Heading(_("View Functions"), 2);
$actionRows->add($introHeader, "100%", null, LEFT, CENTER);

$authZManager =& Services::getService("AuthZ");


// Get the function type and print this info in two headers.
$functionTypes =& $authZManager->getFunctionTypes();
while ($functionTypes->hasNext()) {
	$functionType =& $functionTypes->next();
	ob_start();
	print "<strong>".$functionType->getAuthority()." :: ";
	print $functionType->getDomain()." :: ";
	print $functionType->getKeyword()."</strong> ";
	$typeLayout =& new Block(ob_get_contents(), 2);
	ob_end_clean();
	
	ob_start();
	print "<em>".$functionType->getDescription()."</em>";
	$descriptionLayout =& new Block(ob_get_contents(), 2);
	ob_end_clean();
	
	$actionRows->add($typeLayout, "100%", null, LEFT, CENTER);
	$actionRows->add($descriptionLayout, "100%", null, LEFT, CENTER);
	
	$functionLayout =& new Container($yLayout, Block, 4);
	$actionRows->add($functionLayout, "100%", null, LEFT, CENTER);
	
	$actionRows->add(new Block(" &nbsp; <br /> &nbsp; <br /> &nbsp;",2), "100%", null, LEFT, CENTER);
	
// 	Now get all the functions of the specified type
	$functions =& $authZManager->getFunctions($functionType);
	while ($functions->hasNext()) {
		$function =& $functions->next();
		
	//	Store function info and create layouts.
	//	Types consist of {domain :: authority :: type :: description}
	//	Functions consist of {Id :: displayName :: description}
		
		ob_start();
		$id =& $function->getId();
		print $id->getIdString()." - ";
		print "<strong>".$function->getReferenceName()." - ";
		print "</strong>".$function->getDescription();
		
		$functionLayout->add(new Block(ob_get_contents(),2), "100%", null, LEFT, CENTER);
		ob_end_clean();
        
	}
}



// Return the main layout.
return $mainScreen;