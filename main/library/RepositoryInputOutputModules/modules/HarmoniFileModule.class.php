<?php
/**
 *
 * @package polyphony.library.repository.inputoutput
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HarmoniFileModule.class.php,v 1.15 2006/04/24 22:36:54 adamfranco Exp $
 */

/**
 * Require the class that we are extending.
 * 
 */
require_once(dirname(__FILE__)."/../RepositoryInputOutputModule.interface.php");
require_once(HARMONI."Primitives/Numbers/ByteSize.class.php");

/**
 * InputOutput modules are classes which generate HTML for the display or editing
 * of Records. Which InputOutput module to use is determined by the Format
 * of the RecordStructure corresponding to that Record. For example, a Structure
 * using the "DataManagerPrimitive" Format would use the DataManagerPrimative
 * InputOutput module for displaying generating forms for editing its data.
 * 
 * @package polyphony.library.repository.inputoutput
 * @version $Id: HarmoniFileModule.class.php,v 1.15 2006/04/24 22:36:54 adamfranco Exp $
 * @since $Date: 2006/04/24 22:36:54 $
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
		
		$step =& $wizard->addStep("record", new WizardStep());
		$step->setDisplayName($recordStructure->getDisplayName());
		
		ob_start();
		
		$component =& $step->addComponent("file_upload", new WFileUploadField());
		
		print "\n<em>"._("Upload a new file or change file properties.")."</em>\n<hr />";
		print "\n<br /><strong>";
		if ($parts['FILE_NAME']->getValue()) {
			print _("New file (optional)");
		} else {
			print _("File");
		}
		print ":</strong>";
		
		print "\n[[file_upload]]";
		
		
		$component =& $step->addComponent("file_name", new WTextField());
		$component->setValue($parts['FILE_NAME']->getValue());
		
		$component =& $step->addComponent("use_custom_filename", new WCheckBox());
		$component->setValue(false);
		
		
		$component =& $step->addComponent("file_size", new WTextField());
		$size =& ByteSize::withValue($parts['FILE_SIZE']->getValue());
		$component->setValue($size->asString());
		$component->setEnabled(FALSE, TRUE);
// 		
// 		$component =& $step->addComponent("size_from_file", new WCheckBox());
// 		$component->setValue(false);
		
		
		$component =& $step->addComponent("mime_type", new WTextField());
		$component->setValue($parts['MIME_TYPE']->getValue());
		
		$component =& $step->addComponent("use_custom_type", new WCheckBox());
		$component->setValue(false);
		
		
		// Dimensions 
		$dimensionComponent =& new WTextField();
		$dimensionComponent->setSize(8);
		$dimensionComponent->setStyle("text-align: right");
		$dimensionComponent->setErrorRule(new WECRegex("^([0-9]+px)?$"));
		$dimensionComponent->setErrorText(_("Must be a positive integer followed by 'px'."));
		$dimensionComponent->setOnChange("validateWizard(this.form);");
		
		$dim = $parts['DIMENSIONS']->getValue();
		$component =& $step->addComponent("height", $dimensionComponent->shallowCopy());
		if ($dim[1])
			$component->setValue($dim[1].'px');

		$component =& $step->addComponent("use_custom_height", new WCheckBox());
		$component->setValue(false);
		
		$component =& $step->addComponent("width", $dimensionComponent->shallowCopy());
		if ($dim[0])
			$component->setValue($dim[0].'px');
		
		
		$component =& $step->addComponent("use_custom_width", new WCheckBox());
		$component->setValue(false);
		
		
		// Thumnail Upload
		$component =& $step->addComponent("thumbnail_upload", new WFileUploadField());
		
		
		$component =& $step->addComponent("thumbnail_mime_type", new WTextField());
		$component->setValue($parts['THUMBNAIL_MIME_TYPE']->getValue());
		
		$component =& $step->addComponent("use_custom_thumbnail_type", new WCheckBox());
		$component->setValue(false);
		
		
		// Thumbnail dimensions
		$thumDim = $parts['THUMBNAIL_DIMENSIONS']->getValue();
		$component =& $step->addComponent("thumbnail_height", $dimensionComponent->shallowCopy());
		if ($thumDim[1])
			$component->setValue($thumDim[1].'px');
		
		$component =& $step->addComponent("use_custom_thumbnail_height", new WCheckBox());
		$component->setValue(false);
		
		$component =& $step->addComponent("thumbnail_width", $dimensionComponent->shallowCopy());
		if ($thumDim[0])
			$component->setValue($thumDim[0].'px');
		
		$component =& $step->addComponent("use_custom_thumbnail_width", new WCheckBox());
		$component->setValue(false);
		
		
		
		print "\n<p>"._("Change properties of the uploaded file to custom values:");
		
		print "\n<table border='1'>";
		
		print "\n<tr>";
		print "\n\t<th>";
		print "\n\t\t"._("Property")."";
		print "\n\t</th>";
		print "\n\t<th>";
		print "\n\t\t"._("Use Custom Value")."";
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
		print "\n\t\t[[use_custom_filename]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[file_name]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("File Size")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
// 		print "\n\t\t[[size_from_file]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[file_size]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Mime Type")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_type]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[mime_type]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Width")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_width]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[width]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Height")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_height]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[height]]";
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
		print "\n[[thumbnail_upload]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Mime Type")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_thumbnail_type]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[thumbnail_mime_type]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Width")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_thumbnail_width]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[thumbnail_width]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n<tr>";
		print "\n\t<td>";
		print "\n\t\t"._("Thumbnail Height")."";
		print "\n\t</td>";
		print "\n\t<td align='center'>";
		print "\n\t\t[[use_custom_thumbnail_height]]";
		print "\n\t</td>";
		print "\n\t<td>";
		print "\n\t\t[[thumbnail_height]]";
		print "\n\t</td>";
		print "\n</tr>";
		
		print "\n</table>";
		
		print "\n</p>";
		
		$step->setContent(ob_get_contents());
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
		
		$properties =& $wizard->getAllValues();
		$values =& $properties["record"];
		printpre($properties);
		
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
		if ($values['file_upload']['tmp_name'] 
			&& $values['file_upload']['name']) 
		{
			$name = $values['file_upload']['name'];
			$tmpName = $values['file_upload']['tmp_name'];			
			$mimeType = $values['file_upload']['type'];
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
		if ($values['thumbnail_upload']['tmp_name'] 
			&& $values['thumbnail_upload']['name']) 
		{
			$name = $values['thumbnail_upload']['name'];
			$tmpName = $values['thumbnail_upload']['tmp_name'];			
			$mimeType = $values['thumbnail_upload']['type'];
						
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
		else if ($values['file_upload']['tmp_name'] 
			&& $values['file_upload']['name']) 
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
		
		// if the "use custom" box was checked store the name.
		if ($values['use_custom_filename']) {
			$parts['FILE_NAME']->updateValue($values['file_name']);
		}
		
		// if the "use custom" box was checked store the mime type.
		if ($values['use_custom_type']) {
			$parts['MIME_TYPE']->updateValue($values['mime_type']);
		}
		
		// if the "use custom" box was checked store the height.
		if ($values['use_custom_height']
			&& ereg("^([0-9]+)px$", $values['height'], $matches)) 
		{
			$dimArray = $parts['DIMENSIONS']->getValue();
			$dimArray[1] = $matches[1];
			print "Setting DIMENSIONS to:"; printpre($dimArray);
			$parts['DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the width.
		if ($values['use_custom_width']
			&& ereg("^([0-9]+)px$", $values['width'], $matches)) 
		{
			$dimArray = $parts['DIMENSIONS']->getValue();
			$dimArray[0] = $matches[1];
			print "Setting DIMENSIONS to:"; printpre($dimArray);
			$parts['DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the height.
		if ($values['use_custom_thumbnail_height']
			&& ereg("^([0-9]+)px$", $values['thumbnail_height'], $matches)) 
		{
			$dimArray = $parts['THUMBNAIL_DIMENSIONS']->getValue();
			$dimArray[1] = $matches[1];
			print "Setting THUMBNAIL_DIMENSIONS to:"; printpre($dimArray);
			$parts['THUMBNAIL_DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
		
		// if the "use custom" box was checked store the width.
		if ($values['use_custom_thumbnail_width']
			&& ereg("^([0-9]+)px$", $values['thumbnail_width'], $matches)) 
		{
			$dimArray = $parts['THUMBNAIL_DIMENSIONS']->getValue();
			$dimArray[0] = $matches[1];
			print "Setting THUMBNAIL_DIMENSIONS to:"; printpre($dimArray);
			$parts['THUMBNAIL_DIMENSIONS']->updateValue($dimArray);
		}
		unset($dimArray, $matches);
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
			if (!isset($parts[$partStructureId->getIdString()]) || !is_array($parts[$partStructureId->getIdString()]))
				$parts[$partStructureId->getIdString()] = array();
			$parts[$partStructureId->getIdString()][] =& $part;
		}
		
		// print out the parts;
		ob_start();
		
		$partStructuresToSkip = array ('FILE_DATA', 'THUMBNAIL_DATA', 
								'THUMBNAIL_MIME_TYPE', 'THUMBNAIL_DIMENSIONS');
		$printThumbnail = FALSE;
		foreach (array_keys($partStructures) as $key) {
			$partStructure =& $partStructures[$key];
			$partStructureId =& $partStructure->getId();
			
			if(!in_array($partStructureId->getIdString(), $partStructuresToSkip)){
				print "\n<strong>".$partStructure->getDisplayName().":</strong> \n";
				switch ($partStructureId->getIdString()) {
					case 'FILE_SIZE':
						$size =& ByteSize::withValue($parts[$partStructureId->getIdString()][0]->getValue());
						print $size->asString();
						break;
					case 'DIMENSIONS':
						$dimensionArray = $parts[$partStructureId->getIdString()][0]->getValue();
						print "<em>"._('width: ')."</em>".$dimensionArray[0].'px<em>;</em> ';
						print "<em>"._('height: ')."</em>".$dimensionArray[1].'px';
						break;
					default:
						print $parts[$partStructureId->getIdString()][0]->getValue();
				}
				print "\n<br />";
			}
			// If we've specified that we want the data, or part of the thumb, 
			// print the tumb.
			else {
				$printThumbnail = TRUE;
			}
		}

		$html = ob_get_clean();
		
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-repository");
		
		if ($printThumbnail) {
			ob_start();
			$recordId =& $record->getId();
			$ns = $harmoni->request->endNamespace();
// ======= VIEWER LINK ======== //			
			$xmlAssetIdString = $harmoni->request->get("asset_id");
			
			print "<a href='#' onclick='Javascript:window.open(";
			print '"'.VIEWER_URL."?&amp;source=";
			print urlencode($harmoni->request->quickURL("asset", "browserecordxml",
						array("collection_id" => $repositoryId->getIdString(),
						"asset_id" => $xmlAssetIdString,
						"record_id" => $recordId->getIdString(),
						RequestContext::name("limit_by") => RequestContext::value("limit_by"),
						RequestContext::name("type") => RequestContext::value("type"),
						RequestContext::name("searchtype") => RequestContext::value("searchtype"),
						RequestContext::name("searchstring") => RequestContext::value("searchstring"))));
			print '&amp;start=0", ';
			print '"'.preg_replace("/[^a-z0-9]/i", '_', $assetId->getIdString()).'", ';
			print '"toolbar=no,location=no,directories=no,status=yes,scrollbars=yes,resizable=yes,copyhistory=no,width=600,height=500"';
			print ")'>";
			$harmoni->request->startNamespace($ns);
			// If we have a thumbnail with a valid mime type, print a link to that.
			$thumbnailName = ereg_replace("\.[^\.]+$", "", 
											$parts['FILE_NAME'][0]->getValue());
			if ($thumbnailMimeType = $parts['THUMBNAIL_MIME_TYPE'][0]->getValue()) {
				$mime = Services::getService("MIME");
				$thumbnailName .= ".".$mime->getExtensionForMIMEType($thumbnailMimeType);
			}
			print "\n<img src='".$harmoni->request->quickURL("repository", "viewthumbnail",
				array(
					"repository_id" => $repositoryId->getIdString(),
					"asset_id" => $assetId->getIdString(),
					"record_id" => $recordId->getIdString(),
					"thumbnail_name" => $thumbnailName))."'";
			print " style='border: 0px;'";
			print " alt='Thumbnail image.'";
			print " align='left'";
			print " />";
		
			print "</a> <br />";
			
			$html2 = ob_get_clean();

			ob_start();
			print "\n<a href='".$harmoni->request->quickURL("repository", "viewfile", 
				array(
					"repository_id" => $repositoryId->getIdString(),
					"asset_id" => $assetId->getIdString(),
					"record_id" => $recordId->getIdString(),
					"file_name" => $parts['FILE_NAME'][0]->getValue()))."'";
			print " target='_blank'>";

			print "Download This File</a>\n";
			$downloadlink = ob_get_clean();
			
			
			$html = "<table border=0><tr><td>".$html2."</td><td>".$html.$downloadlink."</td></tr></table>";
		
		}
		
		$harmoni->request->endNamespace();
		
		return $html;
	}
}

?>
