<?php
/**
 * @since 11/7/06
 * @package polyphony.modules.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagAction.abstract.php,v 1.1.2.8 2006/11/15 18:08:54 adamfranco Exp $
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
 * @version $Id: TagAction.abstract.php,v 1.1.2.8 2006/11/15 18:08:54 adamfranco Exp $
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
		
		$harmoni =& Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		
		$actionRows->add(new Block($this->getTagMenu(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		ob_start();
		print $this->getTagCloud($this->getTags(), $this->getViewAction());
		$actionRows->add(new Block(ob_get_clean(), HIGHLIT_BLOCK), "100%", null, LEFT, TOP);
		
		
		$harmoni->request->endNamespace();
		textdomain($defaultTextDomain);
	}
	
	/**
	 * answer the tag cloud html
	 * 
	 * @param object Iterator $tags
	 * @param optional string $viewAction The action to use when clicking on a tag. 
	 *							 Usually view or viewuser
	 * @param optional array $styles An array of style-strings to use for various 
	 *						levels of of tag occurrances.
	 * @return string
	 * @access public
	 * @since 11/7/06
	 */
	function getTagCloud ($tags, $viewAction = 'view', $styles = null, $additionalParams = null) {
		ob_start();
		if ($tags->hasNext()) {
			$harmoni =& Harmoni::instance();
			$harmoni->request->startNamespace("polyphony-tags");
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
			
			if (!is_array($styles))
				$styles = TagAction::getDefaultStyles();
			
			$incrementSize = ceil(($maxFreq - $minFreq)/count($styles));
			if (!$incrementSize)
				$incrementSize = 1;
			
			for ($key=0; $key < count($tagArray); $key++) {
				$tag =& $tagArray[$key];
				$group = 0;
				for ($i=0; $i < $tag->getOccurances() && $group < count($styles); $i = $i + $incrementSize) {
					$style = $styles[$group];
					$group++;
				}
				$parameters = array();
				if (RequestContext::value('agent_id'))
					$parameters['agent_id'] = RequestContext::value('agent_id');
				if (is_array($additionalParams) && count($additionalParams)) {
					foreach ($additionalParams as $name => $value)
						$parameters[$name] = $value;
				}
				$parameters["tag"] = $tag->getValue();
				$url = $harmoni->request->quickURL('tags', $viewAction, $parameters);
				print "\n\t<a rel='tag' href='".$url."' ";
				print " title=\"";
// 				print str_replace('%2', $tag->getValue(),
// 						str_replace('%1', $tag->getOccurances(), 
// 							_("View (%1) items tagged with '%2'")));
				print str_replace('%1', $tag->getValue(),
							_("View items tagged with '%1'"));
				print "\" style='".$style."'>";
				print $tag->getValue()."</a> ";
			}
			$harmoni->request->endNamespace();
		}
		
		return ob_get_clean();
	}
	
	/**
	 * Answer the default styles for the Tag cloud
	 * 
	 * @return array
	 * @access public
	 * @since 11/15/06
	 * @static
	 */
	function getDefaultStyles () {
		return array(
					"font-size: 75%;",
					"font-size: 100%;",
					"font-size: 125%;",
					"font-size: 150%;"
				);
	}
	
	/**
	 * Answer a div element with the tag cloud html
	 * 
	 * @param object Iterator $tags
	 * @param optional string $viewAction The action to use when clicking on a tag. 
	 *							 Usually view or viewuser
	 * @param optional array $styles An array of style-strings to use for various 
	 *						levels of of tag occurrances.
	 * @return string
	 * @access public
	 * @since 11/14/06
	 * @static
	 */
	function getTagCloudDiv ($tags, $viewAction = 'view', $styles = null, $additionalParams = null) {
		ob_start();
		print "\n<div style='text-align: justify'>";
		print TagAction::getTagCloud($tags, $viewAction, $styles, $additionalParams);
		print "\n</div>";
		return ob_get_clean();
	}
	
	/**
	 * Print the tag cloud and tagging link for an item
	 * 
	 * @param object TaggedItem $item
	 * @param optional string $viewAction The action to use when clicking on a tag. 
	 *							 Usually view or viewuser
	 * @param optional array $styles An array of style-strings to use for various 
	 *						levels of of tag occurrances.
	 * @return string
	 * @access public
	 * @since 11/14/06
	 * @static
	 */
	function getTagCloudForItem (&$item, $viewAction = 'view', $styles = null, $additionalParams = null) {
		ob_start();
		print "\n<div>";
		print TagAction::getTagCloud($item->getTags(), $viewAction, $styles, $additionalParams);
		
		print "\n\t<a onclick=\"";
		print "this.viewAction = '".$viewAction."'; ";
		// register the styles with the tagger
		if (!is_array($styles))
			$styles = TagAction::getDefaultStyles();
		print "this.styles = new Array(); ";
		foreach ($styles as $style) {
			$styleStrings = explode(";", $style);
			print "this.styles.push({";
			foreach ($styleStrings as $styleString) {
				if (preg_match('/([a-z0-9\-]+):\s?([a-z0-9\-\s%]+)/i', $styleString, $matches)) 
				{
					// Reformat the style name for javascript
					$styleName = trim($matches[1]);
					$styleNameParts = explode('-', $styleName);
					$styleName = $styleNameParts[0];
					for ($i = 1; $i < count($styleNameParts); $i++)
						$styleName .= ucfirst($styleNameParts[$i]);
					
					
					$styleValue = trim($matches[2]);
					print "'".$styleName."': '".$styleValue."', ";
				}
			}
			print "}); ";
		}
		
		print "Tagger.run('".$item->getIdString()."', '".$item->getSystem()."', this);";
		print "\" title='"._("Add Tags to this Item")."'";
		print " style='font-weight: bold;'>";
		print _("+Tag")."</a>";
		print "\n</div>";
		return ob_get_clean();
	}
	
	/**
	 * Answer the tag cloud for the assets in a repository
	 * 
	 * @param object Repository $repository
	 * @return string
	 * @access public
	 * @since 11/14/06
	 * @static
	 */
	function getTagCloudForRepository ( &$repository, $system, $viewAction = 'viewRepository', $styles = null) {
		if (!is_object($repository))
			return "";
		
		$repositoryId =& $repository->getId();
		$tagManager =& Services::getService('Tagging');
		$items = array();
		$assets =& $repository->getAssets();
		while($assets->hasNext()) {
			$asset =& $assets->next();
			$items[] =& TaggedItem::forId($asset->getId(), $system);
		}
		return TagAction::getTagCloudDiv($tagManager->getTagsForItems(
						new HarmoniIterator($items)), 
					$viewAction, $styles, 
					array('repository_id' => $repositoryId->getIdString(),
						'system' => $system));
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
	
	/**
	 * Answer a menu for the tagging system
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 */
	function getTagMenu () {
		$harmoni =& Harmoni::instance();
		
		ob_start();
		$tagManager =& Services::getService("Tagging");
		if ($currentUserIdString = $tagManager->getCurrentUserIdString()) {
			if ($harmoni->getCurrentAction() == 'tags.user' 
				&& RequestContext::value('agent_id') == $currentUserIdString) 
			{
				print ""._("your tags")." &nbsp; ";
			} else {
				$url = $harmoni->request->quickURL('tags', 'user', 
					array('agent_id' => $tagManager->getCurrentUserIdString()));
				print "<a href='".$url."'>"._("your tags")."</a> &nbsp; ";
			}
		}
		if ($harmoni->getCurrentAction() == 'tags.all') {
			print _("all tags");
		} else {
			$url = $harmoni->request->quickURL('tags', 'all');
			print "<a href='".$url."'>"._("all tags")."</a> &nbsp;";
		}
		
		if (RequestContext::value('tag')) {
			if ($harmoni->getCurrentAction() == 'tags.viewuser' 
				&& RequestContext::value('agent_id') == $currentUserIdString) 
			{
				$url = $harmoni->request->quickURL('tags', 'view', 
					array('agent_id' => $tagManager->getCurrentUserIdString(),
					'tag' => RequestContext::value('tag')));
				print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("items tagged '%1' by everyone"))."</a> &nbsp; ";
			} else {
				$url = $harmoni->request->quickURL('tags', 'viewuser', 
					array('agent_id' => $tagManager->getCurrentUserIdString(),
					'tag' => RequestContext::value('tag')));
				print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("items tagged '%1' by you"))."</a> &nbsp; ";
			}
		}
		
		return ob_get_clean();
	}
}

?>