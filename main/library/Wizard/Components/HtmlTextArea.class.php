<?php
/**
 * @since 9/5/07
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HtmlTextArea.class.php,v 1.2 2007/09/06 17:23:26 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/FckTextArea.class.php");

/**
 * The HtmlTextArea is a text input block that allows the user to use one of several
 * editing interfaces to aid in the entry of HTML text. Some of the editing interfaces
 * may be WYSIWYG editors, while others may simply supply shortcut buttons for
 * entering HTML markup.
 * 
 * @since 9/5/07
 * @package polyphony.library.wizard
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HtmlTextArea.class.php,v 1.2 2007/09/06 17:23:26 adamfranco Exp $
 */
class HtmlTextArea
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
	public static function withRowsAndColumns ($rows, $cols, $class = 'HtmlTextArea') {
		return parent::withRowsAndColumns($rows, $cols, $class);
	}
	
	/**
	 * @var object $editorChoice; A slect field for the editor choice  
	 * @access private
	 * @since 9/5/07
	 */
	private $editorChoice;
	
	/**
	 * @var array $editors;  
	 * @access private
	 * @since 9/5/07
	 */
	private $editors = array();
	
	/**
	 * Constructor
	 * 
	 * @return void
	 * @access public
	 * @since 9/5/07
	 */
	public function __construct () {
		parent::__construct();
		
		$this->editorChoice = new WSelectList;
		$this->editorChoice->addOnChange('this.form.submit();');
		
		$this->addEditor('none', _('None'), new WTextArea);
		$this->addEditor('fck', _('FCK Editor'), new FckTextarea);
		
		$this->chooseEditor('none');
	}
	
	/**
	 * Add a new editor to those supported
	 * 
	 * @param string $name
	  * @param string $displayName
	 * @param object WTextArea $editor
	 * @return void
	 * @access public
	 * @since 9/5/07
	 */
	public function addEditor ($name, $displayName, WTextArea $editor) {
		if (!$name)
			throw new Exception("Name must be a non-zero-length string. '$name' is invalid.");
		if (!$displayName)
			throw new Exception("DisplayName must be a non-zero-length string. '$displayName' is invalid.");
		
		$this->editorChoice->addOption($name, $displayName);
		$this->editors[$name] = $editor;
	}
	
	/**
	 * Choose an editor.
	 * 
	 * @param string $name
	 * @return void
	 * @access public
	 * @since 9/5/07
	 */
	public function chooseEditor ($name) {
		if (!isset($this->editors[$name]))
			throw new Exception("Unknown editor, '$name'.");
		
		$this->editorChoice->setValue($name);
		
		$editor = $this->getCurrentEditor();
		$editor->setRows($this->_rows);
		$editor->setColumns($this->_cols);
		$editor->setValue($this->_value);
		$editor->setStartingDisplayText($this->_startingDisplay);
		$editor->setStyle($this->_style);
		$editor->_onchange = $this->_onchange;
		
	}
	
	/**
	 * Answer the current Editor
	 * 
	 * @return object WTextArea
	 * @access public
	 * @since 9/5/07
	 */
	public function getCurrentEditor () {
		if (!$this->editorChoice->getAllValues())
			throw new Exception("No current editor set.");
		
		if (!isset($this->editors[$this->editorChoice->getAllValues()]))
			throw new Exception("Current editor object is missing.");
			
		return $this->editors[$this->editorChoice->getAllValues()];
	}
	
	/**
	 * Sets the number of visible rows in this textarea.
	 * @param integer $rows
	 * @access public
	 * @return void
	 */
	public function setRows ($rows) {
		parent::setRows($rows);
		$this->getCurrentEditor()->setRows($rows);
	}
	
	/**
	 * Sets the number of visible columns in this textarea.
	 * @param integer $cols
	 * @access public
	 * @return void
	 */
	public function setColumns ($cols) {
		parent::setColumns($cols);
		$this->getCurrentEditor()->setColumns($cols);
	}
	
	/**
	 * Sets the text of the field to display until the user enters the field.
	 * @param string $text
	 * @access public
	 * @return void
	 */
	function setStartingDisplayText ($text) {
		parent::setStartingDisplayText($text);
		$this->getCurrentEditor()->setStartingDisplayText($text);
	}
	
	/**
	 * Sets the CSS style of this field.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle ($style) {
		parent::setStyle($style);
		$this->getCurrentEditor()->setStyle($style);
	}
	
	/**
	 * Add commands to the javascript onchange attribute.
	 * @param string $commands
	 * @access public
	 * @return void
	 */
	function addOnChange($commands) {
		parent::addOnChange($commands);
		$this->getCurrentEditor()->addOnChange($commands);
	}
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * @access public
	 * @return boolean
	 */
	function validate () {
		return $this->getCurrentEditor()->validate();
	}
	
	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update ($fieldName) {
		$this->getCurrentEditor()->update($fieldName);
		$this->_value = $this->getCurrentEditor()->getAllValues();
		
		$oldEditor = $this->editorChoice->getAllValues();
		$this->editorChoice->update($fieldName.'_choice');
		if ($oldEditor != $this->editorChoice->getAllValues())
			$this->chooseEditor($this->editorChoice->getAllValues());
	}
	
	/**
	 * Sets the value of this text field.
	 * @param string $value
	 * @access public
	 * @return void
	 */
	function setValue ($value) {
		parent::setValue($value);
		$this->getCurrentEditor()->setValue($value);
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
		ob_start();
		print "\n<div class='Wizard_HtmlTextArea_choice'>";
		print _("Choose editor: ");
		print $this->editorChoice->getMarkup($fieldName.'_choice');
		print "\n</div>";
		print $this->getCurrentEditor()->getMarkup($fieldName);
		return ob_get_clean();
	}
}

?>