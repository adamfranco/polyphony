<?php

/**
 * Display the file in the specified record.
 * 
 * @package polyphony.modules.dr
 * @version $Id: viewfile.act.php,v 1.1 2005/01/27 19:37:51 adamfranco Exp $
 * @date $Date: 2005/01/27 19:37:51 $
 * @copyright 2004 Middlebury College
 */
$idManager =& Services::getService("Id");
$repositoryManager =& Services::getService("Repository");

$repositoryId =& $idManager->getId($harmoni->pathInfoParts[2]);
$assetId =& $idManager->getId($harmoni->pathInfoParts[3]);
$recordId =& $idManager->getId($harmoni->pathInfoParts[4]);

// See if we are passed a size
if (is_numeric($harmoni->pathInfoParts[5]))
	$size = intval($harmoni->pathInfoParts[5]);
else if (is_numeric($_REQUEST["size"]))
	$size = intval($_REQUEST["size"]);
else
	$size = FALSE;

if ($harmoni->pathInfoParts[5] == "websafe" 
	|| $harmoni->pathInfoParts[6] == "websafe"
	|| $_REQUEST["websafe"])
	$websafe = TRUE;
else
	$websafe = FALSE;

// Get the requested record.
$repository =& $repositoryManager->getRepository($repositoryId);
$asset =& $repository->getAsset($assetId);
$record =& $asset->getRecord($recordId);

// Make sure that the structure is the right one.
$recordStructure =& $record->getRecordStructure();
$fileId =& $idManager->getId('FILE');
if (!$fileId->isEqual($recordStructure->getId())) {
	print "The requested record is not of the FILE recordstructure, and therefore cannot be displayed.";
} else {

	// Get the parts for the record.
	$partIterator =& $record->getParts();
	$parts = array();
	while($partIterator->hasNext()) {
		$part =& $partIterator->next();
		$partStructure =& $part->getPartStructure();
		$partStructureId =& $partStructure->getId();
		$parts[$partStructureId->getIdString()] =& $part;
	}
	
	$imgProcessor =& Services::getService("ImageProcessor");
	
	// If we want to (and can) resize the file, do so
	if (($size || $websafe)
		&& $imgProcessor->isFormatSupported($parts['MIME_TYPE']->getValue())) 
	{
		// Get a version in a web-safe format if so requested
		if ($websafe) 
		{
			header("Content-Type: "
				. $imgProcessor->getWebsafeFormat($parts['MIME_TYPE']->getValue()));
			print $imgProcessor->getWebsafeData(
							$parts['MIME_TYPE']->getValue(),
							$size,
							$parts['FILE_DATA']->getValue());
		} 
		// Otherwise, resize the original
		else {
			header("Content-Type: "
				. $imgProcessor->getResizedFormat($parts['MIME_TYPE']->getValue()));
			
			print $imgProcessor->getResizedData(
							$parts['MIME_TYPE']->getValue(),
							$size,
							$parts['FILE_DATA']->getValue());
		}
	}
	// Otherwise, just send the original file
	else {
		header("Content-Type: ".$parts['MIME_TYPE']->getValue());
	
		print $parts['FILE_DATA']->getValue();
	}
}

exit;
?>