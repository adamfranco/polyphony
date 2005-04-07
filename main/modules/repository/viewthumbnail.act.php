<?php

/**
 * Display the file in the specified record.
 *
 * @package polyphony.modules.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewthumbnail.act.php,v 1.3 2005/04/07 17:08:08 adamfranco Exp $
 */

$idManager =& Services::getService("Id");
$repositoryManager =& Services::getService("Repository");

$repositoryId =& $idManager->getId($harmoni->pathInfoParts[2]);
$assetId =& $idManager->getId($harmoni->pathInfoParts[3]);
$recordId =& $idManager->getId($harmoni->pathInfoParts[4]);

// Get the requested record.
$repository =& $repositoryManager->getRepository($repositoryId);
$asset =& $repository->getAsset($assetId);
$record =& $asset->getRecord($recordId);

// Make sure that the structure is the right one.
$structure =& $record->getRecordStructure();
$fileId =& $idManager->getId('FILE');
if (!$fileId->isEqual($structure->getId())) {
	print "The requested record is not of the FILE structure, and therefore cannot be displayed.";
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
	
	// If we have a thumbnail, print that.
	if ($parts['THUMBNAIL_MIME_TYPE']->getValue()) {
	
		header("Content-Type: ".$parts['THUMBNAIL_MIME_TYPE']->getValue());
	
		print $parts['THUMBNAIL_DATA']->getValue();
	}
	// Otherwise, print a stock image for the mime type.
	else {
		header("Content-Type: image/png");
		
		$mimeType = $parts['MIME_TYPE']->getValue();
		if (!$mimeType || $mimeType == 'application/octet-stream') {
			$mime =& Services::getService("MIME");
			$mimeType = $mime->getMIMETypeForFileName($parts['FILE_NAME']->getValue());
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
			$typeParts = explode("/", $mimeType);
			$imageName = $typeImages[$typeParts[0]];
		}
		
		print file_get_contents(dirname(__FILE__)."/icons/".$imageName);
	}
}

exit;
?>