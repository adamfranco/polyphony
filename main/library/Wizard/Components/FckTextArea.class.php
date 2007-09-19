<?php
/**
 * @since 9/6/07
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FckTextArea.class.php,v 1.3 2007/09/19 14:04:50 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/WTextArea.class.php");
require_once(POLYPHONY.'/javascript/fckeditor/fckeditor.php');

/**
 * The FckTextArea uses the FCKEditor from http://www.fckeditor.net/ to do
 * WYSIWYG editing of text
 * 
 * @since 9/6/07
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: FckTextArea.class.php,v 1.3 2007/09/19 14:04:50 adamfranco Exp $
 */
class FckTextArea
	extends WTextArea
{
		
	/**
	 * Virtual Constructor
	 * @param integer $rows
	 * @param integer $cols
	 * @access public
	 * @return ref object
	 * @static
	 */
	public static function withRowsAndColumns ($rows, $cols, $class = 'FckTextArea') {
		return parent::withRowsAndColumns($rows, $cols, $class);
	}
	
	/**
	 * @var object $editor; 
	 * @access private
	 * @since 9/6/07
	 */
	public $editor;
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 9/5/07
	 */
	public function __construct () {
		parent::__construct();
		
		$this->editor = new FCKeditor(null);
		$this->editor->BasePath = POLYPHONY_PATH."/javascript/fckeditor/" ;
		
		// Set defaults
		$this->editor->Height = '400px';
		
		$this->editor->Config['EnterMode'] = "br";
		$this->editor->Config['ShiftEnterMode'] = "p";
		
		$this->editor->Config['ImageBrowser'] = "false";
		$this->editor->Config['LinkBrowser'] = 'false';
		$this->editor->Config['FlashBrowser'] = 'false';
		
		$this->editor->Config['ImageUpload'] = "false";
		$this->editor->Config['LinkUpload'] = 'false';
		$this->editor->Config['FlashUpload'] = 'false';
		
		$this->editor->Config['LinkDlgHideTarget'] = "false";
		$this->editor->Config['LinkDlgHideAdvanced'] = "false";
		
		$this->editor->Config['ImageDlgHideLink'] = "false";
		$this->editor->Config['ImageDlgHideAdvanced'] = "false";
		
		$this->editor->Config['FlashDlgHideAdvanced'] = "false";
	}
	
	/**
	 * Set a file browser URL and enable uploading. This is a helper method, the
	 * same effect can be accomplished by modifying the editor configuration
	 * directly.
	 * 
	 * @param string $url
	 * @return void
	 * @access public
	 * @since 9/6/07
	 */
	public function enableFileBrowsingAtUrl ( $url) {
		$this->editor->Config['ImageBrowser'] = "true";
		$this->editor->Config['ImageBrowserURL'] = str_replace('&amp;', '&', $url);
		
		$this->editor->Config['FlashBrowser'] = 'true';
		$this->editor->Config['FlashBrowserURL'] = str_replace('&amp;', '&', $url);
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	public function getMarkup ($fieldName) {
		$this->editor->InstanceName = $fieldName;
		$this->editor->Value = $this->_value;		
		$this->editor->Create();
	}
}

?>