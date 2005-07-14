<?php
/**
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleRecordPrinter.class.php,v 1.7 2005/07/14 17:16:44 adamfranco Exp $
 */

/**
 *
 *
 * @package polyphony.library.datamanager_gui
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: SimpleRecordPrinter.class.php,v 1.7 2005/07/14 17:16:44 adamfranco Exp $
 */
class SimpleRecordPrinter {
	
	/**
	 * Prints out the specified record in a plain-text format.
	 * @access public
	 * @return string|void
	 * @static
	 */
	function printRecord(&$record, $html=true, $return=false)
	{
		if ($record->getFetchMode == -1) {
			print "<b>Record not populated...</b><br />";
			return;
		}
		
		$fetchModeStrings = array(
			RECORD_NODATA=>"no_data",
			RECORD_CURRENT=>"current_data",
			RECORD_FULL=>"full_data"
		);
		$recordManager =& Services::getService("RecordManager");
		$v = $record->isVersionControlled();
		
		$text = "";
		if ($html) $text .= "<pre>\n";
		
		if (!$record->isActive()) $text.="(del) ";
		
		if ($record->getID()) {
			$text .= "ID: ".$record->getID();
			$ids = $recordManager->getRecordSetIDsContaining($record);
			$text .= "; groups: ";
			if (count($ids)) $text .= implode(", ",$ids);
			else $text .= "none";
			$text .= "; ";
		}
		$text .= "type: ".HarmoniType::typeToString($record->getType());
		$created =& $record->getCreationDate();
		$text .= "; created: " . $created->asString(true);
		$text .= "; ".$fetchModeStrings[$record->getFetchMode()];
//		$text .= print_r($record->getFetchMode(), true);
		if ($v) $text .= "; vControl";
		$text .= "\n";
		
		$schema =& $record->getSchema();
		$labels = $schema->getAllLabels();
		$noValue = array();
		
		foreach ($labels as $label) {
			if ($record->numValues($label)) {
				foreach ($record->getIndices($label) as $i) {
					$value =& $record->getRecordFieldValue($label,$i);
					if ($v) {
						foreach ($value->getVersionIDs() as $verID) {
							$version =& $value->getVersion($verID);
							if (!$version->isActive()) continue;
							$text .= "\t";
							$text .= SimpleRecordPrinter::_doRecordFieldData($value, $version, $label, $i, true, $verID);
							$text .= "\n";
						}
						foreach ($value->getVersionIDs() as $verID) {
							$version =& $value->getVersion($verID);
							if ($version->isActive()) continue;
							$text .= "\t -";
							$text .= SimpleRecordPrinter::_doRecordFieldData($value, $version, $label, $i, true, $verID);
							$text .= "\n";
						}
					} else {
						$text .= "\t";
						$version =& $value->getActiveVersion();
						if (!$version) $version =& $value->getNewestVersion();
						if (!$version) continue;
						$text .= SimpleRecordPrinter::_doRecordFieldData($value, $version, $label, $i);
						$text .= "\n";
					}
				}
			} else $noValue[] = $label;
		}
		
		if (count($noValue)) $text .= "no values for: ".implode(", ",$noValue) . "\n";
		
		if ($html) $text .= "</pre>\n";
		
		if ($return) return $text;
		else print $text;
	}
	
	/**
	 * Prints out a single {@link RecordFieldData} line.
	 * @access public
	 * @return string
	 */
	 function _doRecordFieldData(&$value, &$version, $label, $i, $v = false, $vID = null)
	 {
	 	$text = '';
	 	$primitive =& $version->getPrimitive();
	 	$flags = array();
	 	if (!$version->isActive()) $flags[] = "d";
	 	if ($version->willPrune()) $flags[] = "p";
	 	if ($version->_update) $flags[] = "u";
	 	if ($v && $version->isActive()) $flags[] = "A";
	 	if (!count($flags)) $flags[] = "-";
	 	$text .= "(".implode("",$flags).") ";
	 	$text .= "\t";
	 	$text .= $label."[$i] = ";
	 	$text .= $primitive->asString();
	 	
	 	if ($v) {
	 		$date =& $version->getDate();
	 		$text .= "\t\t(".$vID." - ".$date->asString(true).")";
	 	}
	 	
	 	return $text;
	 }
	
}