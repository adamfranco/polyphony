<?php

/**
 * Print out an Array of items in rows and columns of TEXT_BLOCK widgets 
 * spread over multiple pages.
 * 
 * @package polyphony.resultprinter
 * @version $Id: ArrayResultPrinter.class.php,v 1.1 2004/08/06 21:52:40 adamfranco Exp $
 * @date $Date: 2004/08/06 21:52:40 $
 * @copyright 2004 Middlebury College
 */

class ArrayResultPrinter {
	
	
	/**
	 * Constructor
	 * 
	 * @param object $iterator The iterator to print.
	 * @param integer $numColumns The number of result columns to print on each page.
	 * @param integer $numResultsPerPage The number of iterator items to print on a page.
	 * @param string $callbackFunction The name of the function that will be called to
	 *			to print each result.
	 * @param optional mixed $callbackArgs Any additional arguements will be stored
	 *			and passed on to the callback function.
	 * @access public
	 * @date 8/5/04
	 */
	function ArrayResultPrinter (& $array, $numColumns, 
									$numResultsPerPage, $callbackFunction) {
		ArgumentValidator::validate($array, new ArrayValidatorRule);
		ArgumentValidator::validate($numColumns, new IntegerValidatorRule);
		ArgumentValidator::validate($numResultsPerPage, new IntegerValidatorRule);
		ArgumentValidator::validate($callbackFunction, new StringValidatorRule);
		
		$this->_array =& $array;
		$this->_numColumns =& $numColumns;
		$this->_pageSize =& $numResultsPerPage;
		$this->_callbackFunction =& $callbackFunction;
		
		$this->_callbackParams = array();
		$args =& func_get_args();
		for ($i=4; $i<count($args); $i++) {
			$this->_callbackParams[] =& $args[$i];
		}
	}
	
	
	
	/**
	 * Returns a layout of the Results
	 * 
	 * @param object Harmoni The Harmoni object containing context data.
	 * @return object Layout A layout containing the results/page links
	 * @access public
	 * @date 8/5/04
	 */
	function & getLayout (& $harmoni) {
		$startingNumber = ($_REQUEST['starting_number'])?$_REQUEST['starting_number']:1;
		
		$layout =& new RowLayout;
		
		
		$endingNumber = $startingNumber+$this->_pageSize-1;
		$numItems = 0;
		$resultLayout =& new RowLayout();
		
		if (count($this->_array)) {
		
			reset($this->_array);
			
			// trash the items before our starting number
			while ($numItems+1 < $startingNumber && $numItems < count($this->_array)) {
				print "Skipping.";
				next($this->_array);
				$numItems++;
			}
			
			// print up to $this->_pageSize items
			$pageItems = 0;
			while ($numItems < $endingNumber && $numItems < count($this->_array)) {
				$item =& current($this->_array);
				next($this->_array);
				$numItems++;
				$pageItems++;
				
				// Table Rows subtract 1 since we are counting 1-based
				if (($pageItems-1) % $this->_numColumns == 0) {
					$currentRow =& new ColumnLayout;
					$resultLayout->addComponent($currentRow);
				}
				
				$itemArray = array (& $item);
				$params = array_merge($itemArray, $this->_callbackParams);
				$itemLayout =& call_user_func_array($this->_callbackFunction, $params);
				$currentRow->addComponent($itemLayout);
			}
			
			//if we have a partially empty last row, add more empty layouts
			// to better-align the columns
// 			while ($pageItems % $this->_numColumns != 0) {
// 				$currentRow->addComponent(new Content(" &nbsp; "));
// 				$pageItems++;
// 			}
			
			// find the count of items 
			while (next($this->_array)) {
				$numItems++;
			}	
		} else {
			$resultLayout->addComponent(new Content(_("No <em>Items</em> are availible.")));
		}		
		
		// print out links to skip to more items if the number of Items is greater
		// than the number we display on the page
		ob_start();
		if ($numItems > $this->_pageSize) {
			$numPages = ceil($numItems/$this->_pageSize);
			$currentPage = floor($startingNumber/$this->_pageSize)+1; // add one for 1-based counting
			for ($i=1; $i<=$numPages; $i++) {
				if ($i > 0 && ($i+1) % 10 == 0)
					print "<br />";
				print " ";
				if ($i != $currentPage)
					print "<a href='".MYURL."/".implode("/", $harmoni->pathInfoParts)."?starting_number=".(($i-1)*$this->_pageSize+1)."'>";
				print $i;
				if ($i != $currentPage)
					print "</a>";
			}
			
			// Add the links to the page
			$pageLinkBlock =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
			$pageLinkBlock->addComponent(new Content(ob_get_contents()));
			ob_end_clean();
			$layout->addComponent($pageLinkBlock, MIDDLE, CENTER);
		}
		
		$layout->addComponent($resultLayout);
		
		if ($numItems > $this->_pageSize) {
			$layout->addComponent($pageLinkBlock, MIDDLE, CENTER);
		}
		
		return $layout;
	}	
}

?>