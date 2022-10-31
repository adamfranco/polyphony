<?php
/**
* @since 8/15/2006
* @package polyphony.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: WTextLayoutEditor.class.php,v 1.4 2007/09/19 14:04:45 adamfranco Exp $
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
* @version $Id: WTextLayoutEditor.class.php,v 1.4 2007/09/19 14:04:45 adamfranco Exp $
*/

class WTextLayoutEditor
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
	function __construct ($callBack, $collectionName, $thingsToApplyCollectionTo) {		

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
		
	
		
		$align = GUIComponentUtility::makeAlignArray();
		$textSpacing = GUIComponentUtility::makeSpacingArray();
		$lineSpacing = GUIComponentUtility::makeLineSpacingArray();
		$marginArray = GUIComponentUtility::makeMarginAndPaddingArray();

		$collectionSelector1 = "*.".$collectionName;

		//first style collection
		if(!$theme->getStyleCollection($collectionSelector1)){
			$styleCollection1 = new StyleCollection($collectionSelector1, $collectionName,"Text Properties", "How would you like your text to look?".$collectionName);
			foreach($thingsToApplyCollectionTo as $arr){
				$theme->addStyleForComponentType($styleCollection1,$arr['type'],$arr['index']);
			}
		}


		//first style collection GUI elements
		$comp = new WGUISelectList($callBack, $collectionSelector1,new TextAlignSP("left"),"TextAlignSC",$align,"text-align");
		$this->addComponent('align',$comp);
		
		$comp = new WGUISelectList($callBack, $collectionSelector1,new LetterSpacingSP("normal"),"TextSpacingSC",$textSpacing,"letter-spacing");
		$this->addComponent('letterSpace',$comp);
		
		$comp = new WGUISelectList($callBack, $collectionSelector1,new WordSpacingSP("normal"),"TextSpacingSC",$textSpacing,false);
		$this->addComponent('wordSpace',$comp);	
		
		$comp = new WGUISelectList($callBack, $collectionSelector1,new LineHeightSP("normal"),"LineHeightSC",$lineSpacing,false);
		$this->addComponent('lineSpace',$comp);	

				$comp = new WGUISelectList($callBack, $collectionSelector1,new PaddingTopSP("0px"),"LengthSC",$marginArray,false);
		$this->addComponent('p_top',$comp);
		$comp = new WGUISelectList($callBack, $collectionSelector1,new PaddingBottomSP("0px"),"LengthSC",$marginArray,false);
		$this->addComponent('p_bottom',$comp);	
		$comp = new WGUISelectList($callBack, $collectionSelector1,new PaddingLeftSP("0px"),"LengthSC",$marginArray,false);
		$this->addComponent('p_left',$comp);	
		$comp = new WGUISelectList($callBack, $collectionSelector1,new PaddingRightSP("0px"),"LengthSC",$marginArray,false);
		$this->addComponent('p_right',$comp);	

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



		$s.="\n<table border='0' width='100%' cellpadding='5'>";
		$s.="\n\t<tr>";
		$s.="\t\t<td>Text Align: ";
		$comp =$this->getChild($name = 'align');
		$s.="\n\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		
		
		$s.="\t\t<td>Line Spacing: ";
		$comp =$this->getChild($name = 'lineSpace');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		
		$s.="\n\t\t<td>More Text layout options: ".$this->getCheckboxMarkup($fieldName)."</td>";



		


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
		
		$s.="\t\t<td width>Space between letters: ";
		$comp =$this->getChild($name = 'letterSpace');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";		
		$s.="\t\t<td width>Space between words: ";
		$comp =$this->getChild($name = 'wordSpace');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr><tr>";
		
		$s.="\t\t<td>Top Padding: ";
		$comp =$this->getChild($name = 'p_top');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";		
		$s.="\t\t<td>Bottom Padding: ";
		$comp =$this->getChild($name = 'p_bottom');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr><tr>";
		
		
		$s.="\t\t<td>Left Padding: ";
		$comp =$this->getChild($name = 'p_left');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";		
		$s.="\t\t<td>Right Padding: ";
		$comp =$this->getChild($name = 'p_right');
		$s.="\t\t".$comp->getMarkup($fieldName."_".$name)."</td>";
		$s.="\n\t</tr><tr>";
		
		$s.="\n\t</tr>";
		$s.="\n</table>";





		return $s;
	}


}

?>