<?php

/**
 * @package polyphony.modules.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewfile.act.php,v 1.9 2006/02/13 22:29:51 adamfranco Exp $
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
 * @version $Id: viewfile.act.php,v 1.9 2006/02/13 22:29:51 adamfranco Exp $
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
				$imageCache =& new RepositoryImageCache($record->getId(), $size, $websafe, $parts);
				
				header("Content-Type: ".$imageCache->getCachedMimeType());
				header('Content-Disposition: attachment; filename="'.
							$imageCache->getCachedFileName().'"');					
				
				print $imageCache->getCachedImageData();
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


/**
 * This cache management class is something of a hack, especially in how it 
 * gets the database index from the repository manager. It should be reworked
 * to make use of its own configuration and not reference the repository tables,
 * though this would require the addition of a modification time part to the
 * FILE RecordStructure as well.
 * 
 * @since 2/13/06
 * @package polyphony.modules.repository
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: viewfile.act.php,v 1.9 2006/02/13 22:29:51 adamfranco Exp $
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
	function RepositoryImageCache ( &$id, $size, $websafe, &$parts ) {
		$this->_id =& $id;
		$this->_size = intval($size);
		$this->_websafe = $websafe;
		$this->_parts =& $parts;
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
		$mime =& Services::getService("MIME");
		
		$extension = $mime->getExtensionForMIMEType($this->getCachedMimeType());
		if (ereg("^.+\.".$extension."$", $this->_parts['FILE_NAME']->getValue())) {
			return $this->_parts['FILE_NAME']->getValue();
		} else {
			return $this->_parts['FILE_NAME']->getValue().".".$extension;
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
		$dbc =& Services::getService('DatabaseManager');
		
		$query =& new SelectQuery;
		$query->addColumn('dr_mime_type.type', 'mime_type');
		$query->addTable('dr_file');
		$query->addTable('dr_resized_cache', LEFT_JOIN, 'dr_file.id = dr_resized_cache.FK_file');
		$query->addTable('dr_mime_type', LEFT_JOIN, 'dr_resized_cache.FK_mime_type = dr_mime_type.id');
		$query->addWhere("dr_file.id = '".addslashes($this->_id->getIdString())."'");
		$query->addWhere("dr_file.mod_time < dr_resized_cache.cache_time");
		$query->addWhere("dr_resized_cache.size = '".addslashes($this->_size)."'");
		$query->addWhere("dr_resized_cache.websafe = ".(($this->_websafe)?'1':'0'));
		
		$result =& $dbc->query($query, $this->getDBIndex());
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
		$dbc =& Services::getService('DatabaseManager');
		
		$query =& new SelectQuery;
		$query->addColumn('data');
		$query->addTable('dr_resized_cache');
		$query->addWhere("dr_resized_cache.FK_file = '".addslashes($this->_id->getIdString())."'");
		$query->addWhere("dr_resized_cache.size = '".addslashes($this->_size)."'");
		$query->addWhere("dr_resized_cache.websafe = ".(($this->_websafe)?'1':'0'));
		
		$result =& $dbc->query($query, $this->getDBIndex());
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
		$dbc =& Services::getService('DatabaseManager');
		
		$query =& new DeleteQuery;
		$query->setTable('dr_resized_cache');
		$query->addWhere("dr_resized_cache.FK_file = '".addslashes($this->_id->getIdString())."'");
		$query->addWhere("dr_resized_cache.size = '".addslashes($this->_size)."'");
		$query->addWhere("dr_resized_cache.websafe = ".(($this->_websafe)?'1':'0'));
		
		$dbc->query($query, $this->getDBIndex());
		
		$query =& new InsertQuery;
		$query->setTable('dr_resized_cache');
		$query->setColumns(array(	'FK_file',
								'size',
								'websafe',
								'cache_time',
								'FK_mime_type',
								'data'));
							
		$values = array();
		$values[] = "'".addslashes($this->_id->getIdString())."'";
		$values[] = "'".addslashes($this->_size)."'";
		$values[] = (($this->_websafe)?'1':'0');
		$values[] = "NOW()";
		
		$imgProcessor =& Services::getService("ImageProcessor");
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
		$repositoryManager =& Services::getService('Repository');
		$configuration =& $repositoryManager->_configuration;
		return $configuration->getProperty('database_index');
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
			$dbc =& Services::getService('DatabaseManager');
			
			// Check to see if the type is in the database
			$query =& new SelectQuery;
			$query->addTable("dr_mime_type");
			$query->addColumn("id");
			$query->addWhere("type = '".$this->_mimeType."'");
			$result =& $dbc->query($query, $this->getDBIndex());
			
			// If it doesn't exist, insert it.
			if (!$result->getNumberOfRows()) {
				$query =& new InsertQuery;
				$query->setTable("dr_mime_type");
				$query->setColumns(array("type"));
				$query->setValues(array("'".addslashes($this->_mimeType)."'"));
				
				$result2 =& $dbc->query($query, $this->getDBIndex());
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