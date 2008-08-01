<?php
/**
 * @since 9/27/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.11 2007/09/19 14:04:54 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");
require_once(POLYPHONY."/main/library/Exporter/XMLAssetExporter.class.php");

/**
 * This is the export action for an asset
 * 
 * @since 9/27/05
 * @package polyphony.basket
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: export.act.php,v 1.11 2007/09/19 14:04:54 adamfranco Exp $
 */
class exportAction 
	extends MainWindowAction
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 9/27/05
	 */
	function isAuthorizedToExecute () {
		$harmoni = Harmoni::instance();
		$authZ = Services::getService("AuthZ");
		$idManager = Services::getService("Id");

		$basket = Basket::instance();
		$basket->reset();
		$view =$idManager->getId("edu.middlebury.authorization.view");
		$this->_exportList = array();

		while ($basket->hasNext()) {
			$id =$basket->next();
			if ($authZ->isUserAuthorized($view, $id))
				$this->_exportList[] = $id;
		}

		if (count($this->_exportList) > 0)
			return true;
		else
			return false;
	}
	
	/**
	 * Return the "unauthorized" string to pring
	 * 
	 * @return string
	 * @access public
	 * @since 9/27/05
	 */
	function getUnauthorizedMessage () {
		return dgettext("polyphony", "You are not authorized to export ANY of these <em>Assets</em>.");
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 9/28/05
	 */
	function getHeadingText () {
		$harmoni = Harmoni::Instance();
		$idManager = Services::getService("Id");

		return dgettext("polyphony", "Export the <em>Assets</em> in the Selection");
	}

	/**
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 9/27/05
	 */
	function buildContent () {
		$harmoni = Harmoni::Instance();

		$centerPane =$this->getActionRows();

		$authN = Services::getService("AuthN");
		$authTypes =$authN->getAuthenticationTypes();
		$uniqueString = "";
		while($authTypes->hasNext()) {
			$authType =$authTypes->next();
			$uniqueString .= "_".$authN->getUserId($authType);
		}

		$cacheName = 'export_asset_wizard'.$uniqueString;
		
		$this->runWizard ( $cacheName, $centerPane );
	}
		
	/**
	 * Create a new Wizard for this action. Caching of this Wizard is handled by
	 * {@link getWizard()} and does not need to be implemented here.
	 * 
	 * @return object Wizard
	 * @access public
	 * @since 9/28/05
	 */
	function createWizard () {
		// Instantiate the wizard, then add our steps.
		$wizard = SimpleWizard::withText(
			"\n<h3>"._("Click <em>Export</em> to Export the Selection")."</h3>".
			"\n<br/>"._("The current content of the Selection will be exported and presented as an archive for download.  Once the archive is downloaded click <em>Cancel</em> to go back.").
			"\n<br/><h3>"._("Archive:")."</h3>".
			"<table border='0' style='margin-top:20px' >\n" .
			"\n<tr><td>"._("Archive Name: ")."</td>".
			"<td>[[filepath]]</td></tr>".
			"\n<tr><td>"._("Compression: ")."</td>".
			"<td>[[compression]]</td></tr>".
			"<tr>\n" .
			"<td align='left'>\n" .
			"[[_cancel]]".
			"</td>\n" .
			"<td align='right'>\n" .
			"[[_save]]".
			"</td></tr></table>");
		
		// Create the properties.
		$fileNameProp =$wizard->addComponent("filepath", new WTextField());

// 		$datefield =$wizard->addComponent("effective_date", new WTextField());
// 		$date = DateAndTime::Now();
// 		$datefield->setValue($date->asString());		
// 
// 		$date2field =$wizard->addComponent("expiration_date", new WTextField());
// 		
// 		if (is_object($date2))
// 			$date2field->setValue($date->asString());
		
 		$type =$wizard->addComponent("compression", new WSelectList());
 		$type->setValue(".tar.gz");
 		$type->addOption(".tar.gz", dgettext("polyphony", "gzip"));
//  		$type->addOption(".zip", _("zip"));
//  		$type->addOption(".bz2", _("bz2"));

		$save =$wizard->addComponent("_save", 
			WSaveButton::withLabel("Export"));
		$cancel =$wizard->addComponent("_cancel", new WCancelButton());

		return $wizard;
	}
		
	/**
	 * Save our results. Tearing down and unsetting the Wizard is handled by
	 * in {@link runWizard()} and does not need to be implemented here.
	 * 
	 * @param string $cacheName
	 * @return boolean TRUE if save was successful and tear-down/cleanup of the
	 *		Wizard should ensue.
	 * @access public
	 * @since 4/28/05
	 */
	function saveWizard ( $cacheName ) {
		$wizard =$this->getWizard($cacheName);
				
		$properties = $wizard->getAllValues();
		// instantiate new exporter
		$exporter = XMLAssetExporter::withCompression(
			$properties['compression']);
			
		$dir = $exporter->exportList($this->_exportList);
		
		$this->_archiveFile = $exporter->compressWithStatus();
		$this->_archiveFileKey = str_replace('.', '', basename($this->_archiveFile, $properties['compression']));
		
// 		printpre($this->_archiveFile);
		
		// For security, only files listed in the following array will be allowed
		// to be downloaded.
		if (!isset($_SESSION['EXPORTED_FILES']))
			$_SESSION['EXPORTED_FILES'] = array();
		
		
		$_SESSION['EXPORTED_FILES'][$this->_archiveFileKey] = array(
			'file' => $this->_archiveFile,
			'name' => basename($properties['filepath'].$properties['compression']),
			'mime' => 'application/x-gzip');

		return TRUE;
	}
	
	/**
	 * Return the URL that this action should return to when completed.
	 * 
	 * @return string
	 * @access public
	 * @since 4/28/05
	 */
	function getReturnUrl () {
		$harmoni = Harmoni::instance();
				
		$return = $harmoni->request->quickURL("basket", "view");
		
		if (isset($this->_archiveFile) && $this->_archiveFile) {
			$harmoni->request->startNamespace('export');
			$downloadUrl = $harmoni->request->quickURL("export", "getFile",
					array('file' => urlencode($this->_archiveFileKey)));
			$harmoni->request->endNamespace();
			print "<div>"._("Your download should begin momentarily. If it does not, please click the download link below.")."</div>";
			print "<div style='margin: 10px; margin-left: 20px;'><a href='".$downloadUrl."'>"._("Download")."</a></div>";
			
			print "<div style=''><a href='".$return."'>"._("&lt;-- Return")."</a></div>";
			
			while(ob_get_level())
				ob_end_flush();
			
			flush();
			
			$harmoni->request->sendTo($downloadUrl);
			exit;
		} else
			return $return;
	}
}

?>
