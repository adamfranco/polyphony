<?php

require_once(dirname(__FILE__)."/../DRInputOutputModule.interface.php");

/**
 * InputOutput modules are classes which generate HTML for the display or editing
 * of InfoRecords. Which InputOutput module to use is determined by the Format
 * of the InfoStructure corresponding to that InfoRecord. For example, a Structure
 * using the "DataManagerPrimitive" Format would use the DataManagerPrimative
 * InputOutput module for displaying generating forms for editing its data.
 * 
 * @package polyphony.drinputoutput
 * @version $Id: HarmoniFileModule.class.php,v 1.5 2004/10/25 15:21:03 adamfranco Exp $
 * @date $Date: 2004/10/25 15:21:03 $
 * @copyright 2004 Middlebury College
 */

class HarmoniFileModule
	extends DRInputOutputModuleInterface {
	
	/**
	 * Constructor
	 * 
	 * @return obj
	 * @access public
	 * @date 10/19/04
	 */
	function HarmoniFileModule () {
		
	}
	
	
		
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @param array $parts An ordered array of the parts to include.
	 * @return void
	 * @access public
	 * @date 10/19/04
	 */
	function createWizardStepsForParts ( & $record, & $wizard, & $parts ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		ArgumentValidator::validate($parts, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("InfoPart")));
		
		$this->createWizardSteps($record, $wizard);
	}
	
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @return void
	 * @access public
	 * @date 10/19/04
	 */
	function createWizardSteps ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		
		$structure =& $record->getInfoStructure();
		
		// Get all the fields
		$fieldIterator =& $record->getInfoFields();
		$fields = array();
		while($fieldIterator->hasNext()) {
			$field =& $fieldIterator->next();
			$part =& $field->getInfoPart();
			$partId =& $part->getId();
			$fields[$partId->getIdString()] =& $field;
		}
		
		$step =& $wizard->createStep($structure->getDisplayName());
		
		$step->createProperty("file_upload",
									new AlwaysTrueValidatorRule,
									FALSE);
									
		$property =& $step->createProperty("file_name",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue($fields['FILE_NAME']->getValue());
		
		$property =& $step->createProperty("name_from_file",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue("TRUE");
		
		$property =& $step->createProperty("file_size",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue($fields['FILE_SIZE']->getValue());
		
		$property =& $step->createProperty("size_from_file",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue("TRUE");
		
		$property =& $step->createProperty("mime_type",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue($fields['MIME_TYPE']->getValue());
		
		$property =& $step->createProperty("type_from_file",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue("TRUE");
		
		$step->createProperty("thumbnail_upload",
									new AlwaysTrueValidatorRule,
									FALSE);
		
		$property =& $step->createProperty("thumbnail_mime_type",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue($fields['THUMBNAIL_MIME_TYPE']->getValue());
		
		$property =& $step->createProperty("thumbnail_type_from_file",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue("TRUE");
		
		ob_start();
		print "\n<em>"._("Upload a new file or change file properties.")."</em>\n<hr />";
		print "\n<br /><strong>"._("New file").":</strong>";
		
		print "\n<input type='file' name='file_upload' />";
		
		print "\n<p>"._("Change properties of the uploaded file to custom values:");
		
		print "\n<table border='1'>";
		
		print "\n<tr>";
		print "\n\t<th>";
		print "\n\t\t"._("Property")."";
		print "\n\t</th>";
		print "\n\t<th>";
		print "\n\t\t"._("Take Value <br />From New File")."";
		print "\n\t</th>";
		print "\n\t<th>";
		print "\n\t\t"._("Custom Value")."";
		print "\n\t</th>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("File Name")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t<input type='checkbox' name='name_from_file' value='TRUE'";
		print " [['name_from_file'=='TRUE'|checked='checked'|]] />";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t<input type='text'";
		print " name='file_name'";
		print " value='[[file_name]]' /> ";
		print " [[file_name|Error]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("File Size")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t<input type='checkbox' name='size_from_file' value='TRUE'";
		print " [['size_from_file'=='TRUE'|checked='checked'|]] disabled='disabled' />";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t<input type='text'";
		print " name='file_size'";
		print " value='[[file_size]]' disabled='disabled' /> ";
		print " [[file_size|Error]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Mime Type")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t<input type='checkbox' name='type_from_file' value='TRUE'";
		print " [['type_from_file'=='TRUE'|checked='checked'|]] />";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t<input type='text'";
		print " name='mime_type'";
		print " value='[[mime_type]]' /> ";
		print " [[mime_type|Error]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t &nbsp; ";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n<input type='file' name='thumbnail_upload'  />";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Mime Type")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t<input type='checkbox' name='thumbnail_type_from_file' value='TRUE'";
		print " [['thumbnail_type_from_file'=='TRUE'|checked='checked'|]] />";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t<input type='text'";
		print " name='thumbnail_mime_type'";
		print " value='[[thumbnail_mime_type]]' /> ";
		print " [[thumbnail_mime_type|Error]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n</table>";
		
		print "\n</p>";
		
		$step->setText(ob_get_contents());
		ob_end_clean();
		
	}
	
	/**
	 * Get the values submitted in the wizard and update the Record with them.
	 * 
	 * @param object $record
	 * @param object $wizard
	 * @return void
	 * @access public
	 * @date 10/19/04
	 */
	function updateFromWizard ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		
		$properties =& $wizard->getProperties();
		
		// Get all the fields
		$fieldIterator =& $record->getInfoFields();
		$fields = array();
		while($fieldIterator->hasNext()) {
			$field =& $fieldIterator->next();
			$part =& $field->getInfoPart();
			$partId =& $part->getId();
			$fields[$partId->getIdString()] =& $field;
		}

		// if a new File was uploaded, store it.
		if (is_array($_FILES['file_upload']) 
			&& $_FILES['file_upload']['name']) 
		{
			$name = $_FILES['file_upload']['name'];
			$tmpName = $_FILES['file_upload']['tmp_name'];			
			$mimeType = $_FILES['file_upload']['type'];
			// If we weren't passed a mime type or were passed the generic
			// application/octet-stream type, see if we can figure out the
			// type.
			if (!$mimeType || $mimeType == 'application/octet-stream') {
				$mime =& Services::getService("MIME");
				$mimeType = $mime->getMimeTypeForFileName($name);
			}
			
			$fields['FILE_DATA']->updateValue(file_get_contents($tmpName));
			$fields['FILE_NAME']->updateValue($name);
			$fields['MIME_TYPE']->updateValue($mimeType);
		}
		
		// If we've uploaded a thumbnail, safe it.
		if (is_array($_FILES['thumbnail_upload']) 
			&& $_FILES['thumbnail_upload']['name']) 
		{
			$name = $_FILES['thumbnail_upload']['name'];
			$tmpName = $_FILES['thumbnail_upload']['tmp_name'];			
			$mimeType = $_FILES['thumbnail_upload']['type'];
			
			print "Saving uploaded thumbnail.";
			
			// If we weren't passed a mime type or were passed the generic
			// application/octet-stream type, see if we can figure out the
			// type.
			if (!$mimeType || $mimeType == 'application/octet-stream') {
				$mime =& Services::getService("MIME");
				$mimeType = $mime->getMimeTypeForFileName($name);
			}
			
			$fields['THUMBNAIL_DATA']->updateValue(file_get_contents($tmpName));
			$fields['THUMBNAIL_MIME_TYPE']->updateValue($mimeType);
		}
		// otherwise, if we've uploaded a new file only, get rid of the
		// old one and try to create a new one
		else if (is_array($_FILES['file_upload']) 
			&& $_FILES['file_upload']['name']) 
		{
			$imageProcessor =& Services::getService("ImageProcessor");
			
			// If our image format is supported by the image processor,
			// generate a thumbnail.
			if ($imageProcessor->isFormatSupported($mimeType)) {
				print "generating thumbnail.";
				
				$fields['THUMBNAIL_DATA']->updateValue(
					$imageProcessor->generateThumbnailData($mimeType, 
											file_get_contents($tmpName)));
				$fields['THUMBNAIL_MIME_TYPE']->updateValue($imageProcessor->getThumbnailFormat());
			} 
			// just make our thumbnail values empty. Default icons will display
			// instead.
			else {
				print "Setting thumbnail to empty.";
				$fields['THUMBNAIL_DATA']->updateValue("");
				$fields['THUMBNAIL_MIME_TYPE']->updateValue("");
			}
		}
		
		// if the "Take from new file" box was unchecked store the name.
		if ($properties['name_from_file']->getValue() != 'TRUE') {
			$fields['FILE_NAME']->updateValue($properties['file_name']->getValue());
		}
		
		// if the "Take from new file" box was unchecked store the size.
// 		if ($properties['size_from_file']->getValue() != 'TRUE') {
// 			$fields['FILE_SIZE']->updateValue($properties['file_size']->getValue());
// 		}
		
		// if the "Take from new file" box was unchecked store the mime type.
		if ($properties['type_from_file']->getValue() != 'TRUE') {
			$fields['MIME_TYPE']->updateValue($properties['mime_type']->getValue());
		}
	}

	/**
	 * Generate HTML for displaying the Record
	 * 
	 * @param object $record
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function generateDisplay ( & $drId, & $assetId, & $record ) {
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		
		// Get all the parts
		$structure =& $record->getInfoStructure();
		$partIterator =& $structure->getInfoParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$parts[] =& $partIterator->next();
		}
		
		return $this->generateDisplayForFields($drId, $assetId, $record, $parts);
	}

	/**
	 * Generate HTML for displaying particular fields of the Record 
	 * 
	 * @param object $record The record to print.
	 * @param array $fields An array of particular fields to print. 
	 * @return string
	 * @access public
	 * @date 10/19/04
	 */
	function generateDisplayForFields ( & $drId, & $assetId, & $record, & $parts ) {
		ArgumentValidator::validate($drId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("InfoRecord"));
		ArgumentValidator::validate($parts, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("InfoPart")));
		
		$fieldIterator =& $record->getInfoFields();
		$fields = array();
		while($fieldIterator->hasNext()) {
			$field =& $fieldIterator->next();
			$part =& $field->getInfoPart();
			$partId =& $part->getId();
			if (!is_array($fields[$partId->getIdString()]))
				$fields[$partId->getIdString()] = array();
			$fields[$partId->getIdString()][] =& $field;
		}
		
		// print out the fields;
		ob_start();
		
		$partsToSkip = array ('FILE_DATA', 'THUMBNAIL_DATA', 'THUMBNAIL_MIME_TYPE');
		$printThumbnail = FALSE;
		
		
		
		foreach (array_keys($parts) as $key) {
			$part =& $parts[$key];
			$partId =& $part->getId();
			
			if(!in_array($partId->getIdString(), $partsToSkip)){
				print "\n<strong>".$part->getDisplayName().":</strong> \n";
				if ($partId->getIdString() == 'FILE_SIZE')
					print StringFunctions::getSizeString($fields[$partId->getIdString()][0]->getValue());
				else
					print $fields[$partId->getIdString()][0]->getValue();
				print "\n<br />";
			}
			// If we've specified that we want the data, or part of the thumb, 
			// print the tumb.
			else {
				$printThumbnail = TRUE;
			}
		}
		
		$html = ob_get_contents();
		ob_end_clean();
		
		if ($printThumbnail) {
			ob_start();
			$recordId =& $record->getId();
			
			print "\n<a href='".MYURL."/dr/viewfile/"
				.$drId->getIdString()."/"
				.$assetId->getIdString()."/"
				.$recordId->getIdString()."/"
				.$fields['FILE_NAME'][0]->getValue()."'";
			print " target='_blank'>";
			
			// If we have a thumbnail with a valid mime type, print a link to that.
			$thumbnailName = ereg_replace("\.[^\.]+$", "", 
											$fields['FILE_NAME'][0]->getValue());
			if ($thumbnailMimeType = $fields['THUMBNAIL_MIME_TYPE'][0]->getValue()) {
				$mime = Services::getService("MIME");
				$thumbnailName .= ".".$mime->getExtensionForMIMEType($thumbnailMimeType);
			}
			print "\n<img src='".MYURL."/dr/viewthumbnail/"
			.$drId->getIdString()."/"
			.$assetId->getIdString()."/"
			.$recordId->getIdString()."/"
			.$thumbnailName."'";
			print " border='0' />";
		
			
			print "</a> <br />";
			
			$html = ob_get_contents().$html;
			ob_end_clean();
		}
		
		return $html;
	}
}

?>