<?php
/**
 * @since 12/12/06
 * @package polyphony.export
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: getFile.act.php,v 1.4 2008/03/06 19:03:21 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/Action.class.php");


/**
 * Give the user the file that they wish to download
 * 
 * @since 12/12/06
 * @package polyphony.export
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: getFile.act.php,v 1.4 2008/03/06 19:03:21 adamfranco Exp $
 */
class getFileAction 
	extends Action
{
	
	/**
	 * Authorization
	 * 
	 * @return boolean
	 * @access public
	 * @since 3/6/08
	 */
	public function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Execute the action
	 * 
	 * @return void
	 * @access public
	 * @since 12/12/06
	 */
	function execute () {
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('export');
		$file = urldecode(RequestContext::value('file'));
		$harmoni->request->endNamespace();
		
		// Check that this is a valid file to return. We don't want to give them
		// any file on the system.
		if (!is_array($_SESSION['EXPORTED_FILES']) 
			|| !isset($_SESSION['EXPORTED_FILES'][$file])
			|| !is_array($_SESSION['EXPORTED_FILES'][$file]))
		{
			print "<h2>"._("Download Error")."</h2>";
			print "<p>"._("This download is not currently available. It may have been already downloaded and removed.")."</p>";
			
			print "<p style='margin: 10px; margin-left: 20px;'><a href='".strip_tags($_SERVER['HTTP_REFERER'])."'>"._("Download again")."</a></p>";
			
			print "<p style=''><a href='".$harmoni->request->quickURL("collections", "namebrowse")."'>"._("&lt;-- Return")."</a></p>";
			print "<hr/>";
			
			throw new OperationFailedException("Could not download export. ".$file." is not in the allowed list to download.");
		}
		
		header("Content-type: ".$_SESSION['EXPORTED_FILES'][$file]['mime']);
		header('Content-Disposition: attachment; filename="'.
			$_SESSION['EXPORTED_FILES'][$file]['name'].'"');
		header('Content-Length: ' . filesize($_SESSION['EXPORTED_FILES'][$file]['file']));
		
		// clean all output buffers
		while (ob_get_level())
			ob_end_clean();
		
		
		$handle =fopen($_SESSION['EXPORTED_FILES'][$file]['file'], "rb");
		while (!feof($handle)) {
			if (!ini_get('safe_mode'))
				set_time_limit(0);
			print fread($handle, 8192);
			flush();
			if (ob_get_length())
				ob_end_flush();
		}
		fclose($handle);
		
		unlink($_SESSION['EXPORTED_FILES'][$file]['file']);
		unset($_SESSION['EXPORTED_FILES'][$file]);
		exit;
	}
	
}

?>