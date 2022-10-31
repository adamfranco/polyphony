<?php
/**
 * @since 8/09/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStyleComponent.class.php,v 1.3 2007/09/19 14:04:46 adamfranco Exp $
 */ 



/**
 * This class allows for the creation of a Style Component selector.
 * 
 * @since 8/09/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStyleComponent.class.php,v 1.3 2007/09/19 14:04:46 adamfranco Exp $
 */
 
class WStyleComponent 
	extends ErrorCheckingWizardComponent 
{

	var $_wizardComponent;
	
	var $_getThemeCallBack;
	var $_collection;
	var $_property;
	var $_component;
	
	var $_showError;
	
	
	function __construct ($callBack, $component, $property, $collection) {
		$this->_getThemeCallBack = $callBack;
		$this->_collection = $collection;
		$this->_property = $property;
		$this->_component = $component;
		
		$styleComponent =$this->getStyleComponent();
		
		
		$rule = $styleComponent->getRule();
		
		$regex = $rule->getRegularExpression();
		
		
		
		
		//$this->setErrorText($styleComponent->getErrorDescription());
		//$this->setErrorRule($styleComponent->getRule());
		//$this->_showError=false;
		
		if(is_null($styleComponent)){
			throwError(new HarmoniError("The Component this references is based on cannot be null","WStyleComponent",true));
		}
	
		if (get_class($styleComponent) == 'colorsc') {
			$input = new WSelectOrNew();
			$input->addOption('', "(not set)");
			
			
			
			$digits = array("0","4","8","C","F");
			for($r =0; $r<count($digits); $r++){
				for($g =0; $g<count($digits); $g++){
					for($b =0; $b<count($digits); $b++){
						$val = '#'.$digits[$r].$digits[$g].$digits[$b];
						
						$arr = $styleComponent->getRGBArray($val);
			
						//num measures the "brightness" of the color.
						//I think green is "brighter" than red and red "brighter" than blue.
						$num = $arr[0]*3+$arr[1]*2+$arr[2];
						//Our threshold is 750.
						if($num > 750){
							$col = "000";
						}else{
							$col = "#FFF";
						}
						$input->addOption($val, $val, "color: ".$col."; background-color:".$val.";");
					}
				}
			}
			
			
			

			// make sure the current color is a possibility.
			if (!is_null($styleComponent->getValue())){
				//$input->addOption($styleComponent->getValue(), $styleComponent->getValue(), "color: ".$col."; background-color:".$styleComponent->getValue().";");
				$input->setValue($styleComponent->getValue());
			}
			
					
			// 			if ([[colorwheel colors]])
 			// generate options for colors
		} else if ($styleComponent->isLimitedToOptions()) {
			$input = new WSelectList();
			$input->addOption('', "(not set)");
			$options = $styleComponent->getOptions();
			foreach ($options as $opt) {
				$input->addOption($opt, $opt, strtolower(preg_replace("/[^a-zA-Z0-9:_-]/", "-", $styleComponent->getDisplayName())).": $opt;");
			}
			$input->setValue($styleComponent->getValue());
		} else if ($styleComponent->hasOptions()) {
			$input = new WSelectOrNew();
			$input->addOption('', "(not set)");
			$options = $styleComponent->getOptions();
			foreach ($options as $opt) {
				$input->addOption($opt, $opt, strtolower(preg_replace("/[^a-zA-Z0-9:_-]/", "-", $styleComponent->getDisplayName())).": $opt;");
			}
			$input->setValue($styleComponent->getValue());
		} else {	
			$input = new WTextField();
			$input->setValue($styleComponent->getValue());

		}
		
		if(!$styleComponent->isLimitedToOptions()){			
			$input->setErrorRule(new WECRegex($regex));
			$input->setErrorText($styleComponent->getErrorDescription());	
		}
		
		
		
		
		$this->_wizardComponent =$input;
		
		
		
		
		
		
		
	}
	
	
	function getStyleComponent(){

		eval('$theme = '.$this->_getThemeCallBack."();");		
		$collection =$theme->getStyleCollection($this->_collection);
		//print " ".$this->_collection;
		//print " ".$this->_property;
		

		
		$property =$collection->getStyleProperty($this->_property);
		return $property->getStyleComponent($this->_component);
		
		
	}
	
	
	
	/*
	function getWizardRepresentation () {
		if (get_class($this) == 'colorsc') {
			$input = new WSelectOrNew();
			$input->addOption('', "(not set)");
			// make sure the current color is a possibility.
			if (!is_null($this->_value)){
				$input->addOption($this->_value, $this->_value, "background-color:$this->_value;");
				$input->setValue($this->_value);
			}
			// 			if ([[colorwheel colors]])
 			// generate options for colors
		} else if ($this->_limitedToOptions) {
			$input = new WSelectList();
			$input->addOption('', "(not set)");
			foreach ($this->_options as $opt) {
				$input->addOption($opt, $opt, strtolower(preg_replace("/[^a-zA-Z0-9:_-]/", "-", $this->_displayName)).": $opt;");
			}
			$input->setValue($this->_value);
		} else if ($this->hasOptions()) {
			$input = new WSelectOrNew();
			$input->addOption('', "(not set)");
			foreach ($this->_options as $opt) {
				$input->addOption($opt, $opt, strtolower(preg_replace("/[^a-zA-Z0-9:_-]/", "-", $this->_displayName)).": $opt;");
			}
			$input->setValue($this->_value);
		} else {
			$input = new WTextField();
			$input->setValue($this->_value);
		}
		return $input;
	}
	
	*/
	
	
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * @access public
	 * @return boolean
	 */
	function validate () {
		
		return $this->_wizardComponent->validate();
		
		//$styleComponent =$this->getStyleComponent();		
		//$rule =$styleComponent->getRule();
		//$err = !$rule->check($styleComponent->getValue());
		//if (!$err) $this->_showError = true;
		//return $err;
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
		
			$styleComponent =$this->getStyleComponent();			
			$val = $this->_wizardComponent->getAllValues();

			
			$rule =$styleComponent->getRule();
			
			
			
			if($rule->check($val)){
				$styleComponent->setValue($val);
			}else{
				print "<br />failed: '".$val."'!";
			}
			
			$styleComponent->setValue($val);
			
		
		
		return true;
	}
	
	/**
	 * Returns the values of wizard-components. Should return an array if children are involved,
	 * otherwise a whatever type of object is expected.
	 * 
	 * In this case, a "1" or a "0" is returned, depending on the checked state of the checkbox.
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
		
		//$errText = sprintf(,$this->getAllValues());
		
		
		
		
		
		
		//$errDescription = "Could not validate the color StyleComponent value \"%s\". Allowed formats are: #RGB, #RRGGBB, rgb(R,G,B), and rgb(R%%,G%%,B%%).";
		
		
		
		/*$errText = $this->getErrorText();
		//$errText = sprintf($errText,"32werwer4");
		$errRule =$this->getErrorRule();
		$errStyle = $this->getErrorStyle();
		
		
		
		//$errText= sprintf("Could not validate the color StyleComponent value \"%s\". Allowed formats are: #RGB, #RRGGBB, rgb(R,G,B), and rgb(R%%,G%%,B%%).",$this->_wizardComponent->getAllValues(),true);
		$val = $this->_wizardComponent->getAllValues();
		$errText= sprintf($errText,$val,$val,$val,$val);
		
		print $errText."<br />";
		
		$this->_showError = false;
		
		if ($errText && $errRule) {
			$s .= "<span id='".$fieldName."_error' style=\"padding-left: 10px; $errStyle\">&laquo; $errText</span>";	
			$s .= Wizard::getValidationJavascript($fieldName, $errRule, $fieldName."_error", $this->_showError);
			$this->_showError = false;
		}*/
		return $s;
	}
	
			
	
}

?>