<?

class PrimitiveIO_boolean extends PrimitiveIO {
	function mkFormHTML(&$primitive, $label, $index) {
		$t = "[ update: <input type='checkbox' name='update-$label-$index' value='1'/> ]\n";
		$t .= "<b>".$label."[".$index."]</b>: \n";
		$bool = $primitive?null:$primitive->getBooleanValue();
		$t .= "<input type='radio' name='value-$label-$index' value='true'".(($bool===true)?" checked":"")."> true\n";
		$t .= "<input type='radio' name='value-$label-$index' value='false'".(($bool===false)?" checked":"")."> false\n";
		return $t;
	}
	function &mkPrimitiveFromFormInput(&$fieldSet, $label, $index) {
		if ($fieldSet->get("update-$label-$index")) {
			$value = $fieldSet->get("value-$label-$index")=="true"?true:false;
			return new Boolean($value);
		}
		return ($null=null);
	}
}
