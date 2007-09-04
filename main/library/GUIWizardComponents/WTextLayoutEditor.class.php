<?php
/**
* @since 8/15/2006
* @package polyphony.library.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WTextLayoutEditor.class.php,v 1.2 2007/09/04 20:27:59 adamfranco Exp $
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
* @version $Id: WTextLayoutEditor.class.php,v 1.2 2007/09/04 20:27:59 adamfranco Exp $
*/

class WTextLayoutEditor
extends WMoreOptions
{


	function WFontEditor ($callBack, $collectionName, $type, $index) {		

		//get the theme
		eval('$theme = '.$callBack."();");


		
		
		$fonts = $this->makeFontArray();
		$fontSize =$this->makeFontSizeArray();


		$collectionSelector1 = "*.".$collectionName;

		//first style collection
		if(!$theme->getStyleCollection($collectionSelector1)){
			$styleCollection1 = new StyleCollection($collectionSelector1, $collectionName,"Font Properties", "Font choice with selector ".$collectionName);
			$theme->addStyleForComponentType($styleCollection1,$type,$index);
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
	 *
	 * Make an array of colors complete with styles.  The colors are arranged by hue families.
	 *
	 * @param int slices The number of families.  6-15 is probably good.
	 * @param int trisize The size of each family.
	 * @return array an array with the values at 'options' and styles in 'styles'
	 */
	
	
	function makeBorderSizeArrays(){
		$arr = array("0px","1px","2px","3px","4px","5px","6px","8px","10px","14px");
		foreach($arr as $option){
			$options[$option]=$option;
			$styles[$option]="border-width: ".$option."; margin 3; padding 3;";
		}
		$ret = array('options'=>$options,'styles'=>$styles);
		return $ret;
	}
	
	function makeBorderSizeArrays(){
		$arr = array("0px","1px","2px","3px","4px","5px","6px","8px","10px","14px");
		foreach($arr as $option){
			$options[$option]=$option;
			$styles[$option]="border-width: ".$option."; margin 3; padding 3;";
		}
		$ret = array('options'=>$options,'styles'=>$styles);
		return $ret;
	}
	
	function makeBorderSizeArrays(){
		$arr = array("0px","1px","2px","3px","4px","5px","6px","8px","10px","14px");
		foreach($arr as $option){
			$options[$option]=$option;
			$styles[$option]="border-width: ".$option."; margin 3; padding 3;";
		}
		$ret = array('options'=>$options,'styles'=>$styles);
		return $ret;
	}
	
	function makeBorderSizeArrays(){
		$arr = array("0px","1px","2px","3px","4px","5px","6px","8px","10px","14px");
		foreach($arr as $option){
			$options[$option]=$option;
			$styles[$option]="border-width: ".$option."; margin 3; padding 3;";
		}
		$ret = array('options'=>$options,'styles'=>$styles);
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

		$s.="\n<table border='0'>";
		$s.="\n\t<tr><td>";



		$s.="\n<table border='1' width='100%'>";
		$s.="\n\t<tr>";
		$s.="\t\t<td>Text Color:</td>";
		$comp =$this->getChild($name = 'color');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";

		$s.="\t\t<td>Text Size:</td>";
		$comp =$this->getChild($name = 'fontSize');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";

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

		$s.="\n<table border='1' width='100%'>";
		$s.="\n\t<tr>";
		$s.="\t\t<td>Font:</td>";
		$comp =$this->getChild($name = 'font');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
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