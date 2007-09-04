<?php
/**
* @since 8/15/2006
* @package polyphony.library.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WFontEditor.class.php,v 1.2 2007/09/04 20:28:00 adamfranco Exp $
*/



/**
* This class allows for the modification of a font.
*
* @since 8/09/2006
* @package polyphony.library.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WFontEditor.class.php,v 1.2 2007/09/04 20:28:00 adamfranco Exp $
*/

class WFontEditor
extends WMoreOptions
{

	
	
	var $_callBack;
	var $_collectionName;
	var $_thingsToApplyCollectionTo;

	


	function WFontEditor ($callBack, $collectionName, $thingsToApplyCollectionTo) {
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
		

		//get arrays ready
		$color = GUIComponentUtility::makeColorArrays(12, 5);
		$fonts = GUIComponentUtility::makeFontArray();
		$fontSize = GUIComponentUtility::makeFontSizeArray();


		$collectionSelector1 = "*.".$collectionName;

		//first style collection
		if(!$theme->getStyleCollection($collectionSelector1)){
			$styleCollection1 = new StyleCollection($collectionSelector1, $collectionName,"Font Properties", "Font choice with selector ".$collectionName);
			foreach($thingsToApplyCollectionTo as $arr){
				$theme->addStyleForComponentType($styleCollection1,$arr['type'],$arr['index']);
			}
		}


		//first style collection GUI elements
		$comp = new WGUISelectList($callBack, $collectionSelector1,new ColorSP("#000000"),"ColorSC",$color['options'],$color['styles']);
		$this->addComponent('color',$comp);

		$prop = new FontSP('serif',"12pt","normal","normal","normal");
		$comp = new WGUISelectList($callBack, $collectionSelector1,$prop,"FontFamilySC",$fonts,"font-family");
		$this->addComponent('font',$comp);
		$comp = new WGUISelectList($callBack, $collectionSelector1,$prop,"FontSizeSC",$fontSize,"font-size");
		$this->addComponent('fontSize',$comp);
		$comp = new WGUICheckBox($callBack, $collectionSelector1,$prop,"FontWeightSC","bold","normal");
		$this->addComponent('boldBox',$comp);
		$comp = new WGUICheckBox($callBack, $collectionSelector1,$prop,"FontStyleSC","italic","normal");
		$this->addComponent('italicsBox',$comp);
		$comp = new WGUICheckBox($callBack, $collectionSelector1,$prop,"FontVariantSC","small-caps","normal");
		$this->addComponent('smallcapsBox',$comp);



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

		$s.="\n<table border='1' width='100%'>";
		$s.="\n\t<tr><td>";



		$s.="\n<table border='0'  cellpadding='5' width='100%'>";
		$s.="\n\t<tr>";
		$s.="\t\t<td>Text Color:";
		$comp =$this->getChild($name = 'color');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";

		$s.="\t\t<td>Text Size: ";
		$comp =$this->getChild($name = 'fontSize');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";

		$s.="\t\t<td>More font options: ".$this->getCheckboxMarkup($fieldName)."</td>";




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
		$s.="\t\t<td>Font: ";
		$comp =$this->getChild($name = 'font');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		$comp =$this->getChild($name = 'boldBox');
		$s.="\t\t<td>Bold".$comp->getMarkup($fieldName."_".$name)."</td>";
		$comp =$this->getChild($name = 'italicsBox');
		$s.="\t\t<td>Italics".$comp->getMarkup($fieldName."_".$name)."</td>";
		$comp =$this->getChild($name = 'smallcapsBox');
		$s.="\t\t<td>Smallcaps".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr>";
		$s.="\n</table>";





		return $s;
	}


}

?>