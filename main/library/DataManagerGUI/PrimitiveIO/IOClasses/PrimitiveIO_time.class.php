<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_time.class.php,v 1.3 2005/04/07 17:07:45 adamfranco Exp $
 */

/**
 * 
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: PrimitiveIO_time.class.php,v 1.3 2005/04/07 17:07:45 adamfranco Exp $
 */
class PrimitiveIO_time extends PrimitiveIO {
	function mkFormHTML(&$primitive, $label, $index) {
		$t = "[ update: <input type='checkbox' name='update-$label-$index' value='1'/> ]\n";
		$t .= "<b>".$label."[".$index."]</b>: \n";
		$month = $day = $hour = $minute = $second = 0;
		$year = 1970;
		if ($primitive) {
			$year = $primitive->getYear();
			$month = $primitive->getMonth();
			$day = $primitive->getDay();
			$hour = $primitive->getHours();
			$minute = $primitive->getMinutes();
			$second = $primitive->getSeconds();
		}
		// year
		$t .= "<i>Y/M/D</i>: \n";
		$t .= "<input type='text' name='year-$label-$index' size='4' maxlength='4' value='$year'/>\n<b>/</b>\n";
		// month
		$t .= "<select name='month-$label-$index'>\n";
		$monthsArray = Time::getMonthsArray();
		for ($i=0; $i < 12; $i++) {
			$t .= "<option value='".($i+1)."'".(($month==($i+1))?" selected":"").">".($i+1)." - ".$monthsArray[$i]."</option>\n";
		}
		$t .= "</select>\n<b>/</b>\n";
		// day
		$t .= "<select name='day-$label-$index'>\n";
		for ($i=1; $i <= 31; $i++) {
			$t .= "<option value='$i'".($day==$i?" selected":"").">$i</option>\n";
		}
		$t .= "</select>\n";
		// hour
		$t .= "<i>HH:MM:ss</i> : ";
		$t .= "<select name='hour-$label-$index'>\n";
		for ($i=0; $i <= 23; $i++) {
			$t .= "<option value='$i'".($hour==$i?" selected":"").">$i</option>\n";
		}
		$t .= "</select>\n<b>:</b>\n";
		// minutes
		$t .= "<select name='minute-$label-$index'>\n";
		for ($i=0; $i <= 59; $i++) {
			$t .= "<option value='$i'".($minute==$i?" selected":"").">$i</option>\n";
		}
		$t .= "</select>\n<b>:</b>\n";
		// seconds
		$t .= "<select name='second-$label-$index'>\n";
		for ($i=0; $i <= 59; $i++) {
			$t .= "<option value='$i'".($second==$i?" selected":"").">$i</option>\n";
		}
		$t .= "</select>\n";


		return $t;
	}
	function &mkPrimitiveFromFormInput(&$fieldSet, $label, $index) {
		if ($fieldSet->get("update-$label-$index")) {
			$year = $fieldSet->get("year-$label-$index");
			$month = $fieldSet->get("month-$label-$index");
			$day = $fieldSet->get("day-$label-$index");
			$hour = $fieldSet->get("hour-$label-$index");
			$minute = $fieldSet->get("minute-$label-$index");
			$second = $fieldSet->get("second-$label-$index");
			$timestamp = mktime($hour, $minute, $second, $month, $day, $year);
			$time =& new Time();
			$time->setDate($timestamp);
			return $time;
		}
		return ($null=null);
	}
}
