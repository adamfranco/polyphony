<?php
/**
 * @since 5/6/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */ 

require_once(POLYPHONY.'/main/library/AbstractActions/ConditionalGetAction.abstract.php');

/**
 * Answer a css file for a theme
 * 
 * @since 5/6/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class theme_cssAction
	extends ConditionalGetAction
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
	 * Answer the last-modified timestamp for this action/id.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 5/13/08
	 */
	public function getModifiedDateAndTime () {
		if (!isset($this->modDate))
			$this->modDate = $this->getTheme()->getModificationDate();
		return $this->modDate;
	}
	
	/**
	 * Answer the delay (in seconds) that the modification time should be cached without
	 * checking the source again. 
	 * 
	 * @return object Duration
	 * @access public
	 * @since 5/13/08
	 */
	public function getCacheDuration () {
		// A default of 1 minute is used. Override this method to add longer
		// or shorter times.
		return Duration::withHours(2);
	}
	
	/**
	 * Output the content
	 * 
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function outputContent () {
		$theme = $this->getTheme();		
		$css = $theme->getCSS();
		
		header("Content-Type: text/css");
		header("Content-Length: ".strlen($css));
		print $css;
		exit;
	}
	
	/**
	 * Answer the theme
	 * 
	 * @return object Harmoni_Gui2_ThemeInterface
	 * @access protected
	 * @since 5/13/08
	 */
	protected function getTheme () {
		if (!isset($this->theme)) {
			$guiMgr = Services::getService("GUIManager");
			$this->theme = $guiMgr->getTheme(RequestContext::value('theme'));
			
			// If the theme has non-default options, we need to get those.
			if ($this->theme->supportsOptions()) {
				$optionsSess = $this->theme->getOptionsSession();
				$harmoni = Harmoni::instance();
				$harmoni->request->startNamespace('theme_options');
				foreach ($optionsSess->getOptions() as $option) {
					if (RequestContext::value($option->getIdString()))
						$option->setValue(RequestContext::value($option->getIdString()));
				}
				$harmoni->request->endNamespace();
			}
		}
		return $this->theme;
	}
	
}

?>