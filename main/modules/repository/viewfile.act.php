<?php

/**
 * @package polyphony.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewfile.act.php,v 1.18 2008/03/11 21:00:22 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/ForceAuthAction.class.php");

/**
 * Display the file in the specified record.
 *
 * @since 11/11/04 
 * @author Ryan Richards
 * @author Adam Franco
 * 
 * @package polyphony.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewfile.act.php,v 1.18 2008/03/11 21:00:22 adamfranco Exp $
 */
class viewfileAction 
	extends ForceAuthAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isExecutionAuthorized () {
		$harmoni = Harmoni::instance();
		$idManager = Services::getService("Id");
		$authZManager = Services::getService("AuthorizationManager");
		
		$harmoni->request->startNamespace("polyphony-repository");
		$assetId =$idManager->getId(RequestContext::value("asset_id"));
		$harmoni->request->endNamespace();
		
		try {
			return $authZManager->isUserAuthorized(
					$idManager->getId("edu.middlebury.authorization.view"),
					$assetId);
		} catch (UnknownIdException $e) {
			HarmoniErrorHandler::logException($e);
			$this->getUnknownIdMessage();
		}
	}
	
	/**
	 * Return a junk image that says you can't view the file
	 *
	 * @since 12/22/05
	 */
	function getUnauthorizedMessage() {
		header("Content-Type: image/gif");
		header('Content-Disposition: filename="english.gif"');
			
		print file_get_contents(POLYPHONY.'/docs/images/unauthorized/english.gif');
		exit;
	}
	
	/**
	 * Answer a junk image that says we don't know the Id of the file
	 * 
	 * @return void
	 * @access protected
	 * @since 2/15/08
	 */
	protected function getUnknownIdMessage () {
		header("Content-Type: image/gif");
		header('Content-Disposition: filename="english.gif"');
			
		print file_get_contents(POLYPHONY.'/docs/images/unknownid/english.gif');
		exit;
	}
	
	/**
	 * Answer the HTTP Authentication 'Relm' to present to the user for authentication.
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 8/7/06
	 */
	function getRelm () {
		return 'Concerto'; // Override for custom relm.
	}
	
	/**
	 * Answer the cancel function for this action, to use if the user hits
	 * the 'cancel' button in the http authentication dialog.
	 * 
	 * @return mixed string or null
	 * @access public
	 * @since 8/7/06
	 */
	function getCancelFunction () {
		return 'viewfileAction::getUnauthorizedMessage();';
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 4/26/05
	 */
	function execute () {
		if (!$this->isAuthorizedToExecute())
			$this->getUnauthorizedMessage();
		
		$defaultTextDomain = textdomain("polyphony");
		
		$harmoni = Harmoni::instance();
		$idManager = Services::getService("Id");
		$repositoryManager = Services::getService("Repository");
		
		$harmoni->request->startNamespace("polyphony-repository");
		
		$repositoryId =$idManager->getId(RequestContext::value("repository_id"));
		$assetId =$idManager->getId(RequestContext::value("asset_id"));
		$recordId =$idManager->getId(RequestContext::value("record_id"));
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
		try {
			$repository =$repositoryManager->getRepository($repositoryId);
			$asset =$repository->getAsset($assetId);
			$record =$asset->getRecord($recordId);
		} catch (UnknownIdException $e) {
			HarmoniErrorHandler::logException($e);
			$this->getUnknownIdMessage();
		}
		
		// Make sure that the structure is the right one.
		$structure =$record->getRecordStructure();
		$remoteFileId =$idManager->getId('REMOTE_FILE');
		$fileId =$idManager->getId('FILE');
		
		if ($remoteFileId->isEqual($structure->getId())) {
			$urlParts =$record->getPartsByPartStructure(
				$idManager->getId("FILE_URL"));
			$urlPart =$urlParts->next();
			header("Location: ".$urlPart->getValue());
		} else if (!$fileId->isEqual($structure->getId())) {
			try {
				throw new Exception("The requested record is not of the FILE structure, and therefore cannot be displayed.");
			} catch (Exception $e) {
				HarmoniErrorHandler::logException($e);
				$this->getUnknownIdMessage();
			}
		} else {
		
			// Get the parts for the record.
			$partIterator =$record->getParts();
			$parts = array();
			while($partIterator->hasNext()) {
				$part =$partIterator->next();
				$partStructure =$part->getPartStructure();
				$partStructureId =$partStructure->getId();
				$parts[$partStructureId->getIdString()] =$part;
			}
			
			$imgProcessor = Services::getService("ImageProcessor");
			
			// Headers for IE so that it won't freak out if saving a file over SSL
			header('Cache-Control: ');
			header('Pragma: ');
			header('Expires: Mon, 26 Jul 1997 05:00:00 GMT');
			
			// If we want to (and can) resize the file, do so
			if (($size || $websafe)
				&& $imgProcessor->isFormatSupported($parts['MIME_TYPE']->getValue())) 
			{
				$imageCache = new RepositoryImageCache($record->getId(), $size, $websafe, $parts);
				
				header("Content-Type: ".$imageCache->getCachedMimeType());
				header('Content-Disposition: attachment; filename="'.
							$imageCache->getCachedFileName().'"');					
				
				print $imageCache->getCachedImageData();
			}
			// Otherwise, just send the original file
			else {
				header("Content-Type: ".$parts['MIME_TYPE']->getValue());
				
				$filename = $parts['FILE_NAME']->getValue();
				if (!ereg("[^\\w]", $filename)) {
					$mime = Services::getService("MIME");
					$extension = $mime->getExtensionForMIMEType($parts['MIME_TYPE']->getValue());
					$filename = _("Untitled").".".$extension;
				}
				
				header('Content-Disposition: attachment; filename="'.$filename.'"');
			
				print $parts['FILE_DATA']->getValue();
			}
		}
		
		$harmoni->request->endNamespace();
		textdomain($defaultTextDomain);
		exit;
	}
}


