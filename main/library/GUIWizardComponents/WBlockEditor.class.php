<?php
/**
* @since 8/12/2006
* @package polyphony.library.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WBlockEditor.class.php,v 1.1 2006/08/15 21:12:35 sporktim Exp $
*/



/**
* This class allows for the modification of an entire "level" of theme.
*
* @since 8/09/2006
* @package polyphony.library.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WBlockEditor.class.php,v 1.1 2006/08/15 21:12:35 sporktim Exp $
*/

class WBlockEditor
extends WizardComponentWithChildren
{


	var $_getThemeCallBack;
	var $_index;

	function WBlockEditor ($callBack, $index) {
		$this->_getThemeCallBack = $callBack;
		$this->_index = $index;




		//get the theme
		eval('$theme =& '.$this->_getThemeCallBack."();");


		//get arrays ready
		$color = GUIComponentUtility::makeColorArrays(12,5);		
		$fonts = GUIComponentUtility::makeFontArray();
		$fontSize = GUIComponentUtility::makeFontSizeArray();
		$borderSizes = GUIComponentUtility::makeBorderSizeArrays();
		$borderStyles =GUIComponentUtility::makeBorderStyleArrays();


		//first style collection
		$collectionSelector1 = '*.block'.$this->_index;

		if(!$theme->getStyleCollection($collectionSelector1)){
			$styleCollection1 =& new StyleCollection($collectionSelector1, 'block'.$this->_index,"Block ".$this->_index, "Block styles for Block ".$this->_index);
			$theme->addStyleForComponentType($styleCollection1,BLOCK,$this->_index);
		}

		
		//first style collection GUI elements
		
		$comp =& new WGUISelectList($callBack, $collectionSelector1,new BackgroundColorSP("#FFFFFF"),"ColorSC",$color['options'],$color['styles']);
		$this->addComponent('bgColor',$comp);
		$comp =& new WGUISelectList($callBack, $collectionSelector1,new ColorSP("#000000"),"ColorSC",$color['options'],$color['styles']);
		$this->addComponent('color',$comp);
		
		$prop =& new FontSP('serif',"12pt","normal","normal","normal");
		$comp =& new WGUISelectList($callBack, $collectionSelector1,$prop,"FontFamilySC",$fonts,"font-family");
		$this->addComponent('font',$comp);
		$comp =& new WGUISelectList($callBack, $collectionSelector1,$prop,"FontSizeSC",$fontSize,"font-size");
		$this->addComponent('fontSize',$comp);
		$comp =& new WGUICheckBox($callBack, $collectionSelector1,$prop,"FontWeightSC","bold","normal");
		$this->addComponent('boldBox',$comp);
		$comp =& new WGUICheckBox($callBack, $collectionSelector1,$prop,"FontStyleSC","italic","normal");
		$this->addComponent('italicsBox',$comp);
		$comp =& new WGUICheckBox($callBack, $collectionSelector1,$prop,"FontVariantSC","small-caps","normal");
		$this->addComponent('smallcapsBox',$comp);
		
		$prop =& new BorderSP('2px',"solid","#000000");
		$comp =& new WGUISelectList($callBack, $collectionSelector1,$prop,"LengthSC",$borderSizes['options'],$borderSizes['styles']);
		$this->addComponent('borderSize',$comp);
		$comp =& new WGUISelectList($callBack, $collectionSelector1,$prop,"BorderStyleSC",$borderStyles['options'],$borderStyles['styles']);
		$this->addComponent('borderStyle',$comp);
		$comp =& new WGUISelectList($callBack, $collectionSelector1,$prop,"ColorSC",$color['options'],$color['styles']);
		$this->addComponent('borderColor',$comp);


		
		//second style collection
		$collectionSelector2 = '*.head'.$this->_index;

		
		
		
		
		
		if(!$theme->getStyleCollection($collectionSelector2)){
			$styleCollection2 =& new StyleCollection($collectionSelector2, 'head'.$this->_index,"Head ".$this->_index, "Head styles for Head ".$this->_index);
			$theme->addStyleForComponentType($styleCollection2,HEADER,$this->_index);
			$theme->addStyleForComponentType($styleCollection2,FOOTER,$this->_index);
			$theme->addStyleForComponentType($styleCollection2,HEADING,$this->_index);
		}

		
		//second style collection GUI elements
		$comp =& new WGUISelectList($callBack, $collectionSelector2,new BackgroundColorSP("#FFFFFF"),"ColorSC",$color['options'],$color['styles']);		
		$this->addComponent('bgColor2',$comp);
		$comp =& new WGUISelectList($callBack, $collectionSelector2,new ColorSP("#000000"),"ColorSC",$color['options'],$color['styles']);
		$this->addComponent('color2',$comp);
		$comp =& new WGUISelectList($callBack, $collectionSelector2,new FontFamilySP("serif"),"FontFamilySC",$fonts,"font-family");
		$this->addComponent('font2',$comp);
		$comp =& new WGUISelectList($callBack, $collectionSelector2,new FontSizeSP("14pt"),"FontSizeSC",$fontSize,"font-size");
		$this->addComponent('fontSize2',$comp);
		
	
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


		$children =& $this->getChildren();
		$ok = true;
		foreach (array_keys($children) as $key) {
			if (!$children[$key]->update($fieldName."_".$key)) {
				$ok = false;
			}
		}


		
		
		
		
		
		
		
		
		eval('$theme =& '.$this->_getThemeCallBack."();");

		//$styleCollection1 =& new StyleCollection('*.blocky'.$this->_index,'blocky'.$this->_index,"Blocky ".$this->_index, "Blocky styles for Block ".$this->_index);


		//$styleCollection1->addSP(new BackgroundColorSP("#FF0000"));

		
		
		

		
		
		
	
		/*

		eval('$theme =& '.$this->_getThemeCallBack."();");

		$styleCollection1 =& new StyleCollection('*.block'.$this->_index,'block'.$this->_index,"Block ".$this->_index, "Block styles for Block ".$this->_index);

		$comp =& $this->getChild('bgColor');
		if($val = $comp->getAllValues())
		$styleCollection1->addSP(new BackgroundColorSP($val));

		$comp =& $this->getChild('color');
		if($val = $comp->getAllValues())
		$styleCollection1->addSP(new ColorSP($val));

		$comp =& $this->getChild('font');
		if($val = $comp->getAllValues())
		$styleCollection1->addSP(new FontFamilySP($val));

		$comp =& $this->getChild('fontSize');
		if($val = $comp->getAllValues())
		$styleCollection1->addSP(new FontSizeSP($val));


		$collections =& $theme->getStylesForComponentType(BLOCK, $this->_index);
		foreach($collections as $collection){
		$theme->removeStyleCollection($collection);
		}
		$theme->addStyleForComponentType($styleCollection1,BLOCK,$this->_index);

		$styleCollection2 =& new StyleCollection('*.head'.$this->_index,'head'.$this->_index,"Head ".$this->_index, "Heading, Header, and footer styles for Block ".$this->_index);

		$comp =& $this->getChild('bgColor2');
		if($val = $comp->getAllValues())
		$styleCollection2->addSP(new BackgroundColorSP($val));

		$comp =& $this->getChild('color2');
		if($val = $comp->getAllValues())
		$styleCollection2->addSP(new ColorSP($val));

		$comp =& $this->getChild('font2');
		if($val = $comp->getAllValues())
		$styleCollection2->addSP(new FontFamilySP($val));

		$comp =& $this->getChild('fontSize2');
		if($val = $comp->getAllValues())
		$styleCollection2->addSP(new FontSizeSP($val));

		$arr = array(HEADING, HEADER, FOOTER);
		foreach($arr as $type){
		$collections =& $theme->getStylesForComponentType($type, $this->_index);
		foreach($collections as $collection){
		$theme->removeStyleCollection($collection);
		}
		$theme->addStyleForComponentType($styleCollection2,$type,$this->_index);
		}

		//printpre($styleCollection1);
		//printpre($styleCollection2);
		*/



		return $ok;

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

		
		$s.="Heading Styles:";
		$s.="\n<table border='1'>";
		$s.="\n\t<tr>";
		$s.="\t\t<td>Background Color:</td>";
		$comp =& $this->getChild($name = 'bgColor2');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\t\t<td>Text Color:</td>";
		$comp =& $this->getChild($name = 'color2');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr><tr>";
		$s.="\t\t<td>Text Size:</td>";
		$comp =& $this->getChild($name = 'fontSize2');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\t\t<td>Font:</td>";
		$comp =& $this->getChild($name = 'font2');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr>";
		$s.="\n</table>";
		

		$s.="Body Styles:";
		$s.="\n<table border='1'>";
		$s.="\n\t<tr>";
		$s.="\t\t<td>Background Color:</td>";
		$comp =& $this->getChild($name = 'bgColor');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\t\t<td>Text Color:</td>";
		$comp =& $this->getChild($name = 'color');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr><tr>";
		$s.="\t\t<td>Text Size:</td>";
		$comp =& $this->getChild($name = 'fontSize');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\t\t<td>Font:</td>";
		$comp =& $this->getChild($name = 'font');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$comp =& $this->getChild($name = 'boldBox');
		$s.="\t\t<td>Bold".$comp->getMarkup($fieldName."_".$name)."</td>";
		$comp =& $this->getChild($name = 'italicsBox');
		$s.="\t\t<td>Italics".$comp->getMarkup($fieldName."_".$name)."</td>";
		$comp =& $this->getChild($name = 'smallcapsBox');
		$s.="\t\t<td>Smallcaps".$comp->getMarkup($fieldName."_".$name)."</td>";
		
		$s.="\n\t</tr><tr>";
		$s.="\t\t<td>Border Size:</td>";
		$comp =& $this->getChild($name = 'borderSize');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\t\t<td>Border Style:</td>";
		$comp =& $this->getChild($name = 'borderStyle');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\t\t<td>Border Color:</td>";
		$comp =& $this->getChild($name = 'borderColor');
		$s.="\t\t<td>".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr>";
		$s.="\n</table>";



		return $s;
	}



}

?>