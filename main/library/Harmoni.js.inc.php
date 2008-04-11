<?php
/**
 * This file includes a number of static javascript files and writes a Harmoni
 * javascript class that dynamically adapts to changes in the configuration
 * of the RequestContext url writer. 
 *
 * @since 11/29/06
 * @package polyphony.javascript
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: Harmoni.js.inc.php,v 1.5 2008/04/11 21:50:24 adamfranco Exp $
 */ 

	// Additional Files
	print "\n\t\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/common.js'></script>";
	print "\n\t\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/Panel.js'></script>";
	print "\n\t\t\t<link rel='stylesheet' type='text/css' href='".POLYPHONY_PATH."javascript/Panel.css' />";
	print "\n\t\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/Tagger.js'></script>";
	print "\n\t\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/AuthZViewer.js'></script>";
	print "\n\t\t\t<script type='text/javascript' src='".POLYPHONY_PATH."javascript/quicksort.js'></script>";

?>

			
			<script type='text/javascript'>
			// <![CDATA[
			
			/**
			 * A Class for harmoni related static methods.
			 * 
			 * @access public
			 * @since 11/10/06
			 */
			function Harmoni () {
				alert('Error: Harmoni is a static class. Do not instantiate');
			}
			
			/**
			 * Create an XML HTTP request object
			 * 
			 * @return object
			 * @access public
			 * @since 11/10/06
			 */
			Harmoni.createRequest = function () {		
				// branch for native XMLHttpRequest object (Mozilla, Safari, etc)
				if (window.XMLHttpRequest)
					var req = new XMLHttpRequest();
					
				// branch for IE/Windows ActiveX version
				else if (window.ActiveXObject)
					var req = new ActiveXObject("Microsoft.XMLHTTP");
				
				return req;
			}

			/**
			 * Answer a harmoni URL
			 * 
			 * @param string action
			 * @param optional array parameters
			 * @return string
			 * @access public
			 * @since 11/10/06
			 */
			Harmoni.quickUrl = function (module, action, parameters, namespace) {
				<?php 
					$harmoni = Harmoni::instance();
					$harmoni->request->startNameSpace(null);
					$url = $harmoni->request->quickURL('xxMODULExx', 'xxACTIONxx', 
						array('xxKEY1xx'=> 'xxVALUE1xx', 'xxKEY2xx' => 'xxVALUE2xx'));
					print "\n\t\t\t\tvar normalUrl = '".$url."';";
					$harmoni->request->endNameSpace();
					
					$harmoni->request->startNameSpace('xxnamespacexx');
					$url = $harmoni->request->quickURL('xxMODULExx', 'xxACTIONxx', 
						array('xxKEY1xx'=> 'xxVALUE1xx', 'xxKEY2xx' => 'xxVALUE2xx'));
					$harmoni->request->endNameSpace();
					print "\n\t\t\t\tvar namespacedUrl = '".$url."';";
				?>
				var debug = '';
				if (namespace) {
					var url = namespacedUrl.replaceAll(/xxnamespacexx/, namespace);
					debug += "\nnamespaced";
				} else
					var url = normalUrl;
				
				url = url.urlDecodeAmpersands();
				
				var result = url.match(/^.+xxACTIONxx/);
				var baseUrl = result[0];
				var result = url.match(/xxVALUE1xx(.+)xxKEY2xx/);
				var parameterSeparator = result[1];
				var result = url.match(/xxKEY1xx(.+)xxVALUE1xx/);
				var keyValueSeparator = result[1];
				
				var newUrl = baseUrl.replace(/xxMODULExx/, module);
				newUrl = newUrl.replace(/xxACTIONxx/, action);
				
				if (parameters) {
					for (var key in parameters) {
						newUrl += parameterSeparator + key + keyValueSeparator + parameters[key];
					}
				}
				
				return newUrl;
			}
			
			/**
			 * Answer a namespaced form fieldname
			 * 
			 * @param string action
			 * @param optional array parameters
			 * @return string
			 * @access public
			 * @since 7/12/07
			 */
			Harmoni.fieldName = function (name, namespace) {
				<?php 
					$harmoni = Harmoni::instance();
					$fieldName = RequestContext::name('xxFIELDNAMExx');
					print "\n\t\t\t\tvar normalFieldName = '".$fieldName."';";
					
					$harmoni->request->startNameSpace('xxNAMESPACExx');
					$fieldName = RequestContext::name('xxFIELDNAMExx');
					$harmoni->request->endNameSpace();
					print "\n\t\t\t\tvar namespacedFieldName = '".$fieldName."';";
				?>
				var debug = '';
				if (namespace) {
					var fieldName = namespacedFieldName.replaceAll(/xxNAMESPACExx/, namespace);
					debug += "\nnamespaced";
				} else
					var fieldName = normalFieldName;
								
				return fieldName.replaceAll(/xxFIELDNAMExx/, name);
			}
			
			// ]]>
			</script>

