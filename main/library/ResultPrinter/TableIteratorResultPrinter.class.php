<?php

/**
 * Print out an Iterator of items in a table spread over multiple pages.
 * 
 * @package polyphony.resultprinter
 * @version $Id: TableIteratorResultPrinter.class.php,v 1.1 2004/08/06 14:56:09 adamfranco Exp $
 * @date $Date: 2004/08/06 14:56:09 $
 * @copyright 2004 Middlebury College
 */

class TableIteratorResultPrinter {
	
	
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
	function TableIteratorResultPrinter (& $iterator, $numColumns, 
									$numResultsPerPage, $callbackFunction) {
		ArgumentValidator::validate($iterator, new HasMethodsValidatorRule("hasNext", "next"));
		ArgumentValidator::validate($numColumns, new IntegerValidatorRule);
		ArgumentValidator::validate($numResultsPerPage, new IntegerValidatorRule);
		ArgumentValidator::validate($callbackFunction, new StringValidatorRule);
		
		$this->_iterator =& $iterator;
		$this->_numColumns =& $numColumns;
		$this->_pageSize =& $numResultsPerPage;
		$this->_callbackFunction =& $callbackFunction;
		
		$this->_callbackParams = array();
		$args =& func_get_args();
		foreach (array_keys($args) as $key) {
			$this->_callbackParams[] =& $args[$key];
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
		
		ob_start();
		$endingNumber = $startingNumber+$this->_pageSize-1;
		$numItems = 0;
		print "\n<table cellspacing='10px'>";
		print  "\n<tr>";
		if ($this->_iterator->hasNext()) {
			
			// trash the items before our starting number
			while ($this->_iterator->hasNext() && $numItems+1 < $startingNumber) {
				$item =& $this->_iterator->next();
				$numItems++;
			}
			
			
			// print up to $this->_pageSize items
			$pageItems = 0;
			while ($this->_iterator->hasNext() && $numItems < $endingNumber) {
				$item =& $this->_iterator->next();
				$numItems++;
				$pageItems++;
				
				// Table Rows subtract 1 since we are counting 1-based
				if ($pageItems > 1 && ($pageItems-1) % $this->_numColumns == 0) 
					print  "\n</tr>\n<tr>";
				
				print "\n<td style='border: 1px solid;  padding: 5px;'>";
				
				$callback = $this->_callbackFunction;
				$callback($item, $this->_callbackParams);
				
				
				print  "\n</td>";
			}
			
			// find the count of items 
			while ($this->_iterator->hasNext()) {
				$item =& $this->_iterator->next();
				$numItems++;
			}	
		} else {
			print  "\n\t<td>"._("No <em>Items</em> are availible.")."</td>";
		}
		print  "\n</tr>";
		print  "\n</table>";
		
		$resultBlock =& new SingleContentLayout(TEXT_BLOCK_WIDGET, 2);
		$resultBlock->addComponent(new Content(ob_get_contents()));
		ob_end_clean();
		
		
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
		
		$layout->addComponent($resultBlock);
		
		if ($numItems > $this->_pageSize) {
			$layout->addComponent($pageLinkBlock, MIDDLE, CENTER);
		}
		
		return $layout;
	}	
}

?>