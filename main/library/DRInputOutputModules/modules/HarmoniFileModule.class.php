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
 * @version $Id: HarmoniFileModule.class.php,v 1.1 2004/10/20 19:04:51 adamfranco Exp $
 * @date $Date: 2004/10/20 19:04:51 $
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
			&& is_uploaded_file($_FILES['file_upload']['name'])) 
		{
			$name = $_FILES['file_upload']['name'];
			$tmpName = $_FILES['file_upload']['tmp_name'];			
			$mimeType = $_FILES['file_upload']['type'];
			if (!$mimeType)
//				$mimeType = MIMETypes::getMimeTypeForFileName($uploadedName);
			
			$fields['FILE_DATA']->updateValue(file_get_contents($tmpName));
			$fields['FILE_NAME']->updateValue($name);
			$fields['MIME_TYPE']->updateValue($mimeType);
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
		
		foreach (array_keys($parts) as $key) {
			$part =& $parts[$key];
			$partId =& $part->getId();
			
			if ($partId->getIdString() == 'FILE_DATA') {
				$recordId =& $record->getId();
				
				print "\n<a href='".MYURL."/file/view/"
					.$drId->getIdString()."/"
					.$assetId->getIdString()."/"
					.$recordId->getIdString()."/"
					.$fields['FILE_NAME'][0]->getValue()."'";
				print " target='_blank'>";
				print $fields['FILE_NAME'][0]->getValue();
				print "</a> <br />";
			} else {
				print "\n<strong>".$part->getDisplayName().":</strong> \n";			
				print$fields[$partId->getIdString()][0]->getValue();
				print "\n<br />";
			}
		}
		
		$html = ob_get_contents();
		ob_end_clean();
		
		return $html;
	}
}

?>