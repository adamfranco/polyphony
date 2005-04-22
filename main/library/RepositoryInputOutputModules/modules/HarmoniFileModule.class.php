<?php
/**
 *
 * @package polyphony.library.repository.inputoutput
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HarmoniFileModule.class.php,v 1.4 2005/04/22 17:31:35 adamfranco Exp $
 */

/**
 * Require the class that we are extending.
 * 
 */
require_once(dirname(__FILE__)."/../RepositoryInputOutputModule.interface.php");

/**
 * InputOutput modules are classes which generate HTML for the display or editing
 * of Records. Which InputOutput module to use is determined by the Format
 * of the RecordStructure corresponding to that Record. For example, a Structure
 * using the "DataManagerPrimitive" Format would use the DataManagerPrimative
 * InputOutput module for displaying generating forms for editing its data.
 * 
 * @package polyphony.library.repository.inputoutput
 * @version $Id: HarmoniFileModule.class.php,v 1.4 2005/04/22 17:31:35 adamfranco Exp $
 * @since $Date: 2005/04/22 17:31:35 $
 * @copyright 2004 Middlebury College
 */

class HarmoniFileModule
	extends RepositoryInputOutputModuleInterface {
	
	/**
	 * Constructor
	 * 
	 * @return obj
	 * @access public
	 * @since 10/19/04
	 */
	function HarmoniFileModule () {
		
	}
	
	
		
	/**
	 * Create wizard steps for editing the values of the specified Record and
	 * add them to the wizard.
	 * 
	 * @param object $record
	 * @param object $wizard The wizard to add the steps to.
	 * @param array $partStructures An ordered array of the partStructures to include.
	 * @return void
	 * @access public
	 * @since 10/19/04
	 */
	function createWizardStepsForPartStructures ( & $record, & $wizard, & $partStructures ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));
		
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
	 * @since 10/19/04
	 */
	function createWizardSteps ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		
		$recordStructure =& $record->getRecordStructure();
		
		// Get all the parts
		$partIterator =& $record->getParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$part =& $partIterator->next();
			$partStructure =& $part->getPartStructure();
			$partStructureId =& $partStructure->getId();
			$parts[$partStructureId->getIdString()] =& $part;
		}
		
		$step =& $wizard->createStep($recordStructure->getDisplayName());
		
		$step->createProperty("file_upload",
									new AlwaysTrueValidatorRule,
									FALSE);
									
		$property =& $step->createProperty("file_name",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue($parts['FILE_NAME']->getValue());
		
		$property =& $step->createProperty("name_from_file",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue("TRUE");
		
		$property =& $step->createProperty("file_size",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue($parts['FILE_SIZE']->getValue());
		
		$property =& $step->createProperty("size_from_file",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue("TRUE");
		
		$property =& $step->createProperty("mime_type",
									new AlwaysTrueValidatorRule,
									FALSE);
		$property->setDefaultValue($parts['MIME_TYPE']->getValue());
		
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
		$property->setDefaultValue($parts['THUMBNAIL_MIME_TYPE']->getValue());
		
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
	 * @since 10/19/04
	 */
	function updateFromWizard ( & $record, & $wizard ) {
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		ArgumentValidator::validate($wizard, new ExtendsValidatorRule("Wizard"));
		
		$properties =& $wizard->getProperties();
		
		// Get all the parts
		$partIterator =& $record->getParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$part =& $partIterator->next();
			$partStructure =& $part->getPartStructure();
			$partStructureId =& $partStructure->getId();
			$parts[$partStructureId->getIdString()] =& $part;
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
			
			$parts['FILE_DATA']->updateValue(file_get_contents($tmpName));
			$parts['FILE_NAME']->updateValue($name);
			$parts['MIME_TYPE']->updateValue($mimeType);
		}
		
		// If we've uploaded a thumbnail, safe it.
		if (is_array($_FILES['thumbnail_upload']) 
			&& $_FILES['thumbnail_upload']['name']) 
		{
			$name = $_FILES['thumbnail_upload']['name'];
			$tmpName = $_FILES['thumbnail_upload']['tmp_name'];			
			$mimeType = $_FILES['thumbnail_upload']['type'];
						
			// If we weren't passed a mime type or were passed the generic
			// application/octet-stream type, see if we can figure out the
			// type.
			if (!$mimeType || $mimeType == 'application/octet-stream') {
				$mime =& Services::getService("MIME");
				$mimeType = $mime->getMimeTypeForFileName($name);
			}
			
			$parts['THUMBNAIL_DATA']->updateValue(file_get_contents($tmpName));
			$parts['THUMBNAIL_MIME_TYPE']->updateValue($mimeType);
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
				$parts['THUMBNAIL_DATA']->updateValue(
					$imageProcessor->generateThumbnailData($mimeType, 
											file_get_contents($tmpName)));
				$parts['THUMBNAIL_MIME_TYPE']->updateValue($imageProcessor->getThumbnailFormat());
			} 
			// just make our thumbnail values empty. Default icons will display
			// instead.
			else {
				$parts['THUMBNAIL_DATA']->updateValue("");
				$parts['THUMBNAIL_MIME_TYPE']->updateValue("NULL");
			}
		}
		
		// if the "Take from new file" box was unchecked store the name.
		if ($properties['name_from_file']->getValue() != 'TRUE') {
			$parts['FILE_NAME']->updateValue($properties['file_name']->getValue());
		}
		
		// if the "Take from new file" box was unchecked store the size.
// 		if ($properties['size_from_file']->getValue() != 'TRUE') {
// 			$parts['FILE_SIZE']->updateValue($properties['file_size']->getValue());
// 		}
		
		// if the "Take from new file" box was unchecked store the mime type.
		if ($properties['type_from_file']->getValue() != 'TRUE') {
			$parts['MIME_TYPE']->updateValue($properties['mime_type']->getValue());
		}
	}

	/**
	 * Generate HTML for displaying the Record
	 * 
	 * @param object $record
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function generateDisplay ( & $repositoryId, & $assetId, & $record ) {
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		
		// Get all the partstructures
		$recordStructure =& $record->getRecordStructure();
		$partStructureIterator =& $recordStructure->getPartStructures();
		$partStructures = array();
		while($partStructureIterator->hasNext()) {
			$partStructures[] =& $partStructureIterator->next();
		}
		
		return $this->generateDisplayForParts($repositoryId, $assetId, $record, $partStructures);
	}

	/**
	 * Generate HTML for displaying particular parts of the Record 
	 * 
	 * @param object $record The record to print.
	 * @param array $partStructures An array of particular partStructures to print. 
	 * @return string
	 * @access public
	 * @since 10/19/04
	 */
	function generateDisplayForPartStructures ( & $repositoryId, & $assetId, & $record, & $partStructures ) {
		ArgumentValidator::validate($repositoryId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($assetId, new ExtendsValidatorRule("Id"));
		ArgumentValidator::validate($record, new ExtendsValidatorRule("RecordInterface"));
		ArgumentValidator::validate($partStructures, new ArrayValidatorRuleWithRule(new ExtendsValidatorRule("PartStructure")));
		
		$partIterator =& $record->getParts();
		$parts = array();
		while($partIterator->hasNext()) {
			$part =& $partIterator->next();
			$partStructure =& $part->getPartStructure();
			$partStructureId =& $partStructure->getId();
			if (!is_array($parts[$partStructureId->getIdString()]))
				$parts[$partStructureId->getIdString()] = array();
			$parts[$partStructureId->getIdString()][] =& $part;
		}
		
		// print out the parts;
		ob_start();
		
		$partStructuresToSkip = array ('FILE_DATA', 'THUMBNAIL_DATA', 'THUMBNAIL_MIME_TYPE');
		$printThumbnail = FALSE;
		
		
		
		foreach (array_keys($partStructures) as $key) {
			$partStructure =& $partStructures[$key];
			$partStructureId =& $partStructure->getId();
			
			if(!in_array($partStructureId->getIdString(), $partStructuresToSkip)){
				print "\n<strong>".$partStructure->getDisplayName().":</strong> \n";
				if ($partStructureId->getIdString() == 'FILE_SIZE')
					print StringFunctions::getSizeString($parts[$partStructureId->getIdString()][0]->getValue());
				else
					print $parts[$partStructureId->getIdString()][0]->getValue();
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
			
			print "\n<a href='".MYURL."/repository/viewfile/"
				.$repositoryId->getIdString()."/"
				.$assetId->getIdString()."/"
				.$recordId->getIdString()."/"
				.$parts['FILE_NAME'][0]->getValue()."'";
			print " target='_blank'>";
			
			// If we have a thumbnail with a valid mime type, print a link to that.
			$thumbnailName = ereg_replace("\.[^\.]+$", "", 
											$parts['FILE_NAME'][0]->getValue());
			if ($thumbnailMimeType = $parts['THUMBNAIL_MIME_TYPE'][0]->getValue()) {
				$mime = Services::getService("MIME");
				$thumbnailName .= ".".$mime->getExtensionForMIMEType($thumbnailMimeType);
			}
			print "\n<img src='".MYURL."/repository/viewthumbnail/"
			.$repositoryId->getIdString()."/"
			.$assetId->getIdString()."/"
			.$recordId->getIdString()."/"
			.$thumbnailName."'";
			print " style='border: 0px;'";
			print " alt='Thumbnail image.'";
			print " />";
		
			
			print "</a> <br />";
			
			$html = ob_get_contents().$html;
			ob_end_clean();
		}
		
		return $html;
	}
}

?>