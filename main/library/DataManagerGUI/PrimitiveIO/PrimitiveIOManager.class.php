<?php
/**
 * @package polyphony.library.datamanager_gui
 */

/**
 * Handles the creation of {@link PrimitiveIO} objects for different data types, as registered with the DataTypeManager of Harmoni.
 * @package polyphony.library.datamanagergui
 * @author Gabe Schine
 * @copyright Middlebury College, 2005
 */

class PrimitiveIOManager extends ServiceInterface {
	
	/**
	 * Creates a new {@link PrimitiveIO} object for the given dataType.
	 * @param string $dataType a datatype string as registered with the DataManager of Harmoni
	 * @return ref object A new {@link PrimitiveIO} object.
	 * @access public
	 */
	function &createObject($dataType) {
		$class = "PrimitiveIO_".$dataType;
		if (!class_exists($class)) return ($null=null);

		return new $class();
	}

	/**
	 * Creates HTML code to allow input to change the given field in this Record.
	 * @param ref object $record The Record object.
	 * @param string $label the label of the field to output
	 * @return string
	 */
	function mkFormHTMLForField(&$record, $label) {
		$t = '';
		$schema =& $record->getSchema();
		$field =& $schema->getField($label);
		$mult = $field->getMultFlag();
		$io =& PrimitiveIOManager::createObject($field->getType());
		if (!is_object($io)) return '';
		foreach ($record->getIndices($label) as $index) {
			$delete = $record->deleted($label, $index)?"undelete":"delete";
			$t .= "[ $delete: <input type='checkbox' name='$delete-$label-$index' value='1'/> ]\n";
			$t .= $io->mkFormHTML($record->getCurrentValuePrimitive($label, $index),$label, $index);
			$t .= "<br/>\n";
		}
		$null = null;
		if ($mult || $record->numValues($label)==0) $t .= $io->mkFormHTML($null, $label, "new")."<br/>\n";
		return $t;
	}
	
	/** 
	 * Updates the record's values based on the form input for the given field
	 * @param ref object $record the Record object.
	 * @param ref object $httpVars A FieldSet object containing the HTTP form input
	 * @param string $label the field to update
	 * @return void
	 */
	function updateRecordFieldFromFormInput(&$record, &$httpVars, $label) {
		$schema =& $record->getSchema();
		$field =& $schema->getField($label);
		$mult = $field->getMultFlag();

		$io =& PrimitiveIOManager::createObject($field->getType());
		if (!is_object($io)) return;
		foreach ($record->getIndices($label) as $index) {
			$primitive =& $io->mkPrimitiveFromFormInput($httpVars, $label, $index);
			if ($primitive) $record->setValue($label, $primitive, $index);
			if ($httpVars->get("delete-$label-$index")) {
				$record->deleteValue($label, $index);
			}
			if ($httpVars->get("undelete-$label-$index")) {
				$record->undeleteValue($label, $index);
			}
		}
		if ($mult || !$record->numValues($label)) {
			$primitive =& $io->mkPrimitiveFromFormInput($httpVars, $label, "new");
			if ($primitive) $record->setValue($label, $primitive, NEW_VALUE);
		}
	}
}
