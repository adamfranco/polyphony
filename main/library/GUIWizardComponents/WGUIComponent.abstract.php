<?php
/**
 * @since 8/14/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WGUIComponent.abstract.php,v 1.3 2007/09/19 14:04:45 adamfranco Exp $
 */ 



/**
 * This class is an abstract class for associating StyleComponents with a wizard component.
 * 
 * @since 8/14/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WGUIComponent.abstract.php,v 1.3 2007/09/19 14:04:45 adamfranco Exp $
 */
 
class WGUIComponent 
	extends WizardComponent 
{

	var $_wizardComponent;
	
	var $_getThemeCallBack;
	var $_collection;
	var $_property;
	var $_component;
	
	/**
	*The default value passed in with the StyleProperty
	*/
	var $_defValue;

	/**
	 * Initialize all the variables.  Should be called last in the constructor.
	 * Requires that the collection and theme are created
	 */
	function init ($callBack, $collectionSelector, $styleProperty, $componentClass) {
		
		//set various variables
		$this->_getThemeCallBack = $callBack;
		$this->_collection = $collectionSelector;
		$this->_property = $styleProperty->getName();
		$this->_component = $componentClass;
		
		//get the colelction
		$collection =$this->getStyleCollection();	
		
		//get the property, using the default one, if need be.  Then check to make sure we've succeeded.
		$property =$collection->getStyleProperty($this->_property);		
		if(is_null($property)){
			$collection->addSP($styleProperty);
			$property =$styleProperty;
		}
		$rule = ExtendsValidatorRule::getRule("StyleProperty");
		if(!$rule->check($property)){
			throwError(new Error("Failed to get StyleProperty","GUIWizardComponents",true));
		}	
		
		//get the first value in case the next value is not legitamate
		$componentTemp =$styleProperty->getStyleComponent($componentClass);		
		$rule = ExtendsValidatorRule::getRule("StyleComponent");
		if(!$rule->check($componentTemp)){
			throwError(new Error("Passed in Style property must have a component of given class, '".$componentClass."'","GUIWizardComponents",true));
		}
		$this->_defaultValue = $componentTemp->getValue();
		if(!$this->isPossibleValue($this->_defaultValue)){
			throwError(new Error("Passed in StyleComponent must have an acceptable value for the WizardComponent.","GUIWizardComponents",true));
		}
		
		//get the component, using the default one, if need be.  Then check to make sure we've succeeded.
		$component =$property->getStyleComponent($this->_component);
		if(is_null($component)){
			$property->addSC($styleProperty->getStyleProperty($componentClass));
		}
		
		$this->importValue();
	}
	
	function useDefaultValue(){
		$this->_wizardComponent->setValue($this->_defValue);		
		//@todo this actually could destroy styles made elsewhere.  Of course if we don't update immeadiately, the next pageload will probably update anyway.  Is there a good way to fix this?
		$this->exportValue();
	}
	
	function getStyleComponent(){
		//eval('$theme = '.$this->_getThemeCallBack."();");		
		//$collection =$theme->getStyleCollection($this->_collection);eval('$theme = '.$this->_getThemeCallBack."();");		
		$collection =$this->getStyleCollection();	
		$property =$collection->getStyleProperty($this->_property);
		return $property->getStyleComponent($this->_component);		
	}
	
	
	function getStyleCollection(){
		eval('$theme = '.$this->_getThemeCallBack."();");
		
		
		$rule = ExtendsValidatorRule::getRule("Theme");
		if(!$rule->check($theme)){
			throwError(new Error("Callback ".$this->_getThemeCallBack."() did not return a theme ","GUIWizardComponents",true));
		}		
		$collection =$theme->getStyleCollection($this->_collection);
		$rule = ExtendsValidatorRule::getRule("StyleCollection");
		if(!$rule->check($collection)){
			throwError(new Error("Theme ".$theme->getDisplayName()." did not have StyleCollection ".$this->_collection,"GUIWizardComponents",true));
		}	
		return $collection;		
	}
	
	
	
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * @access public
	 * @return boolean
	 */
	function validate () {		
		return $this->_wizardComponent->validate();
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
		$this->_wizardComponent->update($fieldName);
		//$styleComponent =$this->getStyleComponent();			
		//$val = $this->getAllValues();
		/*$rule =$styleComponent->getRule();
		if($rule->check($val)){
				$styleComponent->setValue($val);
			}else{
				print "<br />failed: '".$val."'!";
		}*/		
		//$styleComponent->setValue($val);

		$this->exportValue();
		return true;
	}
	
	/**
	 * copy the value of the Wizard component to the StyleComponent
	 */
	function exportValue(){
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	/**
	 * copy the value from  the StyleComponent to the wizard component
	 */
	function importValue(){
		$styleComponent =$this->getStyleComponent();
		$val = $styleComponent->getValue();
		if($this->isPossibleValue($val)){
			$this->_wizardComponent->setValue($val);
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
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
	
	
	
	/**
	 * Returns the values of wizard-components. It may return the value of the 
	 * wizard component, but this may be overridden.
	 * 
	 * @access public
	 * @return mixed
	 */
	function getAllValues () {
		$ret = $this->_wizardComponent->getAllValues();
		return $ret;
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
		$s="";
		$s.= $this->_wizardComponent->getMarkup($fieldName);
		return $s;
	}
	
			
	
}

?>