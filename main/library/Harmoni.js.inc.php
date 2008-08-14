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
			
			Harmoni.POLYPHONY_PATH = "<?php print rtrim(POLYPHONY_PATH, '/'); ?>";
			Harmoni.MYPATH = "<?php print rtrim(MYPATH, '/'); ?>";
			
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
					
					// Request token support.
					$harmoni->request->startNamespace('request');
					print "\n\t\t\t\tvar requestTokenKey = '".$harmoni->request->_mkFullName('token')."';";
					$harmoni->request->endNamespace();
					print "\n\t\t\t\tvar requestToken = '".$harmoni->ActionHandler->getRequestToken()."';";
					if (count($harmoni->ActionHandler->getRequestTokenRequired()))
						print "\n\t\t\t\tvar requestTokenRequired = ['".implode("', '", $harmoni->ActionHandler->getRequestTokenRequired())."'];";
					else
						print "\n\t\t\t\tvar requestTokenRequired = [];";
					
				?>
				
				var debug = '';
				if (namespace) {
					var parts = Harmoni.getUrlParts(
						namespacedUrl.replaceAll(/xxnamespacexx/, namespace));
					debug += "\nnamespaced";
				} else
					var parts = Harmoni.getUrlParts(normalUrl);
				
				var newUrl = parts.baseUrl.replace(/xxMODULExx/, module);
				newUrl = newUrl.replace(/xxACTIONxx/, action);
				
				if (parameters) {
					for (var key in parameters) {
						newUrl += parts.parameterSeparator + key + parts.keyValueSeparator + parameters[key];
					}
				}
				
				// Add the request token if needed.
				if (Harmoni.isActionInArray(module, action, requestTokenRequired)) {
					var normalParts = Harmoni.getUrlParts(normalUrl);
					newUrl += normalParts.parameterSeparator + requestTokenKey + normalParts.keyValueSeparator + requestToken;
				}
				
				return newUrl;
			}
			
			/**
			 * Answer a list of parts from a url that contains placeholders
			 * 
			 * @param string url
			 * @return object
			 * @access private
			 * @since 8/14/08
			 */
			Harmoni.getUrlParts = function (url) {
				url = url.urlDecodeAmpersands();
				
				var parts = {};
				
				var result = url.match(/^.+xxACTIONxx/);
				parts.baseUrl = result[0];
				
				var result = url.match(/xxVALUE1xx(.+)xxKEY2xx/);
				parts.parameterSeparator = result[1];
				
				var result = url.match(/xxKEY1xx(.+)xxVALUE1xx/);
				parts.keyValueSeparator = result[1];
				
				return parts;
			}
			
			/**
			 * Answer true if the module and action passed require a request token.
			 * 
			 * @param string module
			 * @param string action
			 * @param array tokensRequired
			 * @return boolean
			 * @access private
			 * @since 8/14/08
			 */
			Harmoni.isActionInArray = function (module, action, tokensRequired) {
				if (tokensRequired.elementExists(module + '.' + action))
					return true;
				
				// Look for wildcard actions
				for (var i = 0; i < tokensRequired.length; i++) {
					var results = tokensRequired[i].match(/(.+)\.(.+)/);
					if (results[1] == module && results[2] == '*')
						return true;
				}
				
				return false;
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

