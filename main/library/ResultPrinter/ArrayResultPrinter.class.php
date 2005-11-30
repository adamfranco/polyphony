<?php
/**
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ArrayResultPrinter.class.php,v 1.16 2005/11/30 21:33:05 adamfranco Exp $
 */

/**
 * Print out an Array of items in rows and columns of TEXT_BLOCK widgets 
 * spread over multiple pages.
 *
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ArrayResultPrinter.class.php,v 1.16 2005/11/30 21:33:05 adamfranco Exp $
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
		ArgumentValidator::validate($array, ArrayValidatorRule::getRule());
		ArgumentValidator::validate($numColumns, IntegerValidatorRule::getRule());
		ArgumentValidator::validate($numResultsPerPage, IntegerValidatorRule::getRule());
//		ArgumentValidator::validate($callbackFunction, StringValidatorRule::getRule());
		
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
	 * @param optional string $shouldPrintFunction The name of a function that will
	 *		return a boolean specifying whether or not to filter a given result.
	 *		If null, all results are printed.
	 * @return object Layout A layout containing the results/page links
	 * @access public
	 * @date 8/5/04
	 */
	function &getLayout (& $harmoni, $shouldPrintFunction = NULL) {
		$defaultTextDomain = textdomain("polyphony");
		
		if ($harmoni->request->get('starting_number'))
			$startingNumber = $harmoni->request->get('starting_number');
		else
			$startingNumber = 1;
		
		$yLayout =& new YLayout();
		$layout =& new Container($yLayout,OTHER,1);
		
		
		$endingNumber = $startingNumber+$this->_pageSize-1;
		$numItems = 0;
		$resultLayout =& new Container(new TableLayout($this->_numColumns), OTHER, 1);		
		$shouldPrintEval = $shouldPrintFunction?"\$shouldPrint = ".$shouldPrintFunction."(\$item);":"\$shouldPrint = true;";
		if (count($this->_array)) {
		
			reset($this->_array);
			
			// trash the items before our starting number
			while ($numItems+1 < $startingNumber && $numItems < count($this->_array)) {
				$item =& current($this->_array);
				next($this->_array);
				
				// Ignore this if it should be filtered.
				eval($shouldPrintEval);
				if ($shouldPrint)
					$numItems++;
			}
			
			// print up to $this->_pageSize items
			$pageItems = 0;
			while ($numItems < $endingNumber && $numItems < count($this->_array)) {
				$item =& current($this->_array);
				next($this->_array);
				
				// Only Act if this item isn't to be filtered.
				
				eval($shouldPrintEval);
				if ($shouldPrint) {
					$numItems++;
					$pageItems++;
					
					$itemArray = array (& $item);
					$params = array_merge($itemArray, $this->_callbackParams);
					
					// Add in our starting number to the end so that that it is accessible.
					$params[] = $numItems;
					
					$itemLayout =& call_user_func_array(
						$this->_callbackFunction, $params);
					$resultLayout->add($itemLayout, null, null, CENTER, TOP);
				}
			}
			
			//if we have a partially empty last row, add more empty layouts
			// to better-align the columns
// 			while ($pageItems % $this->_numColumns != 0) {
// 				$currentRow->addComponent(new Content(" &nbsp; "));
// 				$pageItems++;
// 			}
			
			// find the count of items 
			while (true) {
				$item =& current($this->_array);
				if (!$item) break;
				next($this->_array);
				// Ignore this if it should be filtered.
				eval($shouldPrintEval);
				if ($shouldPrint)
					$numItems++;
			}	
		} else {
			$text =& new Block("<ul><li>"._("No items are availible.")."</li></ul>", STANDARD_BLOCK);
			$resultLayout->add($text, null, null, CENTER, CENTER);
		}		
		
		// print out links to skip to more items if the number of Items is greater
		// than the number we display on the page
		if ($numItems > $this->_pageSize) {
			ob_start();
			$numPages = ceil($numItems/$this->_pageSize);
			
			if ($this->_pageSize > 1)
				$currentPage = floor($startingNumber/$this->_pageSize)+1; // add one for 1-based counting
			else
				$currentPage = $startingNumber;
				
			for ($i=1; $i<=$numPages; $i++) {
				if ($i > 0 && ($i-1) % 10 == 0)
					print "<br />";
				print " ";
				if ($i != $currentPage) {
					print "<a href='";
					$url =& $harmoni->request->mkURLWithPassthrough();
					$url->setValue("starting_number", (($i-1)*$this->_pageSize+1));
					print $url->write();
					print "'>";
				}
				print $i;
				if ($i != $currentPage)
					print "</a>";
			}
			
			// Add the links to the page
			$pageLinkBlock =& new Block(ob_get_contents(), STANDARD_BLOCK);
			ob_end_clean();
			$layout->add($pageLinkBlock, "100%", null, LEFT, CENTER);
		}
		
		$layout->add($resultLayout, "100%", null, LEFT, CENTER);
		
		if ($numItems > $this->_pageSize) {
			$layout->add($pageLinkBlock, null, null, CENTER, CENTER);
		}
		
		textdomain($defaultTextDomain);
		return $layout;
	}	
}

?>