<?php
/**
 * @package polyphony.library.HierarchyPrinter
 */

/**
 * This class will print an expandible, view of a hierarchy
 * 
 * @package polyphony.library.HierarchyPrinter
 * @version $Id: HierarchyPrinter.class.php,v 1.7 2005/02/04 23:06:11 adamfranco Exp $
 * @since $Date: 2005/02/04 23:06:11 $
 * @copyright 2004 Middlebury College
 */

class HierarchyPrinter {
	
	/**
	 * Print a node and the expanded children
	 * 
	 * @param object $node
	 * @param string $printFunction Function that prints the current node's info.
	 * @param string $hasChildrenFunction Function that returns true if the node
	 *		has children.
	 * @param string $getChildrenFunction Function that returns an array of the
	 *		children of this node.
	 * @return void
	 * @access public
	 * @since 11/8/04
	 */
	function printNode (& $node, & $harmoni,
								$startingPathInfoKey,
								$printFunction, 
								$hasChildrenFunction, 
								$getChildrenFunction, 
								$color = NULL ) 
	{
		
		print "\n<div";
		if ($color !== NULL) {
			print " style='";
//			print " border: 1px solid #000;";
			print " padding-top: 5px; padding-left: 5px; padding-bottom: 10px;";
			print " background-color: ".$color->getHTMLcolor().";'";
		}
		print ">";
		
		// Get a string of our nodeIds
		$nodeId =& $node->getId();
		
		// Build a variable to pass around our get terms when expanding
		if (count($_GET)) {
				$get = "?";
				foreach ($_GET as $key => $val)
					$get .= "&".urlencode($key)."=".urlencode($val);
		}
		
		// Break the path info into parts for the enviroment and parts that
		// designate which nodes to expand.
		$environmentInfo = array();
		$expandedNodes = array();
		
		for ($i=0; $i<count($harmoni->pathInfoParts); $i++) {
			// If the index equals or is after our starting key
			// it designates an expanded nodeId.
			if ($i >= $startingPathInfoKey)
				$expandedNodes[] = $harmoni->pathInfoParts[$i];
			else	
				$environmentInfo[] = $harmoni->pathInfoParts[$i];
		}
		
		print "\n\n<table>\n\t<tr><td valign='top'>";
		// Print The node
		if ($hasChildrenFunction($node)) {
		?>

<div style='
	border: 1px solid #000; 
	width: 15px; 
	height: 15px;
	text-align: center;
	text-decoration: none;
	font-weight: bold;
'>
		<?php
/**
 * @package polyphony.library.
 */		
			// The child nodes are already expanded for this node. 
			// Show option to collapse the list.		
			if (in_array($nodeId->getIdString(), $expandedNodes)) {
				$nodesToRemove = array($nodeId->getIdString());
				$newPathInfo = array_merge($environmentInfo, array_diff($expandedNodes,
																		$nodesToRemove)); 
				print "<a style='text-decoration: none;' href='";
				print htmlentities(MYURL."/".implode("/", $newPathInfo)."/".$get);
				print "'>-</a>";
				
				$expanded = TRUE;
			
			// The node is not already expanded.  Show option to expand.	
			} else { 
				$newPathInfo = array_merge($environmentInfo, $expandedNodes); 
				print "<a style='text-decoration: none;' href='";
				print htmlentities(MYURL."/".implode("/", $newPathInfo)."/".$nodeId->getIdString()."/".$get);
				print "'>+</a>";
				
				$expanded = FALSE;
			}
			print "\n\t\t</div>";
			
			// Make a vertical line to connect to the line in front of the children
			if ($expanded) {
				print <<<END
		<div style='
				height: 100%; 
				border-left: 1px #000 solid;
				margin-left: 10px; 
				margin-right: 0px; 
				margin-top:0px; 
		'>&nbsp;</div>
END;
			}
			
		// The node has no children.  Do not show options to expand/collapse.
		} else 
			print "\n\t\t<div style='width: 15px;'>&nbsp;</div>";
		
		print "\n\t</td><td valign='top'>\n\t\t";
		$printFunction( $node );
		print "\n\t</td></tr>\n</table>";
		
		
		// Recursively print the children.
		
		if (in_array($nodeId->getIdString(), $expandedNodes)) {
			print <<<END

<div style='
	margin-left: 13px; 
	margin-right: 0px; 
	margin-top:0px; 
	padding-left: 10px;
	border-left: 1px solid #000;
END;
			print "'>";
			
			if ($color !== NULL) {
				$childColor =& $color->clone();
				$childColor->darken(20);
			} else {
				$childColor = NULL;
			}
			
			$children =& $getChildrenFunction($node);
			foreach (array_keys($children) as $key) {
				HierarchyPrinter::printNode( $children[$key],
															$harmoni,
															$startingPathInfoKey,
															$printFunction, 
															$hasChildrenFunction, 
															$getChildrenFunction,
															$childColor );
			}
			
			print "\n</div>";
		}
		
		print "\n</div>";
	}
	
	
	
}

?>