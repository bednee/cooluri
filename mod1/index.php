<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2008 Jan Bednarik <info@bednarik.org>
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 2 of the License, or
 *  (at your option) any later version.
 *
 *  The GNU General Public License can be found at
 *  http://www.gnu.org/copyleft/gpl.html.
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

$LANG->includeLLFile('EXT:cooluri/mod1/locallang.xml');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]
require_once t3lib_extMgm::extPath('cooluri') . 'class.tx_cooluri.php';

if (!class_exists('t3lib_SCbase')) {
    class t3lib_SCbase extends \TYPO3\CMS\Backend\Module\BaseScriptClass {}
}

/**
 * Module 'CoolURI' for the 'cooluri' extension.
 *
 * @author    Jan Bednarik <info@bednarik.org>
 * @package    TYPO3
 * @subpackage    tx_cooluri
 */
class  tx_cooluri_module1 extends t3lib_SCbase {
    var $pageinfo;

    /**
     * Initializes the Module
     * @return    void
     */
    function init() {
        global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
        parent::init();
    }

    /**
     * Main function of the module. Write the content to $this->content
     * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
     *
     * @return    [type]        ...
     */
    function main() {
        global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;

        if ($BE_USER->user['admin']) {

            if (class_exists('t3lib_div')) {
                $this->doc = t3lib_div::makeInstance('template');
                $this->doc->setModuleTemplate(t3lib_extMgm::extPath('cooluri') . 'mod1/mod_template.html');
                $this->doc->backPath = $BACK_PATH;
                $this->pageRenderer = $this->doc->getPageRenderer();

                $this->pageRenderer->addCssFile($BACK_PATH . t3lib_extMgm::extRelPath('cooluri') . 'mod1/style.css');
                require_once t3lib_extMgm::extPath('cooluri') . 'cooluri/manager/linkmanager.Main.php';
            } else {
                $this->doc = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('TYPO3\CMS\Backend\Template\DocumentTemplate');
                $this->doc->setModuleTemplate(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('cooluri') . 'mod1/mod_template.html');
                $this->doc->backPath = $BACK_PATH;
                $this->pageRenderer = $this->doc->getPageRenderer();

                $this->pageRenderer->addCssFile($BACK_PATH . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('cooluri') . 'mod1/style.css');
                require_once \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('cooluri') . 'cooluri/manager/linkmanager.Main.php';
            }

            $this->confArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cooluri']);
            $bp = str_replace('typo3/', '', $BACK_PATH);
            if (file_exists(PATH_site . $this->confArray['XMLPATH'] . 'CoolUriConf.xml')) {
                $lt = PATH_site . $this->confArray['XMLPATH'] . 'CoolUriConf.xml';
            } elseif (file_exists(PATH_typo3conf . 'CoolUriConf.xml')) {
                $lt = PATH_typo3conf . 'CoolUriConf.xml';
            } elseif (file_exists(dirname(__FILE__) . '/../cooluri/CoolUriConf.xml')) {
                $lt = dirname(__FILE__) . '/../cooluri/CoolUriConf.xml';
            } else {
                $this->content .= 'XML Config file not found';
                return;
            }
            if (class_exists('t3lib_BEfunc')) {
                $baseUrl = t3lib_BEfunc::getModuleUrl('tools_txcooluriM1');
                $lm = new LinkManger_Main($baseUrl.'&', $lt, $BACK_PATH . t3lib_extMgm::extRelPath('cooluri').'mod1/');
            } else {
                $baseUrl = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('tools_txcooluriM1');
                $lm = new LinkManger_Main($baseUrl.'&', $lt, $BACK_PATH . \TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extRelPath('cooluri').'mod1/');
            }

            $this->content .= $lm->menu();
            $this->content .= $lm->main();

            $markers['CONTENT'] = $this->content;

            // Build the <body> for the module
            $this->doc->form = '';
            $this->content = $this->doc->startPage('');
            $this->content .= $this->doc->moduleBody($this->pageinfo, null, $markers);
            $this->content = $this->doc->insertStylesAndJS($this->content);
        }
    }

    /**
     * Prints out the module HTML
     *
     * @return    void
     */
    function printContent() {
        $this->content .= $this->doc->endPage();
        echo $this->content;
    }


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cooluri/mod1/index.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cooluri/mod1/index.php']);
}


if (class_exists('t3lib_div')) {
    // Make instance:
    $SOBE = t3lib_div::makeInstance('tx_cooluri_module1');
} else {
    $SOBE = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance('tx_cooluri_module1');
}
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>