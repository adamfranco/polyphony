<?php
/**
 * @package polyphony.startupcheck
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: error.tpl.php,v 1.4 2007/09/19 14:04:50 adamfranco Exp $
 */

?><HTML>
	<HEAD>
		<TITLE><?php =$pageTitle?></TITLE>
		<STYLE TYPE="text/css">
			body {
				background-color: #eee;
				margin: 50px 150px 50px 150px;
				padding: 30px;
				color: #333;
				font-family: Verdana;
				
				border: 1px dotted #555;
			}
			
			body p {
				font-size: 12px;
				text-align: center;
				color: #955;
			}
			
			body div {
				font-size: 18px;
				font-weight: normal;
			}
		</STYLE>
	</HEAD>
	
	<BODY>
		<P><?php =$intro?>:</P>
		<DIV>
			<?php =$errorString?>
		</DIV>
	</BODY>
</HTML>