<?php

/**
 * Display the file in the specified record.
 * 
 * @package polyphony.modules.dr
 * @version $Id: viewfile.act.php,v 1.2 2004/11/06 02:18:39 adamfranco Exp $
 * @date $Date: 2004/11/06 02:18:39 $
 * @copyright 2004 Middlebury College
 */
$shared =& Services::getService("Shared");
$drManager =& Services::getService("DR");

$drId =& $shared->getId($harmoni->pathInfoParts[2]);
$assetId =& $shared->getId($harmoni->pathInfoParts[3]);
$recordId =& $shared->getId($harmoni->pathInfoParts[4]);

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
$dr =& $drManager->getDigitalRepository($drId);
$asset =& $dr->getAsset($assetId);
$record =& $asset->getInfoRecord($recordId);

// Make sure that the structure is the right one.
$structure =& $record->getInfoStructure();
$fileId =& $shared->getId('FILE');
if (!$fileId->isEqual($structure->getId())) {
	print "The requested record is not of the FILE structure, and therefore cannot be displayed.";
} else {

	// Get the fields for the record.
	$fieldIterator =& $record->getInfoFields();
	$fields = array();
	while($fieldIterator->hasNext()) {
		$field =& $fieldIterator->next();
		$part =& $field->getInfoPart();
		$partId =& $part->getId();
		$fields[$partId->getIdString()] =& $field;
	}
	
	$imgProcessor =& Services::getService("ImageProcessor");
	
	// If we want to (and can) resize the file, do so
	if (($size || $websafe)
		&& $imgProcessor->isFormatSupported($fields['MIME_TYPE']->getValue())) 
	{
		// Get a version in a web-safe format if so requested
		if ($websafe) 
		{
			header("Content-Type: "
				. $imgProcessor->getWebsafeFormat($fields['MIME_TYPE']->getValue()));
			print $imgProcessor->getWebsafeData(
							$fields['MIME_TYPE']->getValue(),
							$size,
							$fields['FILE_DATA']->getValue());
		} 
		// Otherwise, resize the original
		else {
			header("Content-Type: "
				. $imgProcessor->getResizedFormat($fields['MIME_TYPE']->getValue()));
			
			print $imgProcessor->getResizedData(
							$fields['MIME_TYPE']->getValue(),
							$size,
							$fields['FILE_DATA']->getValue());
		}
	}
	// Otherwise, just send the original file
	else {
		header("Content-Type: ".$fields['MIME_TYPE']->getValue());
	
		print $fields['FILE_DATA']->getValue();
	}
}

exit;
?>