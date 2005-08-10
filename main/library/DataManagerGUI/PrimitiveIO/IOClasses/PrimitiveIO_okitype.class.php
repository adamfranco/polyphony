<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_okitype.class.php,v 1.4 2005/08/10 13:27:04 gabeschine Exp $
 */

/**
 * 
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_okitype.class.php,v 1.4 2005/08/10 13:27:04 gabeschine Exp $
 */
class PrimitiveIO_okitype extends WizardComponentWithChildren {
	var $_domain;
	var $_authority;
	var $_keyword;

	function PrimitiveIO_okitype() {
		$rule =& new WECRegex("[\\w]+");
		$this->_domain =& new WTextField();
		$this->_domain->setErrorRule($rule);
		$this->_domain->setErrorText(dgettext("polyphony", "Please enter a domain."));
		
		$this->_authority =& new WTextField();
		$this->_authority->setErrorRule($rule);
		$this->_authority->setErrorText(dgettext("polyphony", "Please enter an authority."));
		
		$this->_keyword =& new WTextField();
		$this->_keyword->setErrorRule($rule);
		$this->_keyword->setErrorText(dgettext("polyphony", "Please enter a keyword."));
		
		$this->addComponent("domain", $this->_domain);
		$this->addComponent("authority", $this->_authority);
		$this->addComponent("keyword", $this->_keyword);
	}

	/**
	 * Sets the value of this Component to the {@link SObject} passed.
	 * @param ref object $value The {@link SObject} value to use.
	 *
	 * @return void
	 **/
	function setValueFromSObject(&$value)
	{
		$this->_domain->setValue($value->getDomain());
		$this->_authority->setValue($value->getAuthority());
		$this->_keyword->setValue($value->getKeyword());
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
		$ok = true;
		$children =& $this->getChildren();
		foreach(array_keys($children) as $key) {
			$child =& $children[$key];
			if (!$child->update($fieldName."_".$key)) $ok = false;
		}

		return $ok;
	}

	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$type =& new Type();
		$type->setDomain($this->_domain->getAllValues());
		$type->setAuthority($this->_authority->getAllValues());
		$type->setKeyword($this->_keyword->getAllValues());
		
		return $type;
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
		$m = '';
		$m .= dgettext("polyphony", "Domain") . ": " . $this->_domain->getMarkup($fieldName."_"."domain") . "<br/>\n";
		$m .= dgettext("polyphony", "Authority") . ": " . $this->_authority->getMarkup($fieldName."_"."authority") . "<br/>\n";
		$m .= dgettext("polyphony", "Keyword") . ": " . $this->_keyword->getMarkup($fieldName."_"."keyword") . "<br/>\n";
		return $m;
	}
}
