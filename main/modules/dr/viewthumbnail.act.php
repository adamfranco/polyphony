<?php

/**
 * Display the file in the specified record.
 * 
 * @package polyphony.modules.dr
 * @version $Id: viewthumbnail.act.php,v 1.1 2004/10/21 22:34:36 adamfranco Exp $
 * @date $Date: 2004/10/21 22:34:36 $
 * @copyright 2004 Middlebury College
 */
$shared =& Services::getService("Shared");
$drManager =& Services::getService("DR");

$drId =& $shared->getId($harmoni->pathInfoParts[2]);
$assetId =& $shared->getId($harmoni->pathInfoParts[3]);
$recordId =& $shared->getId($harmoni->pathInfoParts[4]);

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
	
	// If we have a thumbnail, print that.
	if ($fields['THUMBNAIL_MIME_TYPE']->getValue()) {
	
		header("Content-Type: ".$fields['THUMBNAIL_MIME_TYPE']->getValue());
	
		print $fields['THUMBNAIL_DATA']->getValue();
	}
	// Otherwise, print a stock image for the mime type.
	else {
		header("Content-Type: image/png");
		
		$mimeType = $fields['MIME_TYPE']->getValue();
		
		$subTypeImages = array(
			"text/plain" => "txt.png",
			"text/html" => "html.png",
			"application/x-tar" => "tar.png",
			"application/pdf" => "pdf.png"
			
		);
		$typeImages = array (
			"text" => "txt.png",
			"application" => "binary.png",
			"audio" => "sound.png",
			"video" => "video.png",
			"image" => "image.png",
		);
		
		if (!$imageName = $subTypeImages[$mimeType]) {
			$parts = explode("/", $mimeType);
			$imageName = $typeImages[$parts[0]];
		}
		
		print file_get_contents(dirname(__FILE__)."/file_images/".$imageName);
	}
}

exit;
?>