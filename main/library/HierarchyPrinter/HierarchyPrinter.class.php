<?php

/**
 * This class will print an expandible, view of a hierarchy
 * 
 * @package polyphony.hierarchyPrinter
 * @version $Id: HierarchyPrinter.class.php,v 1.3 2004/11/17 21:39:44 adamfranco Exp $
 * @date $Date: 2004/11/17 21:39:44 $
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
	 * @date 11/8/04
	 */
	function printNode (& $node, & $harmoni,
								$startingPathInfoKey,
								$printFunction, 
								$hasChildrenFunction, 
								$getChildrenFunction ) 
	{
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
		<?		
			// The child nodes are already expanded for this node. 
			// Show option to collapse the list.		
			if (in_array($nodeId->getIdString(), $expandedNodes)) {
				$nodesToRemove = array($nodeId->getIdString());
				$newPathInfo = array_merge($environmentInfo, array_diff($expandedNodes,
																		$nodesToRemove)); 
				print "<a style='text-decoration: none;' href='";
				print MYURL."/".implode("/", $newPathInfo)."/";
				print "'>-</a>";
			
			// The node is not already expanded.  Show option to expand.	
			} else { 
				$newPathInfo = array_merge($environmentInfo, $expandedNodes); 
				print "<a style='text-decoration: none;' href='";
				print MYURL."/".implode("/", $newPathInfo)."/".$nodeId->getIdString()."/";
				print "'>+</a>";
			}
			print "\n\t\t</div>";
			
		// The node has no children.  Do not show options to expand/collapse.
		} else 
			print "\n\t\t<div style='width: 15px;'>&nbsp;</div>";
		
		print "\n\t</td><td valign='top'>\n\t\t";
		$printFunction( $node );
		print "\n\t</td></tr>\n</table>";
		
		
		// Recursively print the children.
		
		if (in_array($nodeId->getIdString(), $expandedNodes)) {
			?>

<div style='
	margin-left: 13px; 
	margin-right: 0px; 
	margin-top:0px; 
	padding-left: 10px;
	border-left: 1px solid #000;
'>
		<?
			$children =& $getChildrenFunction($node);
			foreach (array_keys($children) as $key) {
				HierarchyPrinter::printNode( $children[$key],
															$harmoni,
															$startingPathInfoKey,
															$printFunction, 
															$hasChildrenFunction, 
															$getChildrenFunction );
			}
			
			print "\n</div>";
		}
	}
	
	
	
}

?>