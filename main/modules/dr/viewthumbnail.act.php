<?php

/**
 * Display the file in the specified record.
 * 
 * @package polyphony.modules.dr
 * @version $Id: viewthumbnail.act.php,v 1.3 2004/10/25 15:21:57 adamfranco Exp $
 * @date $Date: 2004/10/25 15:21:57 $
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
		if (!$mimeType || $mimeType == 'application/octet-stream') {
			$mime =& Services::getService("MIME");
			$mimeType = $mime->getMIMETypeForFileName($fields['FILE_NAME']->getValue());
		}
		
		// These are mappings to file names in the KDE icon set.
		$subTypeImages = array(
			"text/plain" => "txt.png",
			"text/css" => "css.png",
			"text/html" => "html.png",
			"text/x-lyx" => "mime_lyx.png",
			"text/xml" => "xml.png",
			
			"audio/midi" => "midi.png",
			"video/quicktime" => "quicktime.png",
			"application/vnd.rn-realmedia" => "real.png",
			"application/x-pn-realaudio" => "real.png",
			"application/x-pn-realaudio" => "real.png",
			
			"application/msword" => "wordprocessing.png",
			"application/vnd.ms-word" => "wordprocessing.png",
			"application/vnd.ms-excel" => "spreadsheet.png",
			"application/msword" => "wordprocessing.png",
			"application/msword" => "wordprocessing.png",
			
			"application/pdf" => "pdf.png",
			
			"application/x-tar" => "tar.png",
			"application/x-gtar" => "gtar.png",
			"application/x-ustar" => "tar.png",
			"application/x-gzip" => "tar.png",
			"application/x-bzip" => "tar.png",
			"application/x-bzip2" => "tar.png",
			"application/x-bcpio" => "tar.png",
			"application/x-cpio" => "tar.png",
			"application/x-shar" => "tar.png",
			"application/mac-binhex40" => "tar.png",
			"application/x-stuffit" => "tar.png",
			"application/zip" => "tar.png"		
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
		
		print file_get_contents(dirname(__FILE__)."/icons/".$imageName);
	}
}

exit;
?>