			
			<script type='text/javascript'>
			// <![CDATA[
			
			/**
			 * Add a method to string to allow it to replace all occurances
			 * of an expression
			 * 
			 * @param RegExp regExp
			 * @param string replaceValue
			 * @return String
			 * @access public
			 * @since 11/10/06
			 */
			String.prototype.replaceAll = function (regExp, replaceValue) {
				var newString = this;
				var matches;
				while (matches = newString.match(regExp)) {
 					newString = newString.replace(regExp, replaceValue);
				}
				return newString;
			}
			
			/**
			 * Replace '&amp;' in URLs with '&'
			 * 
			 * @return string
			 * @access public
			 * @since 6/12/06
			 */
			String.prototype.urlDecodeAmpersands = function () {
				return this.replaceAll(/&amp;/, '&');
			}
			
			/**
			 * Answer the element of the document by id.
			 * 
			 * @param string id
			 * @return object The html element
			 * @access public
			 * @since 8/25/05
			 */
			document.get_element_by_id = function (id) {
				// Gecko, KHTML, Opera, IE6+
				if (document.getElementById) {
					return document.getElementById(id);
				}
				// IE 4-5
				if (document.all) {
					return document.all[id];
				}			
			}
			
			/**
			 * A Class for harmoni related static methods
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
					$harmoni =& Harmoni::instance();
					$url = $harmoni->request->quickURL('xxMODULExx', 'xxACTIONxx', 
						array('xxKEY1xx'=> 'xxVALUE1xx', 'xxKEY2xx' => 'xxVALUE2xx'));
					print "\n\t\t\t\tvar normalUrl = '".$url."';";
					
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
			
			// ]]>
			</script>
			