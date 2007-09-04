<?php
/**
* @since 8/15/2006
* @package polyphony.library.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WBackgroundEditor.class.php,v 1.3 2007/09/04 20:28:00 adamfranco Exp $
*/



/**
* This class allows for the modification of the layout of a font.
*
* @since 8/09/2006
* @package polyphony.library.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WBackgroundEditor.class.php,v 1.3 2007/09/04 20:28:00 adamfranco Exp $
*/

class WBackgroundEditor
extends WMoreOptions
{

	
	
	var $_callBack;
	var $_collectionName;
	var $_thingsToApplyCollectionTo;

	
	
	/**
	* It is important to know that the $thingsToApplyCollectionTo is an array
	* of arrays.  Each array should have two elements--an index at 'index', and 
	* a type at 'type.'  types might be MENU, BLOCK, HEADING and so forth.
	*/ 
	function WBackgroundEditor ($callBack, $collectionName, $thingsToApplyCollectionTo) {		

		$this->init();
		
		
		$this->_callBack =$callBack;
		$this->_collectionName =$collectionName;
		$this->_thingsToApplyCollectionTo = $thingsToApplyCollectionTo;
		
		
		$this->rebuildContent();
	}
	
	/**
	 * Make everything
	 */
	function rebuildContent(){
		
		
		
		
		$callBack = $this->_callBack;
		$collectionName = $this->_collectionName;
		$thingsToApplyCollectionTo = $this->_thingsToApplyCollectionTo;
		
		
		//get the theme
		eval('$theme = '.$callBack."();");


		
		if(!is_object($theme)){
			return;	
		}
		
	
		
		$color = GUIComponentUtility::makeColorArrays(12, 5);
		$marginArray = GUIComponentUtility::makeMarginAndPaddingArray();
		$borderSizes = GUIComponentUtility::makeBorderSizeArrays();
		$borderStyles =GUIComponentUtility::makeBorderStyleArrays();

		$collectionSelector1 = "*.".$collectionName;

		//first style collection
		if(!$theme->getStyleCollection($collectionSelector1)){
			$styleCollection1 = new StyleCollection($collectionSelector1, $collectionName,"BackGround properties", "How would you like your background to look?".$collectionName);
			foreach($thingsToApplyCollectionTo as $arr){
				$theme->addStyleForComponentType($styleCollection1,$arr['type'],$arr['index']);
			}
		}


		//first style collection GUI elements
		
		$comp = new WGUISelectList($callBack, $collectionSelector1,new BackgroundColorSP("#FFFFFF"),"ColorSC",$color['options'],$color['styles']);
		$this->addComponent('bgColor',$comp);
		
		
		$prop = new BorderSP('0px',"solid","#000000");
		$comp = new WGUISelectList($callBack, $collectionSelector1,$prop,"LengthSC",$borderSizes['options'],$borderSizes['styles']);
		$this->addComponent('borderSize',$comp);
		$comp = new WGUISelectList($callBack, $collectionSelector1,$prop,"BorderStyleSC",$borderStyles['options'],$borderStyles['styles']);
		$this->addComponent('borderStyle',$comp);
		$comp = new WGUISelectList($callBack, $collectionSelector1,$prop,"ColorSC",$color['options'],$color['styles']);
		$this->addComponent('borderColor',$comp);
		
		
	
		
		$comp = new WGUISelectList($callBack, $collectionSelector1,new MarginTopSP("0px"),"LengthSC",$marginArray,false);
		$this->addComponent('m_top',$comp);
		$comp = new WGUISelectList($callBack, $collectionSelector1,new MarginBottomSP("0px"),"LengthSC",$marginArray,false);
		$this->addComponent('m_bottom',$comp);	
		$comp = new WGUISelectList($callBack, $collectionSelector1,new MarginLeftSP("0px"),"LengthSC",$marginArray,false);
		$this->addComponent('m_left',$comp);	
		$comp = new WGUISelectList($callBack, $collectionSelector1,new MarginRightSP("0px"),"LengthSC",$marginArray,false);
		$this->addComponent('m_right',$comp);		

		

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
		
		
		//make sure we're current with the theme
		$this->rebuildContent();
		
		$s="";

		$s.="\n<table border='1'  width='100%'>";
		$s.="\n\t<tr><td>";



		$s.="\n<table border='0' width='100%' cellpadding='5'>";
		$s.="\n\t<tr>";
		$s.="\t\t<td>Background Color: ";
		$comp =$this->getChild($name = 'bgColor');
		$s.="\n\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		
		
				
		$s.="\t\t<td width>Border Size: ";
		$comp =$this->getChild($name = 'borderSize');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";	
		

		
		$s.="\n\t\t<td>More background options: ".$this->getCheckboxMarkup($fieldName)."</td>";



		


		$s.="\n\t</tr>";
		$s.="\n</table>";

		$s.="\n\t</td></tr><tr><td>";

		$s.= $this->getOptionalComponentsMarkup($fieldName);

		$s.="\n\t</td></tr></table>";



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
		$s.="\n\t<tr>";
	
		
		$s.="\t\t<td>Border Color: ";
		$comp =$this->getChild($name = 'borderColor');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		
		$s.="\t\t<td width>Border Style: ";
		$comp =$this->getChild($name = 'borderStyle');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr><tr>";
		

		
		$s.="\t\t<td>Top Margin: ";
		$comp =$this->getChild($name = 'm_top');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";		
		$s.="\t\t<td>Bottom Margin: ";
		$comp =$this->getChild($name = 'm_bottom');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr><tr>";
		
		
		$s.="\t\t<td>Left Margin: ";
		$comp =$this->getChild($name = 'm_left');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";		
		$s.="\t\t<td>Right Margin: ";
		$comp =$this->getChild($name = 'm_right');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr><tr>";
		
		$s.="\n\t</tr>";
		$s.="\n</table>";





		return $s;
	}


}

?>