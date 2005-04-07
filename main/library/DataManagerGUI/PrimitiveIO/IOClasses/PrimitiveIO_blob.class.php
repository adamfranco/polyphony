<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_blob.class.php,v 1.3 2005/04/07 17:07:42 adamfranco Exp $
 */

/**
 * 
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_blob.class.php,v 1.3 2005/04/07 17:07:42 adamfranco Exp $
 */
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
