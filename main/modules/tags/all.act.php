<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: all.act.php,v 1.4 2007/09/04 20:28:14 adamfranco Exp $
 */ 

require_once(dirname(__FILE__)."/TagAction.abstract.php");

/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: all.act.php,v 1.4 2007/09/04 20:28:14 adamfranco Exp $
 */
class allAction 
	extends TagAction
{
		
	/**
	 * Check Authorizations
	 * 
	 * @return boolean
	 * @access public
	 * @since 11/07/06
	 */
	function isAuthorizedToExecute () {
		return true;
	}
	
	/**
	 * Return the heading text for this action, or an empty string.
	 * 
	 * @return string
	 * @access public
	 * @since 11/07/06
	 */
	function getHeadingText () {
		return dgettext("polyphony", "Browse Tags");
	}
	
	/**
	 * Answer the tags
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function getTags () {
		$tagManager = Services::getService("Tagging");
		$tags =$tagManager->getTags(TAG_SORT_ALFA, $this->getNumTags());
// 		printpre($tags);
		return $tags;
	}
	
	/**
	 * Answer the number of tags to show
	 * 
	 * @return integer
	 * @access public
	 * @since 12/5/06
	 */
	function getNumTags () {
		if (RequestContext::value('num_tags') !== null)
			$_SESSION['__NUM_TAGS'] = intval(RequestContext::value('num_tags'));
		else if (!isset($_SESSION['__NUM_TAGS']))
			$_SESSION['__NUM_TAGS'] = 100;
		
		return $_SESSION['__NUM_TAGS'];
	}
	
	/**
	 * This just adds an agent list for debugging purposes.
	 * 
	 * @return void
	 * @access public
	 * @since 11/07/06
	 */
	function buildContent () {
		parent::buildContent();
		
		$defaultTextDomain = textdomain("polyphony");
		$actionRows =$this->getActionRows();
		ob_start();
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		
		ob_start();
		print "\n<select name='".RequestContext::name('num_tags')."'";
		print " onchange=\"";
		print "var url='".$harmoni->request->quickURL(null, null, array('num_tags' => 'XXXXX'))."'; ";
		print "window.location = url.replace(/XXXXX/, this.value).urlDecodeAmpersands(); ";
		print "\">";
		$options = array(50, 100, 200, 400, 600, 1000, 0);
		foreach ($options as $option)
			print "\n\t<option value='".$option."' ".(($option == $this->getNumTags())?" selected='selected'":"").">".(($option)?$option:_('all'))."</option>";
		print "\n</select>";
		print str_replace('%1', ob_get_clean(), _("Showing top %1 tags"));
		
		
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		$harmoni->request->endNamespace();
		textdomain($defaultTextDomain);
	}
	
	/**
	 * Answer the action to use for viewing tags
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getViewAction () {
		return 'view';
	}
}

?>