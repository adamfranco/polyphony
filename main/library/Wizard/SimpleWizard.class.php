<?php

/**
 * @since Jul 19, 2005
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleWizard.class.php,v 1.8 2007/10/10 22:58:54 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/Wizard/Wizard.abstract.php");

/**
 * The SimpleWizard is a {@link WizardClass} which contains children and a block of formatting text in which to include those children.
 * 
 * @since Jul 19, 2005
 * @package polyphony.wizard
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleWizard.class.php,v 1.8 2007/10/10 22:58:54 adamfranco Exp $
 */
class SimpleWizard extends Wizard {
	var $_text;
	
	/**
	 * Returns a new SimpleWizard object with the text supplied.
	 * @static
	 * @param string $text
	 * @access public
	 * @return ref object
	 */
	static function withText ($text, $class = 'SimpleWizard') {
		$obj = new $class();
		$obj->_text = $text;
		return $obj;
	}
	
	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup ($fieldName) {
		$fromParent = parent::getMarkup($fieldName);
		// make sure that we add the form info to the markup
		$harmoni = Harmoni::instance();
		$urlObj =$harmoni->request->mkURL();
		$url = $urlObj->write();
		$formName = $this->getWizardFormName();
		$pre = "<form action='$url' method='post' name='$formName' id='$formName' enctype='multipart/form-data'>\n";
		$post = "\n</form>\n";
		// ignore the field name
		return $fromParent . $pre.Wizard::parseText($this->_text, $this->getChildren(), $this->getIdString()."_").$post;
	}
}

?>