<?php
/**
* @since 8/15/2006
* @package polyphony.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: GUIComponentUtility.class.php,v 1.5 2007/09/19 14:04:46 adamfranco Exp $
*/



/**
* This class allows for the modification of an entire "level" of theme.
*
* @since 8/09/2006
* @package polyphony.guiwizardcomponents
*
* @copyright Copyright &copy; 2006, Middlebury College
* @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
*
* @version $Id: GUIComponentUtility.class.php,v 1.5 2007/09/19 14:04:46 adamfranco Exp $
*/


class GUIComponentUtility{
	
	
	
/**
	 *
	 * Make an array of colors complete with styles.  The colors are arranged by hue families.
	 *
	 * @param int slices The number of families.  6-15 is probably good.
	 * @param int trisize Proportional with the square root of the size of each family 4 to 6 is probably good.
	 * @return array an array with the values at 'options' and styles in 'styles'
	 */
	
	
	
	function makeColorArrays($slices, $triSize){
		$options = array();
		$styles = array();
		$options[''] = "Default";
		$styles[''] = "";


		for($dark = 0; $dark <=$triSize; $dark++){
			$goalDark = 255.99 * $dark / $triSize;
			$arr = GUIComponentUtility::addColor($goalDark,$goalDark,$goalDark);
			
		
			
								$val0 = $arr[0];
					$val1 = $arr[1];
					
					
					$options[$val0]=$val0;
					$styles[$val0]=$val1;
		}


		for($rot = 0; $rot<$slices; $rot++){
			$angle_R = ($rot /$slices) * 2 * M_PI+M_PI*1/3;
			$angle_G = ($rot /$slices) * 2 * M_PI+M_PI*5/3;
			$angle_B = ($rot /$slices) * 2 * M_PI+M_PI*3/3;

			$baseR = 128+ 127.99*sin($angle_R);
			$baseG = 128+ 127.99*sin($angle_G);
			$baseB = 128+ 127.99*sin($angle_B);

			for($dark = $triSize; $dark > 0; $dark--){
				$baseDarknessR = $baseR * $dark / $triSize;
				$baseDarknessG = $baseG * $dark / $triSize;
				$baseDarknessB = $baseB * $dark / $triSize;


				$goalDark = 255.99 * $dark / $triSize;

				for($gray = 0; $gray < $dark; $gray++){

					$actual_r = $baseDarknessR * ($dark -$gray) + $goalDark*$gray;
					$actual_g = $baseDarknessG * ($dark -$gray) + $goalDark*$gray;
					$actual_b = $baseDarknessB * ($dark -$gray) + $goalDark*$gray;

					
					$actual_r /= $dark;
					$actual_g /= $dark;
					$actual_b /= $dark;
					
					$arr = GUIComponentUtility::addColor($actual_r,$actual_g,$actual_b);
					
					$val0 = $arr[0];
					$val1 = $arr[1];
					
					
					$options[$val0] = $val0;
					$styles[$val0] = $val1;
					
				}
			}
		}


		//this hack takes care of the fact that I really want to return them both.
		$ret = array();
		$ret['options'] = $options;
		$ret['styles'] = $styles;

		return $ret;
	}

	function addColor($r, $g, $b){
		
		
		$hexR = strtoupper(dechex(intval($r)));
		if($r <16) $hexR = "0".$hexR;
		$hexG = strtoupper(dechex(intval($g)));
		if($g <16) $hexG = "0".$hexG;
		$hexB = strtoupper(dechex(intval($b)));
		if($b <16) $hexB = "0".$hexB;
		
		$val = '#'.$hexR.$hexG.$hexB;
		

		//num measures the "brightness" of the color.
		//I think green is "brighter" than red and red "brighter" than blue.
		$num = $r*2+$g*3+$b;
		//Our threshold is 750.
		if($num > 750){
			$col = "#000000";
		}else{
			$col = "#FFFFFF";
		}
		return array($val,"font-family: monospace; color: ".$col."; background-color:".$val.";");
	}

	function makeFontArray(){
		$options = array("serif","sans-serif","cursive","fantasy","monospace");
		foreach($options as $option){
			$ret[$option]=$option;
		}
		return $ret;
	}

	function makeFontSizeArray(){
		$options = array("8pt","10pt","12pt","14pt","16pt","18pt","20pt","22pt","24pt","26pt");
		foreach($options as $option){
			$ret[$option]=$option;
		}
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
	
	function makeMarginAndPaddingArray(){
		$arr = array("0px","1px","2px","3px","4px","5px","6px","8px","10px","12px","16px","20px","25px","30px","35px","40px","45px","50px","60px","70px","80px","100px","125px","150px","175px","200px","225px","250px","275px","300px","350px","400px","-0px","-1px","-2px","-3px","-4px","-5px","-6px","-8px","-10px","-12px","-16px","-20px","-25px","-30px","-35px","-40px","-45px","-50px","-60px","-70px","-80px","-100px","-125px","-150px","-175px","-200px","-225px","-250px","-275px","-300px","-350px","-400px","2%","5%","10%","15%","20%","25%","30%","35%","40%","50%","60%","75%","-2%","-5%","-10%","-15%","-20%","-25%","-30%","-35%","-40%","-50%","-60%","-75%","-100%","-125%","-150%","-200%","-250%","-300%","-400%");
		foreach($arr as $option){
			$options[$option]=$option;
		}
		return $options;
	}
	
	function makeBorderStyleArrays(){
		$arr = array("none", "dotted", "dashed", 
					     "solid", "groove", "ridge", 
						 "inset", "outset", "double");
		foreach($arr as $option){
			$options[$option]=$option;
			$styles[$option]="margin: 6px 3px; padding: 3px; border-style: ".$option.";";
		}
		 $ret = array('options'=>$options,'styles'=>$styles);
		 return $ret;
	}
	
	function makeSpacingArray(){
		$arr = array("normal","-5px", "-3px", "-2px","-1px", "0px","1px", "2px","3px","5px", "7px", "10px");   		 
		foreach($arr as $option){
			$options[$option]=$option;
		}
		 return $options;
	}
	
	function makeLineSpacingArray(){
		$options = array("normal","50%","75%","90%","100%","125%","150%","175%","200%","250%");
		foreach($options as $option){
			$ret[$option]=$option;
		}
		return $ret;
	}
	
	function makeAlignArray(){
		$options = array("left","center","right","justified");
		foreach($options as $option){
			$ret[$option]=$option;
		}
		return $ret;
	}
	
	
	
}