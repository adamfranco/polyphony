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
 * @version $Id: polyphony.inc.php,v 1.23 2005/09/01 18:44:44 nstamato Exp $
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
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WTextArea.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WVerticalRadioList.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WSaveCancelListener.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WRepeatableComponentCollection.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WOrderedRepeatableComponentCollection.class.php");
require_once(dirname(__FILE__)."/main/library/Wizard/Components/WColorWheel.class.php");
// error checking
require_once(dirname(__FILE__)."/main/library/Wizard/ErrorCheckingRules/WECRegex.class.php");

require_once(dirname(__FILE__)."/main/library/ResultPrinter/IteratorResultPrinter.class.php");
require_once(dirname(__FILE__)."/main/library/ResultPrinter/ArrayResultPrinter.class.php");

require_once(dirname(__FILE__)."/main/library/Basket/BasketManager.class.php");


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
?>