<?php

/**
 * Display the file in the specified record.
 * 
 * @package polyphony.modules.dr
 * @version $Id: viewfile.act.php,v 1.1 2004/10/20 22:46:40 adamfranco Exp $
 * @date $Date: 2004/10/20 22:46:40 $
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
	
	header("Content-Type: ".$fields['MIME_TYPE']->getValue());
	
	print $fields['FILE_DATA']->getValue();
}

exit;
?>