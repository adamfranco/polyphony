<?php
/**
 * @since 11/7/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagAction.abstract.php,v 1.10 2008/04/11 19:50:02 achapin Exp $
 */ 

require_once(POLYPHONY."/main/library/AbstractActions/MainWindowAction.class.php");

/**
 * <##>
 * 
 * @since 11/7/06
 * @package polyphony.tagging
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TagAction.abstract.php,v 1.10 2008/04/11 19:50:02 achapin Exp $
 */
abstract class TagAction 
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
		$actionRows =$this->getActionRows();
		
		$harmoni = Harmoni::instance();
		$harmoni->request->startNamespace("polyphony-tags");
		
		$actionRows->add(new Block($this->getTagMenu(), STANDARD_BLOCK), "100%", null, LEFT, TOP);
		
		ob_start();
		print $this->getTagCloudDiv($this->getTags(), $this->getViewAction());
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
	 * @param optional array $additionalParams An array of parameters to pass in the same namespace
	 * @param optional array $additionalNamespacedParams A two-dimensional array. Keys of the top 
	 *						array are the namespaces, the values of the top array are arrays 
	 * 						of key/value pairs in that namespace.
	 * @return string
	 * @access public
	 * @static
	 * @since 11/7/06
	 */
	static function getTagCloud ($tags, $viewAction = 'view', $styles = null, $additionalParams = null, array $additionalNamespacedParams = array()) {		
		ob_start();
		if ($tags->hasNext()) {
			$harmoni = Harmoni::instance();
			$harmoni->request->startNamespace("polyphony-tags");
			$tagArray = array();
			$occArray = array();
			$nameArray = array();
			$tag = $tags->next();
			$tagArray[] =$tag;
			$nameArray[] = $tag->getValue();
			$occArray[] = $tag->getOccurances();
			$minFreq = $maxFreq = $tag->getOccurances();
			
			while ($tags->hasNext()) {
				$tag =$tags->next();
				$tagArray[] = $tag;
				$nameArray[] = $tag->getValue();
				$occArray[] = $tag->getOccurances();
				if ($tag->getOccurances() < $minFreq)
					$minFreq = $tag->getOccurances();
				if ($tag->getOccurances() > $maxFreq)
					$maxFreq = $tag->getOccurances();
			}
			
			if (!is_array($styles))
				$styles = TagAction::getDefaultStyles();
				
			// First Try to get a meaningful Standard Deviation
			$incrementSize = TagAction::deviation($occArray);	
			
			// If there are only two results, try this method
			if (!$incrementSize)
				$incrementSize = ceil(($maxFreq - $minFreq)/count($styles));
			
			// If we still don't have an increment size, use 1
			if (!$incrementSize)
				$incrementSize = 1;
			
// 			printpre(TagAction::average($occArray));
// 			printpre($incrementSize);

			array_multisort($nameArray, $tagArray);
			
			for ($key=0; $key < count($tagArray); $key++) {
				$tag =$tagArray[$key];
				$group = -1;
				for ($i = $minFreq; $i <= $tag->getOccurances() && $group < (count($styles) - 1); $i = $i + $incrementSize) {
					$group++;
				}
				$group = max(0, $group);
				$style = $styles[$group];
				
				$url = $harmoni->request->mkURL('tags', $viewAction);
				if (RequestContext::value('agent_id'))
					$url->setValue('agent_id', RequestContext::value('agent_id'));
				if (is_array($additionalParams)) {
					foreach ($additionalParams as $name => $value)
						$url->setValue($name, $value);
				}
				
				foreach ($additionalNamespacedParams as $namespace => $params) {
					$harmoni->request->startNamespace($namespace);
					foreach ($params as $name => $value) {
						$url->setValue($name, $value);
					}
					$harmoni->request->endNamespace();
				}
				
				$url->setValue("tag", $tag->getValue());
				
				print "\n\t<a rel='tag' href='".$url->write()."' ";
				print " title=\"";
// 				print $group." ";
// 				print str_replace('%2', $tag->getValue(),
// 						str_replace('%1', $tag->getOccurances(), 
// 							_("View (%1) items tagged with '%2'")));
				print str_replace('%2', $tag->getOccurances(), 
						str_replace('%1', $tag->getValue(),
							_("View items tagged with '%1'. ")."("._("Frequency").": %2)"));
							
				// span styles cloudStyle, rel and frequency NOT validating
				print "\" style='".$style."'";
				print " class='tag_cloud_group_".$group." tag_frequency_".$tag->getOccurances()."'";
				print ">";
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
	static function getDefaultStyles () {
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
	 * @param optional array $additionalParams An array of parameters to pass in the same namespace
	 * @param optional array $additionalNamespacedParams A two-dimensional array. Keys of the top 
	 *						array are the namespaces, the values of the top array are arrays 
	 * 						of key/value pairs in that namespace.
	 * @return string
	 * @access public
	 * @since 11/14/06
	 * @static
	 */
	static function getTagCloudDiv ($tags, $viewAction = 'view', $styles = null, $additionalParams = null, array $additionalNamespacedParams = array()) {
		ob_start();
		
		print "\n<div class='tag_cloud'>";
		print TagAction::getTagCloud($tags, $viewAction, $styles, $additionalParams, $additionalNamespacedParams);
		
		/******************************************************************************
		 * link for alpha sort of tags
		 ******************************************************************************/
		
		print "\n\t<div class='tags_display_options'>"._('Sort by: ');
		print "\n\t\t<a href='#' onclick='var cloud = TagCloud.forContainer(this.parentNode.parentNode); cloud.orderAlpha(); return false;'>";
		print _('a-z');
		print "</a>";
		print " | ";
		
		/******************************************************************************
		 * link for frequency sort of tags (default)
		 * Better defaults would be dependant on number of tags
		 * if # of tags < 15, then display as list sorted by frequency else alpha cloud
		 ******************************************************************************/
		
		print "\n\t\t<a href='#' onclick='var cloud = TagCloud.forContainer(this.parentNode.parentNode); cloud.orderFreq(); return false;'>";
		print _('count');
		print "</a>";
		print "<br/>";
		
		if ($tags->count() > 1) {
		
			print _('View as: ');
			
			/******************************************************************************
			 * link for cloud display of tags (default)
			 * Better defaults would be dependant on number of tags
			 * if # of tags < 15, then display as list sorted by frequency else alpha cloud
			 ******************************************************************************/

			print "Display: ";
			print "\n\t\t<a href='#' onclick='var cloud = TagCloud.forContainer(this.parentNode.parentNode); cloud.showCloud(); return false;'>";
			print "cloud";
			print "</a>";
			
			print " | ";
			
			/******************************************************************************
			 * link for list display of tags
			 ******************************************************************************/
			
			print "\n\t\t<a href='#' onclick='var cloud = TagCloud.forContainer(this.parentNode.parentNode); cloud.showList(); return false;'>";
			print _('list');
			print "</a>";								
		}
		print "\n\t</div>";
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
	static function getReadOnlyTagCloudForItem ($item, $viewAction = 'view', $styles = null, $additionalParams = null) {
		return self::getReadOnlyTagCloudForItems(array($item), $viewAction, $styles, $additionalParams);
	}
	
	/**
	 * Print the tag cloud and tagging link for an array of items
	 * 
	 * @param array $items An array of TaggedItem objects
	 * @param optional string $viewAction The action to use when clicking on a tag. 
	 *							 Usually view or viewuser
	 * @param optional array $styles An array of style-strings to use for various 
	 *						levels of of tag occurrances.
	 * @return string
	 * @access public
	 * @since 11/14/06
	 * @static
	 */
	static function getReadOnlyTagCloudForItems ($items, $viewAction = 'view', $styles = null, $additionalParams = null) {
		ob_start();
		print "\n<div class='tag_cloud'>";
		
		$tagIterator = self::getTagsFromItems($items);
		
		print TagAction::getTagCloud($tagIterator, $viewAction, $styles, $additionalParams);
		
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
	static function getTagCloudForItem ($item, $viewAction = 'view', $styles = null, $additionalParams = null) {
		ob_start();
		print "\n<div>";
		
		print TagAction::getTagCloud($item->getTags(), $viewAction, $styles, $additionalParams);
		
		print "\n\t<span> &nbsp; ";
		print "\n\t\t<a onclick=\"";
		print "this.viewAction = '".$viewAction."'; ";
		// register the styles with the tagger
		if (!is_array($styles))
			$styles = TagAction::getDefaultStyles();
		print "this.styles = new Array(); ";
		foreach ($styles as $style) {
			$styleStrings = explode(";", $style);
			print "this.styles.push({";
			$started = false;
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
					if ($started)
						print ", ";
					print "'".$styleName."': '".$styleValue."'";
				}
			}
			print "}); ";
		}
		
		print "Tagger.run('".$item->getIdString()."', '".$item->getSystem()."', this, this.parentNode.parentNode";
		
		// Add Context parameters to the urls generated when re-writing the tag cloud.
		$harmoni = Harmoni::instance();
		$contextInfo = $harmoni->request->getContextInfo();
		if (count($contextInfo)) {
			print ", {";
			$params = array();
			foreach ($contextInfo as $info) {
				$params[] = $info->name.": {value: '".$info->value."', namespace: '".$info->namespace."'}";
			}
			print implode(', ', $params);
			print "}";
		}
		
		print ");";
		print "\" title='"._("Add Tags to this Item")."'";
		print " style='font-weight: bold;'>";
		print _("+Tag")."</a>";
		print "\n\t</span>";
		print "\n</div>";
		return ob_get_clean();
	}
	
	/**
	 * Answer a iterator of tags combined from multiple items
	 * 
	 * @param array $items An array of TaggedItems
	 * @return object Iterator
	 * @access public
	 * @since 4/7/08
	 * @static
	 */
	static public function getTagsFromItems (array $items) {
		$allTags = array();
		foreach ($items as $item) {
			$tags = $item->getTags();
			while ($tags->hasNext()) {
				$tag = $tags->next();
				if (!isset($allTags[$tag->getValue()])) {
					$allTags[$tag->getValue()] = $tag;
				} else {
					$allTags[$tag->getValue()]->setOccurances(
						$allTags[$tag->getValue()]->getOccurances()
						+ $tag->getOccurances());
				}
			}
		}
		return new HarmoniIterator($allTags);
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
	static function getTagCloudForRepository ( $repository, $system, $viewAction = 'viewRepository', $styles = null) {
		if (!is_object($repository))
			return "";
		
		$repositoryId =$repository->getId();
		$tagManager = Services::getService('Tagging');
		$items = array();
		$assets =$repository->getAssets();
		while($assets->hasNext()) {
			$asset =$assets->next();
			$items[] = TaggedItem::forId($asset->getId(), $system);
		}
		return TagAction::getTagCloudDiv($tagManager->getTagsForItems(
						new HarmoniIterator($items), TAG_SORT_ALFA, 100), 
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
	abstract function getTags () ;
	
	/**
	 * Answer a menu for the tagging system
	 * 
	 * @return string
	 * @access public
	 * @since 11/8/06
	 * @static
	 */
	public static function getTagMenu () {
		$harmoni = Harmoni::instance();
		
		ob_start();
		$tagManager = Services::getService("Tagging");
		if ($currentUserIdString = $tagManager->getCurrentUserIdString()) {
			if ($harmoni->getCurrentAction() == 'tags.user' 
				&& (!RequestContext::value('agent_id') || RequestContext::value('agent_id') == $currentUserIdString)) 
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
			if ($harmoni->getCurrentAction() != 'tags.view') {
				$url = $harmoni->request->quickURL('tags', 'view', 
					array('agent_id' => $tagManager->getCurrentUserIdString(),
					'tag' => RequestContext::value('tag')));
				print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("items tagged '%1' by everyone"))."</a> &nbsp; ";
			}
		
			if ($harmoni->getCurrentAction() == 'tags.viewuser' 
				&& (!RequestContext::value('agent_id') || RequestContext::value('agent_id') == $currentUserIdString)) 
			{
				
				print " | &nbsp; ";
				
				if (!defined('TAGGING_JS_LOADED')) {
					// Add the tagging manager script to the header
					$harmoni = Harmoni::instance();
					$outputHandler =$harmoni->getOutputHandler();
					$outputHandler->setHead($outputHandler->getHead()
						."\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/Tagger.js'></script>"
						."\n\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/quicksort.js'></script>"
						."\n\t\t<link rel='stylesheet' type='text/css' href='".POLYPHONY_PATH."javascript/Tagger.css' />");
					define('TAGGING_JS_LOADED', true);
				}

				
				print "<a onclick=\"TagRenameDialog.run(new Tag('".RequestContext::value('tag')."'), this);\">"._("rename")."</a> &nbsp; ";
				
				
				print "<a onclick=\"";
				print "if (confirm('"._('Are you sure you want to delete all of your instances of this tag?')."')) { ";
				print 	"var req = Harmoni.createRequest(); ";
				print	"var url = Harmoni.quickUrl('tags', 'deleteUser', {'tag': '".RequestContext::value('tag')."'}, 'polyphony-tags'); ";
				print 	"if (req) { ";
				print		"req.onreadystatechange = function () { ";
				print 			"if (req.readyState == 4) { ";
				print				"if (req.status == 200) { ";
				print					"alert('"._('Tag successfully deleted.')."'); ";
				print					"window.location =  Harmoni.quickUrl('tags', 'user', null, 'polyphony-tags'); ";
				print				"} else { ";
				print					"alert('There was a problem retrieving the XML data: ' + req.statusText); ";
				print 				"} ";
				print			"} ";
				print 		"}; ";
				print		"req.open('GET', url, true); ";
				print 		"req.send(null); ";
				print	"} else { ";
				print 		"alert('Error: Unable to execute AJAX request. Please upgrade your browser.'); ";
				print 	"} ";
				print "} ";
				print "\">"._("delete")."</a> &nbsp; ";
				
			} else if ($tagManager->getCurrentUserIdString()) {
				$url = $harmoni->request->quickURL('tags', 'viewuser', 
					array('agent_id' => $tagManager->getCurrentUserIdString(),
					'tag' => RequestContext::value('tag')));
				print "<a href='".$url."'>".str_replace('%1', RequestContext::value('tag'), _("items tagged '%1' by you"))."</a> &nbsp; ";
			}
		}
		
		return ob_get_clean();
	}
	
	/*********************************************************
	 * From PHP.net:
	 * mightymrj at hotmail dot com
	 * 23-May-2006 12:52
	 * Here's a couple functions I made that easily calculate the standard deviation. 
	 * 
	 * @static
	 *********************************************************/
	static function average($array){
		if (!count($array))
			return 0;
		
	 	$sum  = array_sum($array);
		$count = count($array);
	
		return $sum/$count;
	}
	
	/**
	 * The average function can be use independantly but the deviation function uses 
	 * the average function.
	 *
	 * @static
	 */
	static function deviation ($array) {
		if (!count($array))
			return 0;
		
	   $avg = TagAction::average($array);
	   foreach ($array as $value) {
		   $variance[] = pow($value-$avg, 2);
	   }
	   $deviation = sqrt(TagAction::average($variance));
	   return $deviation;
	}
}

?>
