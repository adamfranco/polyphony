<?php
/**
* @since 8/15/2006
* @package polyphony.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WMultiCollection.class.php,v 1.3 2007/09/19 14:04:45 adamfranco Exp $
*/



/**
* This class allows for the modification of the layout of a font.
*
* @since 8/09/2006
* @package polyphony.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WMultiCollection.class.php,v 1.3 2007/09/19 14:04:45 adamfranco Exp $
*/

class WMultiCollection
extends WMoreOptions
{



	var $_callBack;
	var $_uniqueName;
	var $_thingsToApplyCollectionsTo;


	var $_shown;
	var $_descriptions;


	/**
	* It is important to know that the $thingsToApplyCollectionTo is an array
	* of arrays.  Each array should have two elements--an index at 'index', and
	* a type at 'type.'  types might be MENU, BLOCK, HEADING and so forth.
	*
	* The last three are booleans.  False means they are hidden under "More Options."
	*/
	function WMultiCollection ($callBack, $uniqueName, $thingsToApplyCollectionsTo,$font,$bg,$text) {

		$this->init();




		$comp = new WFontEditor($callBack, $uniqueName."_font", $thingsToApplyCollectionsTo);
		$this->addComponent("font",$comp);
		$comp = new WTextLayoutEditor($callBack, $uniqueName."_font", $thingsToApplyCollectionsTo);
		$this->addComponent("text",$comp);
		$comp = new WBackgroundEditor($callBack, $uniqueName."_font", $thingsToApplyCollectionsTo);
		$this->addComponent("bg",$comp);


		
		
	
		if($font){
			$arr[] = "font";		
		}
		if($bg){
			$arr[] = "bg";
		}
		if($text){
			$arr[] = "text";
		}
		$this->_shown = $arr;
		


		$this->_descriptions =array("font"=>"Font Options:","text"=>"Text Options:","bg"=>"Background Options");


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
		
	

		$s.="\n<table border='0'  width='100%'>";
		$s.="\n\t<tr><td>";



		$s.="\n<table border='0' width='100%' cellpadding='5'>";

		//do we actually need the MoreOptions functions?
		if(count($this->_descriptions) > count($this->_shown)){
			$needsMore = true;
		}else{
			$needsMore = false;
		}


		foreach(array_keys($this->_descriptions) as $name){
			if(!in_array($name,$this->_shown))
			continue;

			$s.="\n\t<tr>";
			$s.="\t\t<td>".$this->_descriptions[$name]."</td>";
			$comp =$this->getChild($name);
			$s.="\n\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
			$s.="\n\t</tr>";
		}


		if($needsMore){
			$s.="\n\t\t<tr><td>&nbsp;</td><td>More Options: ".$this->getCheckboxMarkup($fieldName)."</td></tr>";
		}


		$s.="\n</table>";

		$s.="\n\t</td></tr>";
		
		
		
		if($needsMore){
			$s.= "<tr><td align='right'>";
			
			
		
			
			$s.= $this->getOptionalComponentsMarkup($fieldName);
			
			
			$s.="\n\t</td></tr>";
		}
		
		$s.= "</table>";



		return $s;
	}


	/**
	* Returns a block of XHTML-valid code that contains markup for the "advanced"
	* options.
	* @param string $fieldName The field name to use when outputting form data or
	* similar parameters/information.
	* @access public
	* @return string
	*/
	function advancedMarkup ($fieldName) {
		
		
		
		
		
		$s="";


		$s.="\n<table border='0' width='100%' cellpadding='5'>";


		foreach(array_keys($this->_descriptions) as $name){
			if(in_array($name,$this->_shown))
			continue;

			$s.="\n\t<tr>";
			$s.="\t\t<td>".$this->_descriptions[$name]."</td>";
			$comp =$this->getChild($name);
			$s.="\n\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
			$s.="\n\t</tr>";
		}

		
		$s.="\n</table>";


		return $s;
	}


}

?>