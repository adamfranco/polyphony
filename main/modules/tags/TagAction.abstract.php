<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagAction.abstract.php,v 1.1.2.1 2006/11/08 20:45:55 adamfranco Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagAction.abstract.php,v 1.1.2.1 2006/11/08 20:45:55 adamfranco Exp $
 */
class TagAction 
	extends MainWindowAction
{
	
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
	 * Build the content for this action
	 * 
	 * @return void
	 * @access public
	 * @since 11/07/06
	 */
	function buildContent () {
		$defaultTextDomain = textdomain("polyphony");
		$actionRows =& $this->getActionRows();
		ob_start();
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		
		print $this->getTagCloud($this->getTags(), $this->getViewAction());
		
		
		$actionRows->add(new Block(ob_get_clean(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		$harmoni->request->endNamespace();
		textdomain($defaultTextDomain);
	}
	
	/**
	 * answer the tag cloud html
	 * 
	 * @param object Iterator $tags
	 * @return string
	 * @access public
	 * @since 11/7/06
	 */
	function getTagCloud ($tags, $viewAction = 'view') {
		ob_start();
		if ($tags->hasNext()) {
			$harmoni =& Harmoni::instance();
			$tagArray = array();
			$tag = $tags->next();
			$tagArray[] =& $tag;
			$minFreq = $maxFreq = $tag->getOccurances();
			
			while ($tags->hasNext()) {
				$tag =& $tags->next();
				$tagArray[] =& $tag;
				if ($tag->getOccurances() < $minFreq)
					$minFreq = $tag->getOccurances();
				if ($tag->getOccurances() > $maxFreq)
					$maxFreq = $tag->getOccurances();
			}
			
			$styles = array(
				"font-size: 75%;",
				"font-size: 100%;",
				"font-size: 125%;",
				"font-size: 150%;"
			);
			$incrementSize = ceil(($maxFreq - $minFreq)/count($styles));
			
			print "\n<div style='text-align: justify'>";
			for ($key=0; $key < count($tagArray); $key++) {
				$tag =& $tagArray[$key];
				$group = 0;
				for ($i=0; $i < $tag->getOccurances() && $group < count($styles); $i = $i + $incrementSize) {
					$style = $styles[$group];
					$group++;
				}
				$parameters = array("tag" => $tag->getValue());
				if (RequestContext::value('agent_id'))
					$parameters['agent_id'] = RequestContext::value('agent_id');
				$url = $harmoni->request->quickURL('tags', $viewAction, $parameters);
				print "\n\t<a href='".$url."' title='".$tag->getOccurances()." ocurrances' style='".$style."'>";
				print $tag->getValue()."</a> ";
			}
			print "\n</div>";
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Answer the tags
	 * 
	 * @return object TagIterator
	 * @access public
	 * @since 11/8/06
	 */
	function &getTags () {
		die ("Method <b>".__FUNCTION__."()</b> declared in interface<b> ".__CLASS__."</b> has not been overloaded in a child class.");
	}
}

?>