/**
 * This cache management class is something of a hack, especially in how it 
 * gets the database index from the repository manager. It should be reworked
 * to make use of its own configuration and not reference the repository tables,
 * though this would require the addition of a modification time part to the
 * FILE RecordStructure as well.
 * 
 * @since 2/13/06
 * @package polyphony.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewfile.act.php,v 1.18 2008/03/11 21:00:22 adamfranco Exp $
 */
class RepositoryImageCache {
	
	/**
	 * Constructor
	 * 
	 * @param object Id $id
	 * @param integer $size
	 * @param boolean $websafe
	 * @param ref array $parts
	 * @return object
	 * @access public
	 * @since 2/13/06
	 */
	function RepositoryImageCache ( $id, $size, $websafe, $parts ) {
		$this->_id =$id;
		$this->_size = intval($size);
		$this->_websafe = $websafe;
		$this->_parts =$parts;
	}
	
	/**
	 * Awswer the cached data (updating the cache if necessary).
	 * 
	 
	 * @return string
	 * @access public
	 * @since 2/13/06
	 */
	function getCachedImageData () {
		if (!$this->isCacheUpToDate())
			$this->writeCache();
		
		return $this->readCache();
	}
	
	/**
	 * Awswer the cached data mime-type.
	 * 
	 
	 * @return string
	 * @access public
	 * @since 2/13/06
	 */
	function getCachedMimeType () {
		if (!$this->isCacheUpToDate())
			$this->writeCache();
		
		return $this->_mimeType;
	}
	
	/**
	 * Answer the filename of the cached image
	 * 
	 * @return string
	 * @access public
	 * @since 2/13/06
	 */
	function getCachedFileName () {
		$mime = Services::getService("MIME");
		
		$extension = $mime->getExtensionForMIMEType($this->getCachedMimeType());
		if (eregi("^.+\.".$extension."$", $this->_parts['FILE_NAME']->getValue())) {
			return $this->_parts['FILE_NAME']->getValue();
		} else if (ereg("^[^\\w]+$", $this->_parts['FILE_NAME']->getValue())) {
			return $this->_parts['FILE_NAME']->getValue().".".$extension;
		} else {
			return _("Untitled").".".$extension;
		}
	}
	
	/**
	 * Answer true if the cache is up to date.
	 * 
	 * @return boolean
	 * @access public
	 * @since 2/13/06
	 */
	function isCacheUpToDate () {
		$dbc = Services::getService('DatabaseManager');
		
		$query = new SelectQuery;
		$query->addColumn('dr_mime_type.type', 'mime_type');
		$query->addTable('dr_file');
		$query->addTable('dr_resized_cache', LEFT_JOIN, 'dr_file.id = dr_resized_cache.fk_file');
		$query->addTable('dr_mime_type', LEFT_JOIN, 'dr_resized_cache.fk_mime_type = dr_mime_type.id');
		$query->addWhere("dr_file.id = '".addslashes($this->_id->getIdString())."'");
		$query->addWhere("dr_file.mod_time < dr_resized_cache.cache_time");
		$query->addWhere("dr_resized_cache.size = '".addslashes($this->_size)."'");
		$query->addWhere("dr_resized_cache.websafe = ".(($this->_websafe)?'1':'0'));
		
		$result =$dbc->query($query, $this->getDBIndex());
		if ($result->getNumberOfRows() > 0) {
			$this->_mimeType = $result->field('mime_type');
			$result->free();
			return true;
		} else {
			$this->_mimeType = null;
			$result->free();
			return false;
		}
	}
	
