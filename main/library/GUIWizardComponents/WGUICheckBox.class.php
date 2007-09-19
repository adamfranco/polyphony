<?php
/**
 * @since 8/14/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WGUICheckBox.class.php,v 1.3 2007/09/19 14:04:45 adamfranco Exp $
 */ 



/**
 * This class make a checkbox associated with a style property
 * 
 * @since 8/14/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WGUICheckBox.class.php,v 1.3 2007/09/19 14:04:45 adamfranco Exp $
 */
 
class WGUICheckbox
	extends WGUIComponent 
{

	var $_checkedVal;
	var $_unCheckedVal;

	function WGUICheckbox($callBack, $collectionSelector, $styleProperty, $componentClass, $checked, $unchecked){
		
		
		$this->_wizardComponent = new WCheckBox();
		
		$this->_checkedVal = $checked;
		$this->_unCheckedVal = $unchecked;
		
		$this->init($callBack, $collectionSelector, $styleProperty, $componentClass);
	}
	
	/**
	 * copy the value of the Wizard component to the StyleComponent
	 */
	function exportValue(){
		$styleComponent =$this->getStyleComponent();
		if($this->_wizardComponent->getAllValues()){
			$styleComponent->setValue($this->_checkedVal);
		}else{
			$styleComponent->setValue($this->_unCheckedVal);
		}
	}
	
	
	
	/**
	 * copy the value from  the StyleComponent to the wizard component
	 *
	 * Default is unchecked
	 */
	function importValue(){
		$styleComponent =$this->getStyleComponent();		
		if($styleComponent->getValue()==$this->_checkedVal){
			$this->_wizardComponent->setValue(1);
		}elseif($styleComponent->getValue()==$this->_unCheckedVal){
			$this->_wizardComponent->setValue(0);
		}else{
			$this->useDefaultValue();	
		}
	}
	
	/**
	 * Is $value a legitamate value for this wizard component?
	 *
	 * @param string $value the value to test
	 * @return boolean if the value is legitimate 
	 */
	function isPossibleValue($value){
		if($value!= $this->_checkedVal && $value!=$this->_unCheckedVal){
			return false;
		}
		return true;
	}
	
	
}

?>