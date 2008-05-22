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

require_once(dirname(__FILE__).'/theme_css.act.php');

/**
 * <##>
 * 
 * @since 5/6/08
 * @package polyphony.gui2
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id$
 */
class theme_imageAction
	extends theme_cssAction
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
	 * Answer the last-modified timestamp for this action/id.
	 * 
	 * @return object DateAndTime
	 * @access public
	 * @since 5/13/08
	 */
	public function getModifiedDateAndTime () {
		try {
			return $this->getImage()->getModificationDate();
		} 
		// Don't log errors as any garbage CSS could be pointing to things
		// that don't exist.
		catch (UnknownIdException $e) {
			header('HTTP/1.1 404 Not Found');
			$this->getUnknownIdMessage();
		}
	}
	
	/**
	 * Output the content
	 * 
	 * @return null
	 * @access public
	 * @since 5/6/08
	 */
	public function outputContent () {
		try {		
			$image = $this->getImage();
			header("Content-Type: ".$image->getMimeType());
			header("Content-Length: ".$image->getSize());
			print $image->getContents();
		
		} 
		// Don't log errors as any garbage CSS could be pointing to things
		// that don't exist.
		catch (UnknownIdException $e) {
			header('HTTP/1.1 404 Not Found');
			$this->getUnknownIdMessage();
		}
		exit;
	}
	
	/**
	 * Answer the image requested
	 * 
	 * @return object Harmoni_Filing_FileInterface
	 * @access protected
	 * @since 5/13/08
	 */
	protected function getImage () {
		if (!isset($this->image)) {
			$this->image = $this->getTheme()->getImage(RequestContext::value('file'));
		}
		return $this->image;
	}
	
}

?>