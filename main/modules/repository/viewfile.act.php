<?php

/**
 * @package polyphony.modules.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewfile.act.php,v 1.8 2005/12/22 23:15:27 cws-midd Exp $
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
 * @version $Id: viewfile.act.php,v 1.8 2005/12/22 23:15:27 cws-midd Exp $
 */
class viewfileAction 
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
		return dgettext("polyphony", "View File");
	}
	
	/**
	 * Return a junk image that says you can't view the file
	 *
	 * @since 12/22/05
	 */
	function getUnauthorizedMessage() {
		header("Content-Type: image/gif");
		header('Content-Disposition: attachment; filename="english.gif"');
			
		print file_get_contents(POLYPHONY.'/docs/images/unauthorized/english.gif');
		exit;
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
		$size = RequestContext::value("size");
		$websafe = RequestContext::value("websafe");
		
		// See if we are passed a size
		if (is_numeric($size))
			$size = intval($size);
		else
			$size = FALSE;
		
		if ($websafe)
			$websafe = TRUE;
		else
			$websafe = FALSE;

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
			
			$imgProcessor =& Services::getService("ImageProcessor");
	
			// If we want to (and can) resize the file, do so
			if (($size || $websafe)
				&& $imgProcessor->isFormatSupported($parts['MIME_TYPE']->getValue())) 
			{
				// Get a version in a web-safe format if so requested
				if ($websafe) 
				{
					header("Content-Type: ". $imgProcessor->getWebsafeFormat(
							$parts['MIME_TYPE']->getValue()));
					
					$mime =& Services::getService("MIME");
					$extension = $mime->getExtensionForMIMEType(
									$imgProcessor->getWebsafeFormat(
										$parts['MIME_TYPE']->getValue()));
					if (ereg("^.+\.".$extension."$", $parts['FILE_NAME']->getValue())) {
						header('Content-Disposition: attachment; filename="'.
							$parts['FILE_NAME']->getValue().'"');
					} else {
						header('Content-Disposition: attachment; filename="'.
							$parts['FILE_NAME']->getValue().".".$extension.'"');
					}
					
					print $imgProcessor->getWebsafeData(
									$parts['MIME_TYPE']->getValue(),
									$size,
									$parts['FILE_DATA']->getValue());
				} 
				// Otherwise, resize the original
				else {
					header("Content-Type: "
						. $imgProcessor->getResizedFormat($parts['MIME_TYPE']->getValue()));
					header('Content-Disposition: attachment; filename="'.
							$parts['FILE_NAME']->getValue().'"');
					
					print $imgProcessor->getResizedData(
									$parts['MIME_TYPE']->getValue(),
									$size,
									$parts['FILE_DATA']->getValue());
				}
			}
			// Otherwise, just send the original file
			else {
				header("Content-Type: ".$parts['MIME_TYPE']->getValue());
				header('Content-Disposition: attachment; filename="'.
							$parts['FILE_NAME']->getValue().'"');
			
				print $parts['FILE_DATA']->getValue();
			}
		}
		
		$harmoni->request->endNamespace();
		textdomain($defaultTextDomain);
		exit;
	}
}
?>