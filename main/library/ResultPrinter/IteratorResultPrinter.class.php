<?php
/**
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: IteratorResultPrinter.class.php,v 1.32 2007/10/18 14:24:24 adamfranco Exp $
 */
 
require_once(dirname(__FILE__)."/ResultPrinter.abstract.php");
require_once(HARMONI."GUIManager/StyleProperties/MarginTopSP.class.php");
 
/**
 * Print out an Iterator of items in rows and columns of TEXT_BLOCK widgets 
 * spread over multiple pages.
 *
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: IteratorResultPrinter.class.php,v 1.32 2007/10/18 14:24:24 adamfranco Exp $
 */

class IteratorResultPrinter 
	extends ResultPrinter
{
	
	
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
	function IteratorResultPrinter ($iterator, $numColumns, 
									$numResultsPerPage, $callbackFunction) {
		ArgumentValidator::validate($iterator, new HasMethodsValidatorRule("hasNext", "next"));
		ArgumentValidator::validate($numColumns, new IntegerValidatorRule);
		ArgumentValidator::validate($numResultsPerPage, new IntegerValidatorRule);
		if (is_array($callbackFunction)) {
			ArgumentValidator::validate($callbackFunction[1], new StringValidatorRule);
		} else
			ArgumentValidator::validate($callbackFunction, new StringValidatorRule);
		
		$this->_iterator =$iterator;
		$this->_numColumns = $numColumns;
		$this->_pageSize = $numResultsPerPage;
		$this->_callbackFunction = $callbackFunction;
		
		$this->_callbackParams = array();
		$args = func_get_args();
		for ($i=4; $i<count($args); $i++) {
			$this->_callbackParams[] =$args[$i];
		}
		
		$this->_resultLayout = new TableLayout($this->_numColumns);
	}
	
	/**
	 * Set the direction of component rendering from the default of Left-Right/Top-Bottom.
	 * Allowed values:
	 *		Left-Right/Top-Bottom
	 *		Top-Bottom/Left-Right
	 * 		Right-Left/Top-Bottom
	 *		Top-Bottom/Right-Left
	 *
	 * The other possible directions, listed below, are not implemented due to
	 * lack of utility:
	 *		Left-Right/Bottom-Top
	 *		Bottom-Top/Left-Right
	 *		Right-Left/Bottom-Top
	 *		Bottom-Top/Right-Left
	 * 
	 * @param string $direction
	 * @return void
	 * @access public
	 * @since 8/18/06
	 */
	function setRenderDirection ($direction) {
		$this->_resultLayout->setRenderDirection($direction);
	}
	
	/**
	 * Returns a layout of the Results
	 * 
	 * @param optional string $shouldPrintFunction The name of a function that will
	 *		return a boolean specifying whether or not to filter a given result.
	 *		If null, all results are printed.
	 * @return object Layout A layout containing the results/page links
	 * @access public
	 * @date 8/5/04
	 */
	function getLayout ($shouldPrintFunction = NULL) {
		$defaultTextDomain = textdomain("polyphony");
		
		$startingNumber = $this->getStartingNumber();
		
		$yLayout = new YLayout();
		$container = new Container($yLayout,OTHER,1);
		
		
		$endingNumber = $startingNumber+$this->_pageSize-1;
		$numItems = 0;
		$resultContainer = new Container($this->_resultLayout, OTHER, 1);		
		
		if ($this->_iterator->hasNext()) {
			
			// trash the items before our starting number
			while ($this->_iterator->hasNext() && $numItems+1 < $startingNumber) {
				$item =$this->_iterator->next();
				
				// Ignore this if it should be filtered.
				if (is_null($shouldPrintFunction))
					$shouldPrint = true;
				else
					$shouldPrint = call_user_func_array($shouldPrintFunction, array($item));
					
				if ($shouldPrint)
					$numItems++;
			}
			
			
			// print up to $this->_pageSize items
			$pageItems = 0;
			while ($this->_iterator->hasNext() && $numItems < $endingNumber) {
				$item =$this->_iterator->next();
				
				// Only Act if this item isn't to be filtered.
				if (is_null($shouldPrintFunction))
					$shouldPrint = true;
				else
					$shouldPrint = call_user_func_array($shouldPrintFunction, array($item));
					
				if ($shouldPrint) {
					$numItems++;
					$pageItems++;
					
					$itemArray = array ($item);
					$params = array_merge($itemArray, $this->_callbackParams);
					
					// Add in our starting number to the end so that that it is accessible.
					$params[] = $numItems;
					
					$itemLayout = call_user_func_array(
						$this->_callbackFunction, $params);
					$resultContainer->add($itemLayout, 
						floor(100/$this->_numColumns)."%", 
						"100%", 
						CENTER, TOP);
					
					// If $itemLayout is not unset, since it is an object,
					// it may references to it made in add() will be changed.
					unset($itemLayout);
				}
			}
			
			//if we have a partially empty last row, add more empty layouts
			// to better-align the columns
// 			while ($pageItems % $this->_numColumns != 0) {
// 				$currentRow->addComponent(new Content(" &nbsp; "));
// 				$pageItems++;
// 			}
			
			// find the count of items 
			while ($this->_iterator->hasNext()) {
				$item =$this->_iterator->next();
				
				// Ignore this if it should be filtered.
				if (is_null($shouldPrintFunction))
					$shouldPrint = true;
				else
					$shouldPrint = call_user_func_array($shouldPrintFunction, array($item));
					
				if ($shouldPrint)
					$numItems++;
			}	
		} else {
			$text = new Block("<ul><li>"._("No items are available.")."</li></ul>", STANDARD_BLOCK);
			$resultContainer->add($text, null, null, CENTER, CENTER);
		}		
		
/*********************************************************
 *  Page Links
 * ------------
 * print out links to skip to more items if the number of Items is greater
 * than the number we display on the page
 *********************************************************/
 		if ($linksHTML = $this->getPageLinks($startingNumber, $numItems)) {
			
			// Add the links to the page
			$pageLinkBlock = new Block($linksHTML, BACKGROUND_BLOCK);
			$container->add($pageLinkBlock, "100%", null, CENTER, CENTER);
			
			$styleCollection = new StyleCollection("*.result_page_links", "result_page_links", "Result Page Links", "Links to other pages of results.");
			$styleCollection->addSP(new MarginTopSP("10px"));
			$pageLinkBlock->addStyle($styleCollection);
		}
		
		$container->add($resultContainer, "100%", null, LEFT, CENTER);
		
		if ($numItems > $this->_pageSize) {
			$container->add($pageLinkBlock, null, null, CENTER, CENTER);
		}
		
		
		textdomain($defaultTextDomain);
		return $container;
	}	
	
}

?>
