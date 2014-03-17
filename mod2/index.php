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

// DEFAULT initialization of a module [BEGIN]
$LANG->includeLLFile('EXT:cooluri/mod1/locallang.xml');
$BE_USER->modAccess($MCONF, 1); // This checks permissions and exits if the users has no permission for entry.
// DEFAULT initialization of a module [END]

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
class  tx_cooluri_module2 extends t3lib_SCbase {
    var $pageinfo;

    /**
     * Initializes the Module
     * @return    void
     */
    function init() {
        global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;
        parent::init();
        if (t3lib_div::_GP('clear_all_cache')) {
            $this->include_once[] = PATH_t3lib . 'class.t3lib_tcemain.php';
        }
    }

    /**
     * Main function of the module. Write the content to $this->content
     * If you chose "web" as main module, you will need to consider the $this->id parameter which will contain the uid-number of the page clicked in the page tree
     *
     * @return    [type]        ...
     */
    function main() {
        global $BE_USER, $LANG, $BACK_PATH, $TCA_DESCR, $TCA, $CLIENT, $TYPO3_CONF_VARS;

        $this->doc = t3lib_div::makeInstance('template');
        $this->doc->setModuleTemplate(t3lib_extMgm::extPath('cooluri') . 'mod2/mod_template.html');
        $this->doc->backPath = $BACK_PATH;
        $this->pageRenderer = $this->doc->getPageRenderer();
        $this->pageRenderer->addCssFile($BACK_PATH . t3lib_extMgm::extRelPath('cooluri') . 'mod1/style.css');

        $markers['CONTENT'] = $this->moduleContent();
        // Build the <body> for the module
        $this->doc->form = '<form action="'.t3lib_BEfunc::getModuleUrl('user_txcooluriM2').'" method="post">';
        $this->content = $this->doc->startPage('');
        $this->content .= $this->doc->moduleBody($this->pageinfo, null, $markers);
        $this->content = $this->doc->insertStylesAndJS($this->content);
    }

    /**
     * Prints out the module HTML
     *
     * @return    void
     */
    function moduleContent() {
        $this->content = '';
        if (!empty($_POST['delete']) && is_array($_POST['delete'])) {
            foreach ($_POST['delete'] as $id=>$v) {
                $GLOBALS['TYPO3_DB']->exec_DELETEquery('link_cache','id = '.(int)$id);
            }
            $tce = t3lib_div::makeInstance('t3lib_TCEmain');
            $tce->start(null,null);
            $tce->clear_cacheCmd('pages');

            $this->content .= '<div class="typo3-message message-information">A link has been removed from cache, please reload page where the link is present (not the page itself, but e.g. parent page with this link in menu) in order to generate it again.</div>';
        }

        $this->content .= '<h2>Find URL</h2><p>This tool can fix problems with wrong URL that are result of duplicate content (e.g. when you delete a page and create a new one with the same name, then its URL
        points to the deleted one). Just paste any URL here and when found, delete it.</p>
            <input type="text" name="url" size="100" value="'.(!empty($_POST['url']) ? htmlspecialchars($_POST['url']) : '').'" /><input type="submit" value="Find URL" />
        ';

        $urls = $this->getUrls($_POST['url']);
        if ($urls) {
            $this->content .= $this->doc->spacer(10);
            foreach ($urls as $url) {
                $this->content .= '<input type="submit" value="Delete" name="delete['.$url['id'].']"/> '.$url['url'].' '.http_build_query (unserialize($url['params'])).'<br/>';
            }
        }

        $this->content .= '</div>';
        return $this->content;
    }

    function printContent() {
        $this->content .= $this->doc->endPage();
        echo $this->content;
    }

    function getUrls($url) {
        if (!empty($url)) {
            $parsedUrl = parse_url($url);
            if ($parsedUrl) {
                $parsedUrl['path'] = $GLOBALS['TYPO3_DB']->quoteStr($parsedUrl['path'],null);
                $parsedUrl['host'] = $GLOBALS['TYPO3_DB']->quoteStr($parsedUrl['host'],null);
                $possibleMatches = Array();
                $possibleMatches[] = $parsedUrl['path'];
                $possibleMatches[] = $parsedUrl['host'].'@'.$parsedUrl['path'];
                if (substr($parsedUrl['path'],-1) == '/') {
                    $possibleMatches[] = substr($parsedUrl['path'],0,strlen($parsedUrl['path'])-1);
                    $possibleMatches[] = $parsedUrl['host'].'@'.substr($parsedUrl['path'],0,strlen($parsedUrl['path'])-1);
                }
                if (substr($parsedUrl['path'],0,1) == '/') {
                    $possibleMatches[] = substr($parsedUrl['path'],1,strlen($parsedUrl['path']));
                    $possibleMatches[] = $parsedUrl['host'].'@'.substr($parsedUrl['path'],1,strlen($parsedUrl['path']));
                }
                if (substr($parsedUrl['path'],-1) == '/' && substr($parsedUrl['path'],0,1) == '/') {
                    $possibleMatches[] = substr($parsedUrl['path'],1,strlen($parsedUrl['path'])-1);
                    $possibleMatches[] = $parsedUrl['host'].'@'.substr($parsedUrl['path'],1,strlen($parsedUrl['path'])-1);
                }
                $where = "(url = '".implode("' OR url='",$possibleMatches)."')";
                return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','link_cache',$where);
            }
        }
        return false;
    }


}


if (defined('TYPO3_MODE') && $TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cooluri/mod1/index.php']) {
    include_once($TYPO3_CONF_VARS[TYPO3_MODE]['XCLASS']['ext/cooluri/mod1/index.php']);
}


// Make instance:
$SOBE = t3lib_div::makeInstance('tx_cooluri_module2');
$SOBE->init();

// Include files?
foreach ($SOBE->include_once as $INC_FILE) include_once($INC_FILE);

$SOBE->main();
$SOBE->printContent();

?>
