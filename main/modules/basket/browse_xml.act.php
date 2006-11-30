<?php
/**
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_xml.act.php,v 1.2 2006/11/30 22:02:43 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");
/**
 * 
 * 
 * @package concerto.modules.asset
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: browse_xml.act.php,v 1.2 2006/11/30 22:02:43 adamfranco Exp $
 */
class browse_xmlAction 
	extends Action
{
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _("Viewing Selection");
	}
	
	/**
	 * Answer the title of this slideshow
	 * 
	 * @return string
	 * @access public
	 * @since 5/4/06
	 */
	function getTitle () {
		return _("Viewing Selection");
	}
	
	/**
	 * Answer the assets to display in the slideshow
	 * 
	 * @return object AssetIterator
	 * @access public
	 * @since 5/4/06
	 */
	function &getAssets () {
		$assets = array();
		$repositoryManager =& Services::getService("Repository");
		
		$basket =& Basket::instance();
		$basket->clean();
		$basket->reset();
		
		while ($basket->hasNext())
			$assets[] =& $repositoryManager->getAsset($basket->next());
		
		$iterator =& new HarmoniIterator($assets);
		return $iterator;
	}
	
	/**
	 * Build the content for this action
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function execute () {						
		/*********************************************************
		 * First print the header, then the xml content, then exit before
		 * the GUI system has a chance to try to theme the output.
		 *********************************************************/		
		header("Content-Type: text/xml; charset=\"utf-8\"");
		
		print<<<END
<?xml version="1.0" encoding="utf-8" ?>
<!DOCTYPE slideshow PUBLIC "- //Middlebury College//Slide-Show//EN" "http://concerto.sourceforge.net/dtds/viewer/2.0/slideshow.dtd">
<slideshow>

END;
		print "\t<title>".$this->getTitle()."</title>\n";
		
		print "\t<media-sizes>\n";
		print "\t\t\t\t<size>small</size>\n";
		print "\t\t\t\t<size>medium</size>\n";
		print "\t\t\t\t<size>large</size>\n";
		print "\t\t\t\t<size>original</size>\n";
		print "\t</media-sizes>\n";
		
		print "\t<default_size>medium</default_size>\n";
	
		
		$assets =& $this->getAssets();
		
		
		$authZ =& Services::getService("AuthZ");
		$idManager =& Services::getService("Id");
		
		while ($assets->hasNext())
			$this->printAssetXML($assets->next());
		
		print "</slideshow>\n";		
		exit;
	}	
	
	/**
	 * Function for printing the asset block of the slideshow XML file
	 * 
	 * @param object Asset $asset
	 * @return void
	 * @access public
	 * @since 10/14/05
	 */
	function printAssetXML( &$asset) {
		
		$assetId =& $asset->getId();
		$harmoni =& Harmoni::instance();
		
		
		// ------------------------------------------
		
		print "\t<slide ";
		print "source='";
		print $harmoni->request->quickURL('collection', 'browse_slide_xml', 
			array('asset_id' => $assetId->getIdString()));
		print "'>\n";
		
		// Text-Position
		print "\t\t<text-position>";
			print "right";
		print "</text-position>\n";		
		
		print "\t</slide>\n";
	}
}
