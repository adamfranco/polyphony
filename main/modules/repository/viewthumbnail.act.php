<?php

/**
 * @package polyphony.modules.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewthumbnail.act.php,v 1.5 2005/07/21 15:45:25 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * Display the file in the specified record.
 *
 * @since 11/11/04 
 * @author Ryan Richards
 * @author Adam Franco
 * 
 * @package polyphony.modules.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewthumbnail.act.php,v 1.5 2005/07/21 15:45:25 adamfranco Exp $
 */
class viewthumbnailAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$authZManager =& Services::getService("AuthorizationManager");
		
		$harmoni->request->startNamespace("polyphony-repository");
		$assetId =& $idManager->getId(RequestContext::value("asset_id"));
		$harmoni->request->endNamespace();
		
		return $authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"),
					$assetId);
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return dgettext("polyphony", "File Thumbnail");
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function buildContent () {
		$defaultTextDomain = textdomain("polyphony");
		
		$actionRows =& $this->getActionRows();
		$harmoni =& Harmoni::instance();
		$idManager =& Services::getService("Id");
		$repositoryManager =& Services::getService("Repository");
		
		$harmoni->request->startNamespace("polyphony-repository");
		
		$repositoryId =& $idManager->getId(RequestContext::value("repository_id"));
		$assetId =& $idManager->getId(RequestContext::value("asset_id"));
		$recordId =& $idManager->getId(RequestContext::value("record_id"));

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
				
				if (isset($subTypeImages[$mimeType])) {
					$imageName = $subTypeImages[$mimeType];
				} else {
					$typeParts = explode("/", $mimeType);
					$imageName = $typeImages[$typeParts[0]];
				}
				
				print file_get_contents(dirname(__FILE__)."/icons/".$imageName);
			}
		}
		
		$harmoni->request->endNamespace();
		textdomain($defaultTextDomain);
		exit;
	}
}
?>