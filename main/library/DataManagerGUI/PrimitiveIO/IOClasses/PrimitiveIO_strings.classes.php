<?php
/**
 * @package polyphony.library.datamanager_gui
 */

/**
 * 
 * @package polyphony.library.datamanager_gui
 */
class PrimitiveIO_shortstring extends PrimitiveIO {
	function mkFormHTML(&$primitive, $label, $index) {
		$t = "[ update: <input type='checkbox' name='update-$label-$index' value='1'/> ]\n";
		$t .= "<b>".$label."[".$index."]</b>: \n";
		$value = $primitive?htmlentities($primitive->toString(), ENT_QUOTES):"";
		$t .= "<input type='text' name='value-$label-$index' value='".$value."' size=30/>\n";
		return $t;
	}
	function &mkPrimitiveFromFormInput(&$fieldSet, $label, $index) {
		if ($fieldSet->get("update-$label-$index")) {
			$value = $fieldSet->get("value-$label-$index");
			return new String($value);
		}
		return ($null=null);
	}
}

/**
 * 
 * @package polyphony.library.datamanager_gui
 */
class PrimitiveIO_string extends PrimitiveIO_shortstring {
	function mkFormHTML(&$primitive, $label, $index) {
		$t = "[ update: <input type='checkbox' name='update-$label-$index' value='1'/> ]\n";
		$t .= "<b>".$label."[".$index."]</b>: \n";
		$value = $primitive?htmlentities($primitive->toString(), ENT_QUOTES):"";
		$t .= "<br/><textarea rows='3' cols='60' scrolling='virtual' name='value-$label-$index'>".$value."</textarea>\n";
		return $t;
	}
}
