<?php
/**
 * @package polyphony.modules.authorization
 */

// Get the Layout compontents. See core/modules/moduleStructure.txt
// for more info. 
$harmoni->ActionHandler->execute("window", "screen");
$mainScreen =& $harmoni->getAttachedData('mainScreen');
$statusBar =& $harmoni->getAttachedData('statusBar');
$centerPane =& $harmoni->getAttachedData('centerPane');
 

// Our
$actionRows =& new RowLayout();
$centerPane->addComponent($actionRows, TOP, CENTER);

// Intro
$introHeader =& new SingleContentLayout(HEADING_WIDGET, 2);
$introHeader->addComponent(new Content(_("View Functions")));
$actionRows->addComponent($introHeader);

$authZManager =& Services::getService("AuthZ");




// $sharedManager =& Services::getService("Shared");
// 
// $qualifierHierarchyId =& $sharedManager->getId("673");
// $functionId =& $sharedManager->createId();
// $functionType =& new HarmoniType("Concerto", "Midd", "Use", "Functions for viewing and using shiznat.");
// $authZManager->createFunction($functionId, "Comment", "Add comments to this thing.", $functionType, $qualifierHierarchyId);
// exit;




// Get the function type and print this info in two headers.
$functionTypes =& $authZManager->getFunctionTypes();
while ($functionTypes->hasNext()) {
	$functionType =& $functionTypes->next();
	ob_start();
	print "<strong>".$functionType->getAuthority()." :: ";
	print $functionType->getDomain()." :: ";
	print $functionType->getKeyword()."</strong> ";
	$typeLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 1);
	$typeLayout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	
	ob_start();
	print "<em>".$functionType->getDescription()."</em>";
	$descriptionLayout =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
	$descriptionLayout->addComponent(new Content(ob_get_contents()));
	ob_end_clean();
	
	$actionRows->addComponent($typeLayout);
	$actionRows->addComponent($descriptionLayout);
	
	$functionLayout =& new RowLayout(TEXT_BLOCK_WIDGET, 3);
	$actionRows->addComponent($functionLayout);
	
	$actionRows->addComponent(new Content(" &nbsp; <br /> &nbsp; <br /> &nbsp;"));
	
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
		
		$functionLayout->addComponent(new Content(ob_get_contents()));
		ob_end_clean();
        
	}
}



// Return the main layout.
return $mainScreen;