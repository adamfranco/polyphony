<?

class PrimitiveIO_blob extends PrimitiveIO {
	function mkFormHTML(&$primitive, $label, $index) {
		$t = "[ update: <input type='checkbox' name='update-$label-$index' value='1'/> ]\n";
		$t .= "<b>".$label."[".$index."]</b>: \n";
		$value = $primitive?htmlentities($primitive->toString(), ENT_QUOTES):"";
		$t .= "<br/><textarea rows='3' cols='60' scrolling='virtual' name='value-$label-$index'>".$value."</textarea>\n";
		return $t;
	}
	function &mkPrimitiveFromFormInput(&$fieldSet, $label, $index) {
		if ($fieldSet->get("update-$label-$index")) {
			$value = $fieldSet->get("value-$label-$index");
			return new Blob($value);
		}
		return ($null=null);
	}
}
