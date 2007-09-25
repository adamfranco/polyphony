<?php
/**
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: filebrowser.act.php,v 1.3 2007/09/25 18:31:46 adamfranco Exp $
 */ 

/**
 * This class is the most simple abstraction of an action. It provides a structure
 * for common methods
 * 
 * @package polyphony.AbstractActions
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: filebrowser.act.php,v 1.3 2007/09/25 18:31:46 adamfranco Exp $
 * @since 4/28/05
 */
class filebrowserAction
	extends Action 
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 4/26/05
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 4/26/05
	 */
	function getHeadingText () {
		return _('File Browser');
	}
	
	/**
	 * Execute this action.
	 * 
	 * @return mixed
	 * @access public
	 * @since 4/25/05
	 */
	function execute () {
		$title = $this->getHeadingText();
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace('fckeditor');
		$nodeId = RequestContext::value('node');
		$harmoni->request->endNamespace();
		$POLYPHONY_PATH = POLYPHONY_PATH;
		$MYPATH = MYPATH;
		
		print <<<END


<html>
<head>
	<title>$title</title>

END;
	
	require(POLYPHONY_DIR."/main/library/Harmoni.js.inc.php");

		print <<< END
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/CenteredPanel.js'></script>
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/TabbedContent.js'></script>
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/prototype.js'></script>
	<script type='text/javascript' src='$POLYPHONY_PATH/javascript/js_quicktags.js'></script>
	<script type='text/javascript' src='$MYPATH/javascript/MediaLibrary.js'></script>
	<link rel='stylesheet' type='text/css' href='$MYPATH/javascript/MediaLibrary.css'/>
	
	<script type='text/javascript'>
		// <![CDATA[
		
		MediaLibrary.prototype.onClose = function () { 
			window.close();
		}
		
		// ]]>
	</script>
	
</head>
<body onload="this.onUse = function (mediaFile) { window.opener.SetUrl(mediaFile.getUrl());}; MediaLibrary.run('$nodeId', this); ">
	
</body>
</html>


END;
		
		exit;
	}
}