<?php

/**
 * This file includes all necessary Polyphony classes
 *
 *
 * @package polyphony
 * 
 * @copyright Copyright &copy; 2005, Middlebury College
 * @license http://www.gnu.org/copyleft/gpl.html GNU General Public License (GPL)
 *
 * @version $Id: polyphony.inc.php,v 1.36 2006/05/02 20:23:59 adamfranco Exp $
 */

/**
 * Define a constant for the Polyphony root directory.
 * 
 */
define("POLYPHONY", dirname(__FILE__));

/**
 * Include our library classes
 * 
 */
require_once(dirname(__FILE__)."/main/library/Wizard/SimpleStepWizard.class.php");
// components:
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WizardStep.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WTextField.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WCheckBox.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WFileUploadField.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WHorizontalRadioList.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WizardStep.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WMultiSelectList.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WPasswordField.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WSelectList.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WSelectOrNew.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WTextArea.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WVerticalRadioList.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WSaveCancelListener.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WRepeatableComponentCollection.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WOrderedRepeatableComponentCollection.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WNewOnlyEditableRepeatableComponentCollection.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WAddFromListRepeatableComponentCollection.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WHiddenField.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WText.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WVerifiedChangeInput.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WChooseOptionButton.class.php");

require_once(dirname(__FILE__)."/main/library/Wizard/Components/WAgentBrowser.class.php");

require_once(dirname(__FILE__)."/main/library/Wizard/Components/WColorWheel.class.php");

// error checking
require_once(dirname(__FILE__)."/main/library/Wizard/ErrorCheckingRules/WECRegex.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/ErrorCheckingRules/WECNonZeroRegex.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/ErrorCheckingRules/WECOptionalRegex.class.php");

require_once(dirname(__FILE__)."/main/library/ResultPrinter/IteratorResultPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/ArrayResultPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/EmbeddedArrayResultPrinter.class.php");

require_once(dirname(__FILE__)."/main/library/Basket/Basket.class.php");


require_once(dirname(__FILE__)."/main/library/RepositoryInputOutputModules/RepositoryInputOutputModuleManager.class.php");
Services::registerService("InOutModules", "RepositoryInputOutputModuleManager");

require_once(dirname(__FILE__)."/main/library/RepositorySearchModules/RepositorySearchModuleManager.class.php");
Services::registerService("RepositorySearchModules", "RepositorySearchModuleManager");

require_once(OKI2."osid/OsidContext.php");
$context =& new OsidContext;
$context->assignContext('harmoni', $harmoni);
require_once(HARMONI."oki2/shared/ConfigurationProperties.class.php");
$configuration =& new ConfigurationProperties;
Services::startManagerAsService("InOutModules", $context, $configuration);
Services::startManagerAsService("RepositorySearchModules", $context, $configuration);

require_once(dirname(__FILE__)."/main/library/HierarchyPrinter/GroupPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/HierarchyPrinter/HierarchyPrinter.class.php");

// NEW CONFIGS AFTER HERE!!!:

if (file_exists(dirname(__FILE__)."/config/domit.conf.php"))
	require_once(dirname(__FILE__)."/config/domit.conf.php");
else
	require_once(dirname(__FILE__)."/config/domit_default.conf.php");

if (file_exists(dirname(__FILE__)."/config/exif.conf.php"))
	require_once(dirname(__FILE__)."/config/exif.conf.php");
else
	require_once(dirname(__FILE__)."/config/exif_default.conf.php");
	

require_once(dirname(__FILE__)."/main/modules/help/browse_help.act.php");


?>