<?php
/**
 * @since 5/7/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/MainWindowAction.class.php');

/**
 * Display a list of available themes.
 * 
 * @since 5/7/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class theme_listAction
	extends MainWindowAction
{
		
	/**
	 * AuthZ
	 * 
	 * @return boolean
	 * @access public
	 * @since 5/6/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute
	 * 
	 * @return null
	 * @access public
	 * @since 5/7/08
	 */
	public function buildContent () {
		$rows = $this->getActionRows();
		$rows->add(new Heading(_("Themes"), 1));
		
		$guiMgr = Services::getService('GUIManager');
		if (!method_exists($guiMgr, 'getThemeSources'))
			throw new Exception('This action only works with Gui2.');
		
		$themes = $guiMgr->getThemes();
		
		$resultPrinter = new ArrayResultPrinter($themes, 1, 10, array($this, 'getThemeListing'));
		$rows->add($resultPrinter->getLayout());
	}
	
	/**
	 * Answer a gui component for this theme listing
	 * 
	 * @param object Harmoni_Gui2_ThemeInterface $theme
	 * @return object ComponentInterface
	 * @access public
	 * @since 5/7/08
	 */
	public function getThemeListing (Harmoni_Gui2_ThemeInterface $theme) {
		ob_start();
		try {
			print "\n\t<h2>".$theme->getDisplayName()."</h2>";
		} catch (UnimplementedException $e) {
			print "\n\t<div style='font-style: italic'>"._("Display-Name not available.")."</div>";
		}
		try {
			$thumb = $theme->getThumbnail();
			$harmoni = Harmoni::instance();
			print "\n\t<img src='".$harmoni->request->quickUrl('gui2', 'theme_thumbnail', array('theme' => $theme->getIdString()))."' style='float: left; width: 200px; margin-right: 10px;'/>";
		} catch (UnimplementedException $e) {
			print "\n\t<div style='font-style: italic'>"._("Thumbnail not available.")."</div>";
		} catch (OperationFailedException $e) {
			print "\n\t<div style='font-style: italic'>"._("Thumbnail not available.")."</div>";
		}
		print _("Id").": ".$theme->getIdString();
		try {
			print "\n\t<p>".$theme->getDescription()."</p>";
		} catch (UnimplementedException $e) {
			print "\n\t<div style='font-style: italic'>"._("Description not available.")."</div>";
		}
		
		try {
			$history = $theme->getHistory();
			print "\n\t<h4>"._("History")."</h4>";
			print "\n\t<ul>";
			foreach ($history as $entry) {
				print "\n\t\t<li style='margin-left: 20px;'>";
				print $entry->getDateAndTime()->ymdString();
				print " - ";
				print $entry->getName();
				print "<br/><em>".$entry->getComment()."</em>";
				print "</li>";
			}
			print "\n\t</ul>";
		} catch (UnimplementedException $e) {
			print "\n\t<div style='font-style: italic'>"._("History not available.")."</div>";
		}
		
		return new Block(ob_get_clean(), STANDARD_BLOCK);
		
	}
}

?>