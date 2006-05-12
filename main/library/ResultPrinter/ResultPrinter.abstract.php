<?php
/**
 * @since 12/7/05
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ResultPrinter.abstract.php,v 1.2 2006/05/12 18:29:40 adamfranco Exp $
 */ 

/**
 * This abstract class provides common methods for child classes
 * 
 * @since 12/7/05
 * @package polyphony.resultprinter
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: ResultPrinter.abstract.php,v 1.2 2006/05/12 18:29:40 adamfranco Exp $
 */
class ResultPrinter {

	/**
	 * Answer the number of the first asset on our current page
	 * 
	 * @return integer 1 through the total number of items
	 * @access public
	 * @since 5/11/06
	 */
	function getStartingNumber () {
		if (RequestContext::value('starting_number'))
			return RequestContext::value('starting_number');
		else
			return 1;
	}
	
	/**
	 * Answer the parameter name used to pass the starting number 
	 * (for inclusion in other urls).
	 * 
	 * @return string
	 * @access public
	 * @since 5/11/06
	 */
	function startingNumberParam () {
		return RequestContext::name('starting_number');
	}
		
	/**
	 * Return a string containing HTML links to other pages of the iterator.
	 * if all items fit on one page, an empty string will be returned.
	 * 
	 * @param integer $startingNumber The item number to start with.
	 * @param integer $numItems The total number of Items.
	 * @return string
	 * @access public
	 * @since 12/7/05
	 */
	function getPageLinks ($startingNumber, $numItems) {
		if ($numItems > $this->_pageSize) {
			$harmoni =& Harmoni::instance();
			
			ob_start();
			print "\n<table width='100%'>";
			print "\n\t<tr>";
			print "\n\t\t<td>";
			$numPages = ceil($numItems/$this->_pageSize);
			
			if ($this->_pageSize > 1)
				$currentPage = floor($startingNumber/$this->_pageSize)+1; // add one for 1-based counting
			else
				$currentPage = $startingNumber;
			
			
			$pagesAround = 2;
			
			$firstPage = $currentPage - $pagesAround;
			if ($firstPage <= 1)
				$firstPage = 1;
			else if ($firstPage > ($numPages - (2 * $pagesAround))) {
				$firstPage = $numPages - (2 * $pagesAround);
				if ($firstPage <= 1)
					$firstPage = 1;
			}
			
			$url =& $harmoni->request->mkURLWithPassthrough();
			
			$lastPage = $firstPage + (2 * $pagesAround);
			if ($lastPage > $numPages)
				$lastPage = $numPages;
			
			if ($currentPage == 1) {
//				print "&lt;&lt; \n";
			} else {
				$url->setValue("starting_number", 1);
// 				print "<a href='";
// 				print $url->write();
// 				print "'>&lt;&lt;</a> \n";
				
				if ($firstPage > 1) {
					print "<a href='";
					print $url->write();
					print "'>1</a> ";
					if ($firstPage > 2)
						print " ... \n";
				}
			}
			
			for ($i = $firstPage; $i <= $lastPage; $i++) {
				print " ";
				if ($i != $currentPage) {
					$url->setValue("starting_number", (($i-1)*$this->_pageSize+1));
					
					print "<a href='";
					print $url->write();
					print "'>";
				}
				print $i;
				if ($i != $currentPage)
					print "</a>\n";
			}
			
			if ($currentPage == $numPages) {
//				print " &gt;&gt;\n";
			} else {
				$url->setValue("starting_number", (($numPages-1)*$this->_pageSize+1));
				
				if ($lastPage < $numPages) {
					if ($lastPage < $numPages - 1)
						print " ... ";
					print "<a href='";
					print $url->write();
					print "'>".$numPages."</a> \n";
				}
				
// 				print " <a href='";
// 				print $url->write();
// 				print "'>&gt;&gt;</a>\n";
			}
			
			print "\n\t\t</td>";
			if ($numPages > 2*$pagesAround + 3) {
				print "\n\t\t<td style='text-align: center'>";
			} else {
				print "\n\t\t<td style='text-align: right'>";
			}
			print "(";
			print $startingNumber;
			print "-";
			print ($startingNumber + $this->_pageSize - 1);
			print " "._("of")." ".$numItems." "._("items").")";
			
			if ($numPages > 2*$pagesAround + 3) {
				print "\n\t\t</td>\n\t\t<td style='text-align: right'>\n\t\t\t"._("Go to page:")." \n\t\t\t<select onchange='Javascript:jumpToPage(this);'>\n";
				for ($i = 1; $i <= $numPages; $i++) {
					$value = ($i-1)*$this->_pageSize + 1;
					print "\t<option value='".$value."'";
					if ($i == $currentPage)
						print " selected='selected'";
					print ">".$i."</option>\n";
				}
				print "</select>\n";
				
				$url->setValue("starting_number", "__________");
				$urlString = str_replace("__________", "' + inputField.value + '",
								str_replace('&amp;', '&', $url->write()));
				print "\n<script type='text/javascript'>\n//<![CDATA[";
				print "\n	function jumpToPage(inputField) {";
				print "\n		window.location = '".$urlString."'";
				print "\n	}";
				print "\n//]]>\n</script>\n";
			}
			print "\n\t\t</td>";
			print "\n\t</tr>";
			print "\n</table>";
			
			$html = ob_get_contents();
			ob_end_clean();
			return $html;
		} else {
			return "";
		}
	}
	
}

?>