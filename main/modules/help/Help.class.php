<?php
/**
 * @since 12/4/07
 * @package polyphony.help
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Help.class.php,v 1.1 2007/12/04 18:54:04 adamfranco Exp $
 */ 

/**
 * The Help class is a collection of static methods for easily creating help links.
 * 
 * @since 12/4/07
 * @package polyphony.help
 * 
 * @copyright Copyright &copy; 2007, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Help.class.php,v 1.1 2007/12/04 18:54:04 adamfranco Exp $
 */
class Help {
		
	/**
	 * Answer a help link (HTML markup) for the topic specified.
	 * 
	 * @param string $topic
	 * @return string
	 * @access public
	 * @since 12/4/07
	 * @static
	 */
	public static function link ($topic = '') {
		ArgumentValidator::validate($topic, StringValidatorRule::getRule());
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace(null);
		if (strlen($topic))
			$url = $harmoni->request->quickURL('help', 'browse_help', array('topic' => $topic));
		else
			$url = $harmoni->request->quickURL('help', 'browse_help');
		$harmoni->request->endNamespace();
		
		ob_start();
		print "<a href='$url' ";
		print "onclick=\"";
		
		print "var url='$url'; ";
		print "url = url.urlDecodeAmpersands(); ";
		print "var helpWindow = window.open(url, 'help', 'width=700,height=600,scrollbars=yes,resizable=yes'); ";
		print "helpWindow.focus(); ";
		print "return false; ";
		
		print "\">"._("Help")."</a>";
		
		return ob_get_clean();
	}
	
}

?>