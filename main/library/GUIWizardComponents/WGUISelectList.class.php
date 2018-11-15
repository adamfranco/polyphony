<?php
/**
 * @since 8/14/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WGUISelectList.class.php,v 1.3 2007/09/19 14:04:45 adamfranco Exp $
 */ 



/**
 * This class make a selectlist associated with a style property
 * 
 * @since 8/14/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WGUISelectList.class.php,v 1.3 2007/09/19 14:04:45 adamfranco Exp $
 */
 
class WGUISelectList
	extends WGUIComponent 
{

	
	/**
	* Creates the component.
	*
	* styles can be an array indexed just as options is with styles as strings, can be set to a string to describe
	* the appropriate style property to automatically generate styles, or set to false to just leave them off.
	*/
	function __construct($callBack, $collectionSelector, $styleProperty, $componentClass, $options, $styles = false){
		
				
		$input = new WSelectList();		
		foreach ($options as $opt=>$name) {
			if($styles){
				if(is_array($styles)){
					$input->addOption($opt, $name, $styles[$opt]);
				}else{
					$input->addOption($opt, $name, $styles.": ".$opt.";");
				}
			}else{
				$input->addOption($opt, $name);
			}
		}		
		$this->_wizardComponent =$input;
			
			
		$this->init($callBack, $collectionSelector, $styleProperty, $componentClass);
	}
	
	/**
	 * copy the value of the Wizard component to the StyleComponent
	 */
	function exportValue(){
		$styleComponent =$this->getStyleComponent();
		$val = $this->_wizardComponent->getAllValues();
		$styleComponent->setValue($val);		
	}
	
	
	
	/**
	 * Is $value a legitamate value for this wizard component?
	 *
	 * @param string $value the value to test
	 * @return boolean if the value is legitimate 
	 */
	function isPossibleValue($value){
		return $this->_wizardComponent->isOption($value);
	}
	
	
	
}

?>