<?php
/**
 * @package polyphony.library.HierarchyPrinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HierarchyPrinter.class.php,v 1.13 2007/09/04 20:28:00 adamfranco Exp $
 */

/**
 * This class will print an expandible, view of a hierarchy
 *
 * @package polyphony.library.HierarchyPrinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: HierarchyPrinter.class.php,v 1.13 2007/09/04 20:28:00 adamfranco Exp $
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
	function printNode ($node, $harmoni,
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
		$nodeId =$node->getId();
		
		// Break the path info into parts for the enviroment and parts that
		// designate which nodes to expand.
		$expandedNodes = explode("!", $harmoni->request->get('expanded_nodes'));
		
		print "\n\n<table>\n\t<tr><td valign='top'>";
		// Print The node
		eval('$hasChildren = '.$hasChildrenFunction.'($node);');
		if ($hasChildren) {
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
				$newExpandedNodes = array_diff($expandedNodes, 
					array($nodeId->getIdString())); 	
				$symbol = '-';
				$expanded = TRUE;
			
			// The node is not already expanded.  Show option to expand.	
			} else { 
				$newExpandedNodes = array_merge($expandedNodes, 
					array($nodeId->getIdString())); 
				$symbol = '+';
				$expanded = FALSE;
			}
			
			$url =$harmoni->request->mkURLWithPassthrough();
			$url->setValue('expanded_nodes', implode('!', $newExpandedNodes));
			
			print "<a style='text-decoration: none;' href='".$url->write()."'>".$symbol."</a>";
			
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
		} else {
			print "\n\t\t<div style='width: 15px;'>&nbsp;</div>";
		}
		
		print "\n\t</td><td valign='top'>\n\t\t";
		eval($printFunction.'( $node );');
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
				$childColor =$color->replicate();
				$childColor->darken(20);
			} else {
				$childColor = NULL;
			}
			
			eval('$children = '.$getChildrenFunction.'($node);');
			foreach (array_keys($children) as $key) {
				HierarchyPrinter::printNode( $children[$key],														$harmoni, $startingPathInfoKey, $printFunction, $hasChildrenFunction,	$getChildrenFunction, $childColor );
			}		
			print "\n</div>";
		}
		
		print "\n</div>";
	}
	
	
	
}

?>