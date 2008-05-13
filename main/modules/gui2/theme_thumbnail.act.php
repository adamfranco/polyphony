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

require_once(dirname(__FILE__).'/theme_image.act.php');

/**
 * Answer a thumbnail image for a theme.
 * 
 * @since 5/6/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class theme_thumbnailAction
	extends theme_imageAction
{
		
	/**
	 * Answer the image requested
	 * 
	 * @return object Harmoni_Filing_FileInterface
	 * @access protected
	 * @since 5/13/08
	 */
	protected function getImage () {
		if (!isset($this->image)) {
			$this->image = $this->getTheme()->getThumbnail();
		}
		return $this->image;
	}
	
}

?>