<?php
/**
 * @since 8/09/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStyleProperty.class.php,v 1.3 2007/09/19 14:04:46 adamfranco Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponentWithChildren.abstract.php');

/**
 * This class allows for the creation of a StyleProperty GUI Wizard Component.
 * 
 * @since 8/09/2006
 * @package polyphony.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStyleProperty.class.php,v 1.3 2007/09/19 14:04:46 adamfranco Exp $
 */
 
class WStyleProperty 
	extends WizardComponentWithChildren
{

	var $_wizardComponent;
	
	var $_getThemeCallBack;
	var $_collection;
	var $_property;
	
	
	
	
	function WStyleProperty ($callBack, $property, $collection) {
		$this->_getThemeCallBack = $callBack;
		
		
		$this->_collection = $collection;
		$this->_property = $property;
		
		$styleProperty =$this->getStyleProperty();
	
		
		
		// the list of existing SCs
		$scs =$styleProperty->getSCs();
		
		$i =0;
		foreach (array_keys($scs) as $key) {
			$class = get_class($scs[$key]);	
			$scComp = new WStyleComponent($this->_getThemeCallBack, $class ,$styleProperty->getName(),$collection,true);
			$this->addComponent("comp".$i, $scComp);
			$i++;	
		}
		/*
		$empties = array_diff($scList, array_keys($scs));
		// for each SC not populated create their options too
		foreach ($empties as $empty) {
			$emptySC = new $empty();
			$emptyComp = new WStyleComponent($emptySC,true);
			$wizSP->addComponent("comp".$i, $emptyComp);
			// table row [displayName][input][description]
			$s.= "<tr><td>".$emptySC->getDisplayName().":</td>";
			$s.= "<td>[["."comp".$i."]]</td>";
			$s.= "<td>".$emptySC->getDescription()."</td></tr>";
			$i++;
		}
	*/
	
		
	}
	
	function getStyleProperty(){

		eval('$theme = '.$this->_getThemeCallBack."();");		
		$collection =$theme->getStyleCollection($this->_collection);
		return $collection->getStyleProperty($this->_property);

		
		
	}
	
	
	
	/*
	$wizSP = new WizardStep();
		// the list of existing SCs
		$scs =$this->getSCs();
		// the list of SC types for this SP
		$scList = $this->getSCList();
		ob_start();
		print "<table border=1>";
		// for each existing SC built request an input for it
		$i = 0;
		
		

		
		foreach (array_keys($scs) as $key) {
			$class = get_class($scs[$key]);
			
			//printpre($scs);
			
			//print "   $key>-->'".$class."' ";
			
			$scComp = new WStyleComponent($callBack, $class ,$this->getName(),$collection,true);
			$wizSP->addComponent("comp".$i, $scComp);
			// table row [displayName][input][description]
			print "<tr><td>".$scs[$key]->getDisplayName().":</td>";
			print "<td>[["."comp".$i."]]</td>";
			print "<td>".$scs[$key]->getDescription()."</td></tr>";
			$i++;	
		}
		
		
		
		$empties = array_diff($scList, array_keys($scs));
		// for each SC not populated create their options too
		foreach ($empties as $empty) {
			$emptySC = new $empty();
			$emptyComp = new WStyleComponent($emptySC,true);
			$wizSP->addComponent("comp".$i, $emptyComp);
			// table row [displayName][input][description]
			print "<tr><td>".$emptySC->getDisplayName().":</td>";
			print "<td>[["."comp".$i."]]</td>";
			print "<td>".$emptySC->getDescription()."</td></tr>";
			$i++;
		}
		print "</table>";
		$wizSP->setContent(ob_get_clean());
		return $wizSP;
	
	*/
	

	
	
	

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
		$children =$this->getChildren();
		$ok = true;
		foreach (array_keys($children) as $key) {
			
			if(is_null($children[$key])){
				printpre(array_keys($children));
				print $key;
				throwError(new HarmoniError("prob",""));	
			}
			
			if (!$children[$key]->update($fieldName."_".$key)) {
				$ok = false;
			}
		}
		
		return $ok;
	}
	
	
	/**
	 * Returns true if this component (and all child components if applicable) have valid values.
	 * By default, this will just return TRUE.
	 * 
	 * @access public
	 * @return boolean
	 */
	function validate () {
		$children =$this->getChildren();
		foreach (array_keys($children) as $step) {
			if (!$children[$step]->validate()) {
				return false;
			}
		}
		return true;
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
		$styleProperty =$this->getStyleProperty();
		// the list of existing SCs
		$scs =$styleProperty->getSCs();
		// the list of SC types for this SP
		$scList = $styleProperty->getSCList();

		$s = "";
		
		$s.= "<table border=1>";
		// for each existing SC built request an input for it
		$i = 0;
		
		
		$children =$this->getChildren();
		
		foreach ($children as $key=>$child) {
			$sc =$child->getStyleComponent();
						
			// table row [displayName][input][description]
			$s.= "<tr><td>".$sc->getDisplayName().":</td>";
			$s.= "<td>".$child->getMarkup($fieldName."_".$key)."</td>";
			$s.= "<td>".$sc->getDescription()."</td></tr>";	
		}
		
		
		/*
		$empties = array_diff($scList, array_keys($scs));
		// for each SC not populated create their options too
		foreach ($empties as $empty) {
			$emptySC = new $empty();
			$emptyComp = new WStyleComponent($emptySC,true);
			$wizSP->addComponent("comp".$i, $emptyComp);
			// table row [displayName][input][description]
			$s.= "<tr><td>".$emptySC->getDisplayName().":</td>";
			$s.= "<td>[["."comp".$i."]]</td>";
			$s.= "<td>".$emptySC->getDescription()."</td></tr>";
			$i++;
		}*/
		$s.= "</table>";
	
		return $s;
	}
}

?>