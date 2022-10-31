<?php
/**
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ArrayResultPrinter.class.php,v 1.29 2008/04/03 12:34:50 adamfranco Exp $
 */

require_once(dirname(__FILE__)."/ResultPrinter.abstract.php");

/**
 * Print out an Array of items in rows and columns of TEXT_BLOCK widgets 
 * spread over multiple pages.
 *
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ArrayResultPrinter.class.php,v 1.29 2008/04/03 12:34:50 adamfranco Exp $
 */

class ArrayResultPrinter 
	extends ResultPrinter
{
	
	
	/**
	 * Constructor
	 * 
	 * @param object $iterator The iterator to print.
	 * @param integer $numColumns The number of result columns to print on each page.
	 * @param integer $numResultsPerPage The number of iterator items to print on a page.
	 * @param mixed $callbackFunction The name of the function that will be called to
	 *			to print each result. If null, the first parameter is assumed to be an
	 *			array of gui components that can be rendered without further processing.
	 * @param optional mixed $callbackArgs Any additional arguements will be stored
	 *			and passed on to the callback function.
	 * @access public
	 * @date 8/5/04
	 */
	function __construct ($array, $numColumns, 
									$numResultsPerPage, $callbackFunction = NULL) {
		ArgumentValidator::validate($array, ArrayValidatorRule::getRule());
		ArgumentValidator::validate($numColumns, IntegerValidatorRule::getRule());
		ArgumentValidator::validate($numResultsPerPage, IntegerValidatorRule::getRule());
//		ArgumentValidator::validate($callbackFunction, StringValidatorRule::getRule());
		
		if (is_null($callbackFunction))
			ArgumentValidator::validate($array, ArrayValidatorRuleWithRule::getRule(
				ExtendsValidatorRule::getRule("ComponentInterface")));
		
		
		$this->_array = $array;
		$this->_numColumns = $numColumns;
		$this->_pageSize = $numResultsPerPage;
		$this->_callbackFunction = $callbackFunction;
		
		$this->_callbackParams = array();
		$args = func_get_args();
		for ($i=4; $i<count($args); $i++) {
			$this->_callbackParams[] =$args[$i];
		}
		
		$this->_resultLayout = new TableLayout($this->_numColumns);
		$this->_resultLayout->printEmptyCells = false;
		
		$this->_linksStyleCollection = new StyleCollection(
			"*.result_page_links", 
			"result_page_links", 
			"Result Page Links", 
			"Links to other pages of results.");
// 		$this->_linksStyleCollection->addSP(new MarginTopSP("10px"));
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
	 * Set the tdStyles
	 * 
	 * @param string $tdStyles
	 * @return void
	 * @access public
	 * @since 9/18/06
	 */
	function setTdStyles ($tdStyles) {
		$this->_resultLayout->setTdStyles($tdStyles);
	}
	
	/**
	 * Add style properties to the links
	 * 
	 * @param object StyleProperty $styleProperty
	 * @return void
	 * @access public
	 * @since 9/19/06
	 */
	function addLinksStyleProperty ($styleProperty) {
		$this->_linksStyleCollection->addSP($styleProperty);
	}
	
	/**
	 * Returns a layout of the Results
	 * 
	 * @param optional mixed $shouldPrintFunction A callback function that will
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
		
		if (count($this->_array)) {
		
			reset($this->_array);
			
			// trash the items before our starting number
			while ($numItems+1 < $startingNumber && $numItems < count($this->_array) && current($this->_array) !== false) {
				$item = current($this->_array);
				next($this->_array);
				
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
			while ($numItems < $endingNumber && $numItems < count($this->_array) && current($this->_array) !== false) {
				$item = current($this->_array);
				next($this->_array);
				
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
					
					// If the callback function is null, assume that we have an
					// array of GUI components that can be used directly
					if (is_null($this->_callbackFunction)) {
						$itemComponent = $item;					
					} else {
						// The following call returns an object, but notices are given
						// if an '&' is used.
						$itemComponent = call_user_func_array(
							$this->_callbackFunction, $params);
					}
					$resultContainer->add($itemComponent, null, null, CENTER, TOP);
					
					// If $itemComponent is not unset, since it is an object,
					// it may references to it made in add() will be changed.
					unset($itemComponent);
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
				$item = current($this->_array);
				if (!$item) break;
				next($this->_array);
				// Ignore this if it should be filtered.
				if (!is_object($item))
					var_dump($item);
				
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
			$pageLinkBlock = new UnstyledBlock($linksHTML, BLANK);
			$container->add($pageLinkBlock, "100%", null, CENTER, CENTER);
			
			$pageLinkBlock->addStyle($this->_linksStyleCollection);
		}
		
		$container->add($resultContainer, "100%", null, LEFT, CENTER);
		
		if ($numItems > $this->_pageSize) {
			$container->add($pageLinkBlock, null, null, CENTER, CENTER);
		}
		
		$this->numItemsPrinted = $numItems;
		
		textdomain($defaultTextDomain);
		return $container;
	}
}

?>