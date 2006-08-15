<?php
/**
 * @since 8/09/2006
 * @package polyphony.library.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStyleCollection.class.php,v 1.1 2006/08/15 21:12:35 sporktim Exp $
 */ 

require_once(POLYPHONY.'/main/library/Wizard/WizardComponentWithChildren.abstract.php');

/**
 * This class allows for the creation of a Style Collection GUI Wizard component.
 * 
 * @since 8/09/2006
 * @package polyphony.library.guiwizardcomponents
 * 
 * @copyright Copyright &copy; 2006, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WStyleCollection.class.php,v 1.1 2006/08/15 21:12:35 sporktim Exp $
 */
 
class WStyleCollection 
	extends WizardComponentWithChildren
{

	var $_wizardComponent;
	
	var $_getThemeCallBack;
	var $_collection;

	
	
	
	function WStyleCollection ($callBack, $collection) {
		$this->_getThemeCallBack = $callBack;
		
		
		$this->_collection = $collection;
		
		
		$styleCollection =& $this->getStyleCollection();
		
		
		/*
		// the list of existing SCs
		$scs =& $styleProperty->getSCs();
		
		
		foreach (array_keys($scs) as $key) {
			$class = get_class($scs[$key]);	
			$scComp =& new WStyleComponent($this->_callBack, $class ,$this->getName(),$collection,true);
			$wizSP->addComponent("comp".$i, $scComp);
			$i++;	
		}
*/



		// build individula SP markup chunks that can be unset
		$SPs =& $styleCollection->getSPs();
		$i = 0;
		foreach (array_keys($SPs) as $key) {
			$styleProperty =& new WStyleProperty($callBack, $key, $collection);
			$this->addComponent("property".$i, $styleProperty);
			$i++;

		}
		
		
	
	
		
	}
	
	function &getStyleCollection(){
		eval('$theme =& '.$this->_getThemeCallBack."();");		
		return $theme->getStyleCollection($this->_collection);	
	}
	
	
	
	/*
	$wizStyle =& new WizardStep();
		$guiManager =& Services::getService('GUI');
		
		// table in buffer for WHOLE Style Collection
		ob_start();
		print "<table border=3 width='100%'>";

		print "<td>".$this->getDisplayName()."</td>";
		print "<td><table border=2 width='100%'>";

		// build individula SP markup chunks that can be unset
		$SPs =& $this->getSPs();
		$i = 0;
		foreach (array_keys($SPs) as $key) {
			
		
			$spid =& $SPs[$key]->getId();
			$wizStyle->addComponent("property_".$i,
									$SPs[$key]->getWizardRepresentation($callBack,$this->getSelector()));
			
			
									
			// buffer for SP markup
			ob_start();
			print "<tr><td>".$SPs[$key]->getDisplayName()."</td>";
			print "<td>[[property_".$i."]]</td>";

			
			
			
			// create remove button for each SP
			if ($removable) {
				$wizStyle->addComponent('remove-'.$spid->getIdString(),
										WEventButton::withLabel('-'));
				print "<td>[[remove-property_".$i."]]</td>";
			}
			
			print "</tr>";
			$wizStyle->setContent(ob_get_clean(), 
											 $spid->getIdString()); 
			
		}
		
		print "</table></td>";
		
		// insert all the markup chunks that exist
		//foreach ($wizStyle->getMarkups() as $key => $markup) {
		//	print $markup;
		//}
		print $wizStyle->getMarkup($this->getSelector());
		
		
		// create list and button for adding SPs
		if ($removable) {
			$SL =& $wizStyle->addComponent('add-SP', new WSelectList());		
			$wizStyle->addComponent('plus', WEventButton::withLabel('+'));
			$SupSPs = $guiManager->getSupportedSPs();
			$available = array_diff($SupSPs, array_keys($SPs));
			
			foreach ($available as $option) {
				$SL->addOption($option, $option);
			}
			
			ob_start();
			print "<tr><td>"._("Add another Property:")."</td>";
			print "<td>[[add-SP]]</td><td>[[plus]]</td></tr>";
			$wizStyle->setMarkupForComponent(ob_get_clean(), 'add-property');
			
			// why? i don't know!
			print $wizStyle->getMarkupForComponent('add-property');
		}
		
		print "</table>";
		$wizStyle->setContent(ob_get_clean());
		
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
		$children =& $this->getChildren();
		$ok = true;
		foreach (array_keys($children) as $key) {
			
			if(is_null($children[$key])){
				printpre(array_keys($children));
				print $key;
				throwError(new Error("prob",""));	
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
		$children =& $this->getChildren();
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
		$styleCollection =& $this->getStyleCollection();


		$s = "";
		/*
		$s.= "<table border=1>";
		// for each existing SC built request an input for it
		$i = 0;
		
		
		$children =& $this->getChildren();
		
		foreach ($children as $key=>$child) {
			$sc =& $child->getStyleComponent();
						
			// table row [displayName][input][description]
			$s.= "<tr><td>".$sc->getDisplayName().":</td>";
			$s.= "<td>".$child->getMarkup($fieldName."_".$key)."</td>";
			$s.= "<td>".$sc->getDescription()."</td></tr>";	
		}
		
		*/
		$wizStyle =& new WizardStep();
		$guiManager =& Services::getService('GUI');
		
		// table in buffer for WHOLE Style Collection
		
		$s.= "<table border=3 width='100%'><tr>";

		$s.= "<td>".$styleCollection->getDisplayName()."</td>";
		$s.= "<td><table border=2 width='100%'>";

		// build individula SP markup chunks that can be unset
		$children =& $this->getChildren();
		$i = 0;
		foreach ($children as $key=>$child) {
			
		
			
			$styleProperty =& $child->getStyleProperty();
			$s.= "<tr><td>".$styleProperty->getDisplayName()."</td>";
			$s.= "<td>".$child->getMarkup($fieldName."_".$key)."</td>";


			
			$s.= "</tr>";
		
		}
		
		$s.= "</table></td>";
		
		// insert all the markup chunks that exist
		//foreach ($wizStyle->getMarkups() as $key => $markup) {
		//	print $markup;
		//}

		
		$s.= "</tr></table>";
	
		return $s;
	}
}

?>