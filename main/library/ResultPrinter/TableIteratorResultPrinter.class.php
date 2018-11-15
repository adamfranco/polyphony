<?php
/**
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TableIteratorResultPrinter.class.php,v 1.21 2008/04/03 12:34:50 adamfranco Exp $
 */
 
require_once(dirname(__FILE__)."/ResultPrinter.abstract.php");
 
/**
 * Print out an Iterator of items in rows and columns of TEXT_BLOCK widgets 
 * spread over multiple pages.
 *
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: TableIteratorResultPrinter.class.php,v 1.21 2008/04/03 12:34:50 adamfranco Exp $
 */

class TableIteratorResultPrinter 
	extends ResultPrinter
{
	
	/**
	 * Constructor
	 * 
	 * @param object $iterator The iterator to print.
	 * @param string $headRow The heading row.
	 * @param integer $numResultsPerPage The number of iterator items to print on a page.
	 * @param string $callbackFunction The name of the function that will be called to
	 *			to print each result.
	 * @param optional mixed $callbackArgs Any additional arguements will be stored
	 *			and passed on to the callback function.
	 * @access public
	 * @date 8/5/04
	 */
	function __construct ($iterator, $headRow, $numResultsPerPage, 
		$callbackFunction, $tableBorder = 0) 
	{
		ArgumentValidator::validate($iterator, new HasMethodsValidatorRule("hasNext", "next"));
		ArgumentValidator::validate($headRow, new StringValidatorRule);
		ArgumentValidator::validate($numResultsPerPage, new IntegerValidatorRule);
		if (is_array($callbackFunction))
			ArgumentValidator::validate($callbackFunction[1], new StringValidatorRule);
		else
			ArgumentValidator::validate($callbackFunction, new StringValidatorRule);
		ArgumentValidator::validate($tableBorder, new IntegerValidatorRule);
		
		$this->_iterator =$iterator;
		$this->_headRow =$headRow;
		
		preg_match_all("/<th>|<td>/", $headRow, $matches);
		$this->_numColumns = count($matches[0]);
		
		$this->_tableBorder = $tableBorder;
		
		$this->_pageSize =$numResultsPerPage;
		$this->_callbackFunction =$callbackFunction;
		
		$this->_callbackParams = array();
		$args = func_get_args();
		for ($i=4; $i<count($args); $i++) {
			$this->_callbackParams[] =$args[$i];
		}
	}
	
	/**
	 * Returns a layout of the Results
	 * 
	 * @param object Harmoni $harmoni The Harmoni object containing context data.
	 * @param optional string $shouldPrintFunction The name of a function that will
	 *		return a boolean specifying whether or not to filter a given result.
	 *		If null, all results are printed.
	 * @return string A table string.
	 * @access public
	 * @date 8/5/04
	 */
	function getTable ($shouldPrintFunction = NULL) {
		$harmoni = Harmoni::instance();
		
		$defaultTextDomain = textdomain("polyphony");
		
		$startingNumber = $this->getStartingNumber();
				
		// print out all of the rows.
		
		$endingNumber = $startingNumber+$this->_pageSize-1;
		$numItems = 0;
		
		if ($this->_iterator->hasNext()) {
			ob_start();
			
			// trash the items before our starting number
			while ($this->_iterator->hasNext() && $numItems+1 < $startingNumber) {
				if (!is_null($shouldPrintFunction)) {
					$this->_iterator->skipNext();
					$numItems++;
				} else {
					$item =$this->_iterator->next();
					// Ignore this if it should be filtered.
					if (is_null($shouldPrintFunction))
						$shouldPrint = true;
					else
						$shouldPrint = call_user_func_array($shouldPrintFunction, array($item));
						
					if ($shouldPrint)
						$numItems++;
				}
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
					
					print call_user_func_array($this->_callbackFunction, $params);
				}
			}
			
			// find the count of items 
			if (is_null($shouldPrintFunction)) {
				$numItems = $this->_iterator->count();
			} else {
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
			}
			
			$rows = ob_get_clean();
		} else {
			$rows = "\n\t<tr>\n\t\t<td colspan='".$this->_numColumns."'>"._("No items are available.")."</td>\n\t</tr>";
		}		
		
/*********************************************************
 *  Page Links
 * ------------
 * print out links to skip to more items if the number of Items is greater
 * than the number we display on the page
 *********************************************************/
 		if ($linksHTML = $this->getPageLinks($startingNumber, $numItems)) {
			$linksRow = "\n\t<tr>\n\t\t<td colspan='".$this->_numColumns."' style='text-align: right'>\n\t\t\t".$linksHTML."\n\t\t</td>\n\t</tr>";
		} else {
			$linksRow = '';
		}
		
		ob_start();
		print "\n<table border='".$this->_tableBorder."'>";
		print $linksRow;
		print $this->_headRow;
		print $rows;
		print $linksRow;
		print "\n</table>";		
		
		$this->numItemsPrinted = $numItems;
		
		textdomain($defaultTextDomain);
		return ob_get_clean();
	}	
	
}