	/**
	 * Answer the cached data
	 * 
	 * @return string
	 * @access public
	 * @since 2/13/06
	 */
	function readCache () {
		$dbc = Services::getService('DatabaseManager');
		
		$query = new SelectQuery;
		$query->addColumn('data');
		$query->addTable('dr_resized_cache');
		$query->addWhere("dr_resized_cache.fk_file = '".addslashes($this->_id->getIdString())."'");
		$query->addWhere("dr_resized_cache.size = '".addslashes($this->_size)."'");
		$query->addWhere("dr_resized_cache.websafe = ".(($this->_websafe)?'1':'0'));
		
		$result =$dbc->query($query, $this->getDBIndex());
		$data = $result->field('data');
		$result->free();
		return $data;
	}
	
	/**
	 * Write the cache
	 * 
	 * @return void
	 * @access public
	 * @since 2/13/06
	 */
	function writeCache () {
		$dbc = Services::getService('DatabaseManager');
		
		$query = new DeleteQuery;
		$query->setTable('dr_resized_cache');
		$query->addWhere("dr_resized_cache.fk_file = '".addslashes($this->_id->getIdString())."'");
		$query->addWhere("dr_resized_cache.size = '".addslashes($this->_size)."'");
		$query->addWhere("dr_resized_cache.websafe = ".(($this->_websafe)?'1':'0'));
		
		$dbc->query($query, $this->getDBIndex());
		
		$query = new InsertQuery;
		$query->setTable('dr_resized_cache');
		$query->setColumns(array(	'fk_file',
								'size',
								'websafe',
								'cache_time',
								'fk_mime_type',
								'data'));
							
		$values = array();
		$values[] = "'".addslashes($this->_id->getIdString())."'";
		$values[] = "'".addslashes($this->_size)."'";
		$values[] = (($this->_websafe)?'1':'0');
		$values[] = "NOW()";
		
		$imgProcessor = Services::getService("ImageProcessor");
		if ($this->_websafe) {
			$this->_mimeType = $imgProcessor->getWebsafeFormat(
									$this->_parts['MIME_TYPE']->getValue());
			$values[] = $this->getMimeKey();
			$values[] = "'".addslashes(
								$imgProcessor->getWebsafeData(
									$this->_parts['MIME_TYPE']->getValue(),
									$this->_size,
									$this->_parts['FILE_DATA']->getValue()))."'";
		} else {
			$this->_mimeType = $imgProcessor->getResizedFormat(
									$this->_parts['MIME_TYPE']->getValue());
			$values[] = $this->getMimeKey();
			$values[] = "'".addslashes(
								$imgProcessor->getResizedData(
									$this->_parts['MIME_TYPE']->getValue(),
									$this->_size,
									$this->_parts['FILE_DATA']->getValue()))."'";
		}
		$query->addRowOfValues($values);
		
		$dbc->query($query, $this->getDBIndex());
	}
	
	/**
	 * Answer the db index to use
	 * 
	 * @return integer
	 * @access public
	 * @since 2/13/06
	 */
	function getDBIndex () {		
		return IMPORTER_CONNECTION;
	}
	
	/**
	 * Answer the mime type key
	 * 
	 * @param string $mimeType
	 * @return integer
	 * @access public
	 * @since 2/13/06
	 */
	function getMimeKey () {
		// If we have a key, make sure it exists.
		if ($this->_mimeType && $this->_mimeType != "NULL") {
			$dbc = Services::getService('DatabaseManager');
			
			// Check to see if the type is in the database
			$query = new SelectQuery;
			$query->addTable("dr_mime_type");
			$query->addColumn("id");
			$query->addWhere("type = '".$this->_mimeType."'");
			$result =$dbc->query($query, $this->getDBIndex());
			
			// If it doesn't exist, insert it.
			if (!$result->getNumberOfRows()) {
				$query = new InsertQuery;
				$query->setTable("dr_mime_type");
				$query->setAutoIncrementColumn("id", "dr_mime_type_id_seq");
				$query->setColumns(array("type"));
				$query->setValues(array("'".addslashes($this->_mimeType)."'"));
				
				$result2 =$dbc->query($query, $this->getDBIndex());
				$mimeId = "'".$result2->getLastAutoIncrementValue()."'";
			} else {
				$mimeId = "'".$result->field("id")."'";
			}
			$result->free();
		} 
		// If we don't have an Id, set the key to NULL.
		else {
			$mimeId = "NULL";
		}
		
		return $mimeId;
	}
	
}

?>