<?

require_once(POLYPHONY."/main/library/DataManagerGUI/PrimitiveIO/IOClasses/PrimitiveIO_strings.classes.php");

class PrimitiveIO_integer extends PrimitiveIO_shortstring {
	function &mkPrimitiveFromFormInput(&$fieldSet, $label, $index) {
		if ($fieldSet->get("update-$label-$index")) {
			$value = $fieldSet->get("value-$label-$index");
			return new Integer($value);
		}
		return ($null=null);
	}
        function mkFormHTML(&$primitive, $label, $index) {
		$t = "[ update: <input type='checkbox' name='update-$label-$index' value='1'/> ]\n";
		$t .= "<b>".$label."[".$index."]</b>: \n";
		$value = $primitive?htmlentities($primitive->toString(), ENT_QUOTES):"";
		$t .= "<input type='text' name='value-$label-$index' value='".$value."' size=5/>\n";
		return $t;
											        }
}

class PrimitiveIO_float extends PrimitiveIO_integer {
	function &mkPrimitiveFromFormInput(&$fieldSet, $label, $index) {
		if ($fieldSet->get("update-$label-$index")) {
			$value = $fieldSet->get("value-$label-$index");
			return new Float($value);
		}
		return ($null=null);
	}
}
