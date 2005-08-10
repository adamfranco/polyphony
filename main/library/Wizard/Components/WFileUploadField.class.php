<?php

/**
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WFileUploadField.class.php,v 1.2 2005/08/10 17:52:05 adamfranco Exp $
 */

require_once (POLYPHONY."/main/library/Wizard/WizardComponent.abstract.php");

/**
 * This adds an input type='text' field to a {@link Wizard}.
 * 
 * @since Jul 21, 2005
 * @package polyphony.library.wizard.components
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: WFileUploadField.class.php,v 1.2 2005/08/10 17:52:05 adamfranco Exp $
 */
class WFileUploadField 
	extends WizardComponent 
{
	
	var $_style = null;
	var $_filename = null;
	var $_hdfile = null;
	var $_size = null;
	var $_changed = false;
	var $_mimetype = null;

	var $_errString = null;

	var $_accept = array ();

	/**
	 * Sets the CSS style of this field.
	 * @param string $style
	 * @access public
	 * @return void
	 */
	function setStyle($style) {
		$this->_style = $style;
	}

	/**
	 * Sets the accepted mimetypes (passed to the browser).
	 * @param array $types
	 * @access public
	 * @return void
	 */
	function setAcceptedMimetypes($types) {
		$this->_accept = $types;
	}

	/**
	 * Tells this field to use the passed file on the filesystem as a starting value. If $filename is
	 * passed, it is used as the display filename instead of the basename of the path.
	 * @param string $path
	 * @param optional string $filename
	 * @access public
	 * @return void
	 */
	function setFile($path, $filename = null) {
		$this->_filename = $filename != null ? $filename : basename($filename);
		$this->_size = filesize($filename);
		$this->_hdfile = $filename;
	}

	/**
	 * Tells the wizard component to update itself - this may include getting
	 * form post data or validation - whatever this particular component wants to
	 * do every pageload. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return boolean - TRUE if everything is OK
	 */
	function update($fieldName) {
		$val = RequestContext :: value($fieldName);
		if (is_array($val)) {
			$uploadFile = $val['tmp_name'];
			if (is_uploaded_file($uploadFile)) {
				if ($val['error'] == 0) {
					// no error!

					// get a new temp filename so PHP doesn't delete the uploaded file
					$tmpDir = dirname($uploadFile);
					$newFile = tempnam($tmpDir, "WU");
					if (file_exists($newFile))
						@ unlink($newFile);

					if (move_uploaded_file($uploadFile, $newFile)) {
						// if we already have an uploaded file, delete it
						if ($this->_changed) {
							@ unlink($this->_hdfile);
						}
						$this->_changed = true;
						$this->_hdfile = $newFile;
						$this->_filename = $val['name'];
						$this->_mimetype = $val['type'];
						$this->_size = $val['size'];
						return true;
					}
				} else {
					// generate an error string for display
					$errString = '';
					switch ($val['error']) {
						case UPLOAD_ERR_INI_SIZE :
						case UPLOAD_ERR_FORM_SIZE :
							$errString = dgettext("polyphony", "File exceeded maximum allowed size.");
							break;
						case UPLOAD_ERR_PARTIAL :
							$errString = dgettext("polyphony", "An upload error occured. (partial upload)");
							break;
						case UPLOAD_ERR_NO_FILE :
							$errString = dgettext("polyphony", "An upload error occured. (no file)");
							break;
						case UPLOAD_ERR_NO_TMP_DIR :
							$errString = dgettext("polyphony", "An upload error occured. (no temp directory)");
							break;
					}

					$this->_errString = $errString;
				}
			}
		}
		return false;
	}

	/**
	 * For file upload, returns an array:
	 * name => the original name of the file
	 * size => the size in bytes of the file
	 * type => the mimetype of the file (if the browser supplied it)
	 * tmp_name => the location on the hard drive of the file
	 * @access public
	 * @return mixed
	 */
	function getAllValues() {
		$ar = array ();
		$ar['name'] = $this->_filename;
		$ar['size'] = $this->_size;
		$ar['type'] = $this->_mimetype;
		$ar['tmp_name'] = $this->_hdfile;
		return $ar;
	}

	/**
	 * Returns a filesize string
	 * @param integer $size
	 * @access private
	 * @return string
	 */
	function _mkfilesize($size) {
		$j = 0;
		$ext = array ("B", "KB", "MB", "GB", "TB");
		$file_size = $size;
		while ($file_size >= pow(1024, $j))
			++ $j;
		$file_size = round($file_size / pow(1024, $j -1) * 100) / 100 . $ext[$j -1];
		return $file_size;
	}

	/**
	 * Returns a block of XHTML-valid code that contains markup for this specific
	 * component. 
	 * @param string $fieldName The field name to use when outputting form data or
	 * similar parameters/information.
	 * @access public
	 * @return string
	 */
	function getMarkup($fieldName) {
		$name = RequestContext :: name($fieldName);

		$m = "";

		if ($this->_filename) {
			$m .= "<i>". $this->_filename." (".$this->_mkfilesize($this->_size).")</i>\n";
		}

		$m .= "<input type='file' name='$name'";
		if (count($this->_accept)) {
			$m .= " accept='".implode(", ", $this->_accept)."'";
		}
		if ($this->_style) {
			$m .= " style=\"".addslashes($this->_style)."\"";
		}
		$m .= " />";

		if ($this->_errString) {
			$m .= "<span style='color: red; font-weight: 900;'>".$this->_errString."</span>";
			$this->_errString = null;
		}

		return $m;
	}
}
?>