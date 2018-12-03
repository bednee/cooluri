<?php
namespace Bednarik\Cooluri\Integration;

use TYPO3\CMS\Core\Utility\GeneralUtility;
use TYPO3\CMS\Core\Utility\HttpUtility;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2010 Jan Bednarik <info@bednarik.org>
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

class CoolUri
{

    private static $pObj = null;
    private static $confArray = null;

    /**
     * returns singleton instance
     * instance is stored in the session for non-logged users
     * this is because of developement, users that are logged in BE could be
     * editing the conf file, so they need to see the changes immediately
     */
    public static function getTranslateInstance()
    {
        if (!isset($_SESSION) || !is_array($_SESSION)) {
            session_start();
        }
        if (!self::$confArray) {
            self::$confArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cooluri']);
            if (empty(self::$confArray['LANGID'])) {
                self::$confArray['LANGID'] = 'L';
            }
        }
        if (!self::isBEUserLoggedIn() && !empty($_SESSION['coolUriTransformerInstance']) && !empty($_SESSION['coolUriTransformerInstance']->conf)) {
            return $_SESSION['coolUriTransformerInstance'];
        }
        if (file_exists(self::$confArray['XMLPATH'] . 'CoolUriConf.xml')) {
            $lt = \Bednarik\Cooluri\Core\Translate::getInstance(self::$confArray['XMLPATH'] . 'CoolUriConf.xml');
        } elseif (file_exists(PATH_typo3conf . 'CoolUriConf.xml')) {
            $lt = \Bednarik\Cooluri\Core\Translate::getInstance(PATH_typo3conf . 'CoolUriConf.xml');
        } elseif (file_exists(dirname(__FILE__) . '/cooluri/CoolUriConf.xml')) {
            $lt = \Bednarik\Cooluri\Core\Translate::getInstance(dirname(__FILE__) . '/cooluri/CoolUriConf.xml');
        } else {
            return false;
        }
        if (!self::isBEUserLoggedIn()) {
            $cc = @clone($lt);
            $_SESSION['coolUriTransformerInstance'] = $cc;
        }
        return $lt;
    }

    public static function cool2params($params, $ref)
    {
        self::$pObj = & $ref;

        if (!empty($params['pObj']->siteScript)) {
            $cond = $params['pObj']->siteScript && substr($params['pObj']->siteScript, 0, 9) != 'index.php' && substr($params['pObj']->siteScript, 0, 1) != '?';
            $paramsinurl = '/' . $params['pObj']->siteScript;
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('SITESCRIPT: ' . $paramsinurl, 'CoolUri');
        } else {
            $cond = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI') && substr(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI'), 1, 9) != 'index.php' && substr(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI'), 1, 1) != '?';
            $paramsinurl = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI');
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('REQUEST_URI: ' . $paramsinurl, 'CoolUri');
        }

        // check if the only param is the same as the TYPO3 site root
        if ($paramsinurl == substr(PATH_site, strlen(preg_replace('~/$~', '', $_SERVER['DOCUMENT_ROOT'])))) {
            return;
        }

        if ($cond) {

            $lt = self::getTranslateInstance();

            if (!$lt) return;

            if (self::$confArray['MULTIDOMAIN'] || \Bednarik\Cooluri\Core\Translate::$conf->domainlanguages) {
                $domain = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
                $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'sys_domain', 'domainName=' . $GLOBALS['TYPO3_DB']->fullQuoteStr($domain, 'sys_domain') . ' AND hidden=0');
                $row = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
                if (!$row) {
                    return; // Domain is not available, so no translation
                }
                if (empty(\Bednarik\Cooluri\Core\Translate::$conf->cache->prefix)) {
                    if ($row && !empty($row['redirectTo'])) {
                        $url = $row['redirectTo'] . substr($paramsinurl, 1);
                        $path = \TYPO3\CMS\Core\Utility\GeneralUtility::locationHeaderUrl($url);
                        $path = str_replace('http://','//',$path);
                        $path = str_replace('https://','//',$path);
                        if (empty($row['redirectHttpStatusCode'])) {
                            header('Location: ' . $path);
                        } else {
                            header('Location: ' . $path, true, $row['redirectHttpStatusCode']);
                        }
                        exit;
                    }
                    self::simplexml_addChild(\Bednarik\Cooluri\Core\Translate::$conf->cache, 'prefix', $domain . '@');
                    \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('DOMAIN: ' . $domain, 'CoolUri');
                } else {
                    \Bednarik\Cooluri\Core\Translate::$conf->cache->prefix = $domain . '@';
                    \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('DOMAIN 2: ' . $domain, 'CoolUri');
                }
            }

            $pars = $lt->cool2params($paramsinurl);

            $params['pObj']->id = $pars['id'];
            unset($pars['id']);
            $npars = self::extractArraysFromParams($pars);
            self::stripSlashesOnArray($npars);
            $params['pObj']->mergingWithGetVars($npars);

            // Re-create QUERY_STRING from Get vars for use with typoLink()
            $_SERVER['QUERY_STRING'] = self::decodeSpURL_createQueryString($pars);
            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Resolved QS: ' . $_SERVER['QUERY_STRING'], 'CoolUri');
        }
    }

    /**
     * Generates a parameter string from an array recursively (function from RealUrl)
     *
     * @param    array        Array to generate strings from
     * @param    string        path to prepend to every parameter
     * @return    array        Array with parameter strings
     */
    private static function decodeSpURL_createQueryStringParam($paramArr, $prependString = '')
    {
        if (!is_array($paramArr)) {
            return array($prependString . '=' . $paramArr);
        }
        if (count($paramArr) == 0) {
            return array();
        }
        $paramList = array();
        foreach ($paramArr as $var => $value) {
            $paramList = array_merge($paramList, self::decodeSpURL_createQueryStringParam($value, $prependString . '[' . $var . ']'));
        }
        return $paramList;
    }

    /**
     * Re-creates QUERY_STRING for use with typoLink() (function from RealUrl)
     *
     * @param    array        List of Get vars
     * @return    string        QUERY_STRING value
     */
    private static function decodeSpURL_createQueryString(&$getVars)
    {
        if (!is_array($getVars) || count($getVars) == 0) {
            return $_SERVER['QUERY_STRING'];
        }
        $parameters = array();
        foreach ($getVars as $var => $value) {
            $parameters = array_merge($parameters, self::decodeSpURL_createQueryStringParam($value, $var));
        }
        // Don't use GeneralUtility::getIndpEnv() to avoid cache generation before overriding $_SERVER['QUERY_STRING']
        $queryString = $_SERVER['QUERY_STRING'];
        if ($queryString) {
            array_push($parameters, $queryString);
        }
        return implode('&', $parameters);
    }


    private static function getShortcutpage($page)
    {
        $limit = 5;
        $mode = $page['shortcut_mode'];
        while (!empty($page['shortcut_mode']) && $mode > 0 && $page['doktype'] == 4 && $limit > 0) {
            switch ($mode) {
                case 1:
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'pid=' . (int)$page['uid'] . $GLOBALS['TSFE']->cObj->enableFields('pages'), '', 'sorting', '1');
                    break;
                case 2:
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'nav_hide=0 and pid=' . (int)$page['uid'] . $GLOBALS['TSFE']->cObj->enableFields('pages'), '', 'RAND()', '1');
                    break;
                case 3:
                    $res = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'uid=' . (int)$page['pid'] . $GLOBALS['TSFE']->cObj->enableFields('pages'));
                    break;
                default:
                    $res = null;
            }
            $tmp = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
            if ($tmp) {
                $page = $tmp;
                $mode = $page['shortcut_mode'];
            }
            --$limit;
        }
        return $page;
    }

    private static function simplexml_addChild($parent, $name, $value = '')
    {
        $new_child = new \SimpleXMLElement("<$name>$value</$name>");
        $node1 = dom_import_simplexml($parent);
        $dom_sxe = dom_import_simplexml($new_child);
        $node2 = $node1->ownerDocument->importNode($dom_sxe, true);
        $node1->appendChild($node2);
        return simplexml_import_dom($node2);
    }

    public static function params2cool(&$params, $ref)
    {

        if (empty($GLOBALS['TSFE']->config['config']['tx_cooluri_enable']) || !$GLOBALS['TSFE']->config['config']['tx_cooluri_enable']) {
            return;
        }

        if (!empty($params['args']['page']['shortcut']) && $params['args']['page']['doktype'] == 4) {
            $shortcut = $params['args']['page']['shortcut'];
            $limit = 5;
            while (!empty($shortcut) && $limit > 0) {
                $page = $GLOBALS['TSFE']->sys_page->getPage($shortcut);
                $params['args']['page'] = $page;
                if (!$page || $page['doktype'] != 4) break;
                $shortcut = $page['shortcut'];
                --$limit;
            }
        } elseif (!empty($params['args']['page']['shortcut_mode']) && $params['args']['page']['shortcut_mode'] > 0 && $params['args']['page']['doktype'] == 4) {
            $page = self::getShortcutpage($params['args']['page']);
            $params['args']['page'] = $page;
        }

        if ($params['args']['page']['doktype'] == 3) {
            switch ($params['args']['page']['urltype']) {
                case 1:
                    $url = 'http://';
                    break;
                case 4:
                    $url = 'https://';
                    break;
                case 2:
                    $url = 'ftp://';
                    break;
                case 3:
                    $url = 'mailto:';
                    break;
            }
            $params['LD']['totalURL'] = $url . $params['args']['page']['url'];
            return;
        }

        $decodedUrl = urldecode($params['LD']['totalURL']);

        $decodedUrl = strtr($decodedUrl, array('|' => '%7C'));

        $tu = explode('?', $decodedUrl);
        \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('PARAMS URL: ' . $decodedUrl, 'CoolUri');

        if (isset($tu[1])) {
            $anch = explode('#', $tu[1]);
            $pars = \Bednarik\Cooluri\Core\Functions::convertQuerystringToArray($tu[1]);

            $pars['id'] = $params['args']['page']['uid'];

            $lt = self::getTranslateInstance();
            if (!$lt) return;

            if (self::$confArray['MULTIDOMAIN'] || \Bednarik\Cooluri\Core\Translate::$conf->domainlanguages) {
                if (!empty($params['LD']['domain'])) {
                    $domain = $params['LD']['domain'];
                } elseif (!empty($pars['MP'])) {
                    // found MP call - get ID of page which mounts
                    $mpSource = (int)substr($pars['MP'], strpos($pars['MP'], '-') + 1);
                    if ($mpSource > 0) {
                        $domain = self::getDomain($mpSource);
                    } else {
                        $domain = self::getDomain((int)$pars['id']);
                    }
                } else {
                    $domain = self::getDomain((int)$pars['id']);
                }
            }

            if (self::$confArray['MULTIDOMAIN']) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('MultiDomain on', 'CoolUri');
                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Domain: ' . $domain, 'CoolUri');
                if (empty(\Bednarik\Cooluri\Core\Translate::$conf->cache->prefix)) {
                    self::simplexml_addChild(\Bednarik\Cooluri\Core\Translate::$conf->cache, 'prefix', $domain . '@');
                } else {
                    \Bednarik\Cooluri\Core\Translate::$conf->cache->prefix = $domain . '@';
                }
            } elseif (\Bednarik\Cooluri\Core\Translate::$conf->domainlanguages) {
                self::prefixWithLangDomain($pars, $domain);
            }
            $params['LD']['totalURL'] = $lt->params2cool($pars, '', false) . (!empty($anch[1]) ? '#' . $anch[1] : '');

            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Found URL: ' . $params['LD']['totalURL'], 'CoolUri');

            // urlencode stuff after ?
            $parts = explode('?', $params['LD']['totalURL']);
            if (isset($parts[1])) {
                $parts[1] = strtr($parts[1], array('[' => '%5B', ']' => '%5D', '|' => '%7C'));
            }
            $params['LD']['totalURL'] = implode('?', $parts);

            if (self::$confArray['MULTIDOMAIN'] || \Bednarik\Cooluri\Core\Translate::$conf->domainlanguages) {
                if (strpos($params['LD']['totalURL'], '@')) {
                    $params['LD']['totalURL'] = explode('@', $params['LD']['totalURL']);
                    $beforeat = $params['LD']['totalURL'][0];
                    unset($params['LD']['totalURL'][0]);
                    $afterat = implode('@', $params['LD']['totalURL']);

                    \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('In the same domain: ' . $beforeat . '==' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST'), 'CoolUri');

                    if ($beforeat == \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST')) {
                        $params['LD']['totalURL'] = $afterat;
                    } else {
                        $protocol = 'http';
                        if ($params['args']['page']['url_scheme'] == HttpUtility::SCHEME_HTTPS || $params['args']['page']['url_scheme'] == 0 && GeneralUtility::getIndpEnv('TYPO3_SSL')) {
                            $protocol = 'https';
                        }
                        $params['LD']['totalURL'] = $protocol.'://' . $beforeat . '/' . $afterat;
                    }
                } else {
                    \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('@ not found in expected MultiDomain URL: ' . $params['LD']['totalURL'], 'CoolUri', 2);
                }
            }

            // Check if config.absRefPrefix is set and if link doesn't already start with http:// or https://
            if (!empty($GLOBALS['TSFE']->config['config']['absRefPrefix'])) {
                if (!strpos($params['LD']['totalURL'], '://'))
                    $params['LD']['totalURL'] = $GLOBALS['TSFE']->config['config']['absRefPrefix'] . ($params['LD']['totalURL'] != '/' ? $params['LD']['totalURL'] : '');
            }

            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Result URL: ' . $params['LD']['totalURL'], 'CoolUri');
        }
    }

    public static function prefixWithLangDomain(&$pars, $domain)
    {
        if (!isset($pars[self::$confArray['LANGID']])) {
            $pars[self::$confArray['LANGID']] = $GLOBALS['TSFE']->config['config']['sys_language_uid'] ? $GLOBALS['TSFE']->config['config']['sys_language_uid'] : 0;
        }
        $group = false;
        foreach (\Bednarik\Cooluri\Core\Translate::$conf->domainlanguages->domain as $d) {
            if ($group === false && !empty($d['group'])) {
                $group = self::findDomainGroup($domain);
            }
            if ($group && $d['group'] != $group) {
                continue;
            }
            if ($d['lang'] == $pars[self::$confArray['LANGID']]) {
                \Bednarik\Cooluri\Core\Translate::$conf->cache->prefix = (String)$d . '@';
                break;
            }
        }
    }

    public static function findDomainGroup($domain)
    {
        foreach (\Bednarik\Cooluri\Core\Translate::$conf->domainlanguages->domain as $d) {
            if ((String)$d == $domain) {
                return (String)$d['group'];
            }
        }
        return false;
    }

    public static function getDomain($id)
    {
        static $domains = array();
        if (isset($domains[$id])) {
            return $domains[$id];
        }

        \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Getting domain for ' . $id, 'CoolUri');
        if ($GLOBALS['TSFE']->showHiddenPage || self::isBEUserLoggedIn()) {
            $enable = ' AND pages.deleted=0';
        } else {
            $enable = ' AND pages.deleted=0 AND pages.hidden=0';
        }
        $db = & $GLOBALS['TYPO3_DB'];
        $max = 10;
        while ($max > 0 && $id) {

            \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Looking for domain on page ' . $id, 'CoolUri');
            
            $q = $db->exec_SELECTquery('pages.title, pages.pid, pages.is_siteroot, pages.uid AS id, sys_domain.domainName, sys_domain.redirectTo', 'pages LEFT JOIN sys_domain ON pages.uid=sys_domain.pid', 'pages.uid=' . $id . $enable . ' AND (sys_domain.domainName IS NULL OR sys_domain.domainName="'.\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST').'") AND (sys_domain.hidden=0 OR sys_domain.hidden IS NULL)', '', 'sys_domain.sorting');
            $page = $db->sql_fetch_assoc($q);

            if ($page['domainName'] && !$page['redirectTo']) {
                $resDom = preg_replace('~^.*://(.*)/?$~', '\\1', preg_replace('~/$~', '', $page['domainName']));
                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Resolved domain: ' . $resDom, 'CoolUri');
                $domains[$id] = $resDom;
                return $resDom;
            }

            $count = NULL;
            if ($page['is_siteroot'] != 1) {
                $count = self::getTemplateCount($id);
            }

            if ($page['is_siteroot'] == 1 || $count['num'] > 0) {
                \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Domain missing for ID ' . $id . ', using HTTP_HOST ' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST'), 'CoolUri');
                $domains[$id] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
                return \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
            }


            $id = $page['pid'];
            --$max;
        }
        \TYPO3\CMS\Core\Utility\GeneralUtility::devLog('Domain not found, using HTTP_HOST ' . \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST'), 'CoolUri', 2);
        $domains[$id] = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
        return \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('HTTP_HOST');
    }

    protected static function getTemplateCount($pagId)
    {
        static $countCache = [];
        if (isset($countCache[$pagId])) {
            return $countCache[$pagId];
        }

        if ($GLOBALS['TSFE']->showHiddenPage || self::isBEUserLoggedIn()) {
            $enable = ' AND deleted=0';
        } else {
            $enable = ' AND deleted=0 AND hidden=0';
        }


        $db = &$GLOBALS['TYPO3_DB'];
        $temp = $db->exec_SELECTquery('COUNT(*) as num', 'sys_template', 'deleted=0 AND hidden=0 AND pid=' . $pagId . ' AND root=1' . $enable);
        $countCache[$pagId] = $db->sql_fetch_assoc($temp);
        return $countCache[$pagId];
    }

    public static function goForRedirect($params, $ref)
    {
        if (empty($_GET['eID']) && empty($_GET['ADMCMD_prev']) && empty($_GET['ADMCMD_cooluri']) && $GLOBALS['TSFE']->config['config']['tx_cooluri_enable'] == 1 && $GLOBALS['TSFE']->config['config']['redirectOldLinksToNew'] == 1 && \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI') && (substr(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI'), 1, 9) == 'index.php' || substr(\TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI'), 1, 1) == '?')) {
            $ourl = \TYPO3\CMS\Core\Utility\GeneralUtility::getIndpEnv('REQUEST_URI');
            $ss = explode('?', $ourl);
            if ($ss[1]) {
                $ss[1] = strtr($ss[1], array('%5B' => '[', '%5D' => ']'));
                $pars = \Bednarik\Cooluri\Core\Functions::convertQuerystringToArray($ss[1]);
            }

            $pageid = $pars['id'];

            if (is_null($pageid)) {
                // No page id given, so no need to redirect
                return;
            } else if (!ctype_digit($pageid)) {
                $pageid = $GLOBALS['TYPO3_DB']->fullQuoteStr($pageid, 'pages');
                $q = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'alias=' . $pageid);
                $page = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($q);
                $pars['id'] = (int)$page['uid'];
            } else {
                $q = $GLOBALS['TYPO3_DB']->exec_SELECTquery('*', 'pages', 'uid=' . (int)$pageid);
                $page = $GLOBALS['TYPO3_DB']->sql_fetch_assoc($q);
                $pars['id'] = (int)$page['uid'];
            }
            // if a page is hidden, there won't be any redirect, because it would
            // redirect to the root
            if (!$page || $page['hidden'] == 1 || $page['deleted'] == 1) {
                return;
            }

            if ($pars) {
                $lt = self::getTranslateInstance();

                if (!$lt) return;

                if (self::$confArray['MULTIDOMAIN']) {
                    if (empty(\Bednarik\Cooluri\Core\Translate::$conf->cache->prefix)) {
                        self::simplexml_addChild(\Bednarik\Cooluri\Core\Translate::$conf->cache, 'prefix', self::getDomain((int)$pars['id']) . '@');
                    } else {
                        \Bednarik\Cooluri\Core\Translate::$conf->cache->prefix = self::getDomain((int)$pars['id']) . '@';
                    }
                }
                $url = $lt->params2coolForRedirect($pars);

                $parts = explode('?', $url);
                if (empty($parts[0])) return;

                if (self::$confArray['MULTIDOMAIN'] || \Bednarik\Cooluri\Core\Translate::$conf->domainlanguages) {
                    $url = explode('@', $url);
                    $url = 'http://' . $url[0] . '/' . $url[1];
                }

                \Bednarik\Cooluri\Core\Functions::redirect($url, 301);
            }
        }
    }

    public static function getPageTitleBE($conf, $value)
    {
        if ($GLOBALS['TSFE']->showHiddenPage || self::isBEUserLoggedIn()) {
            $enable = ' AND deleted=0';
        } else {
            $enable = ' AND deleted=0 AND hidden=0';
        }
        $db = & $GLOBALS['TYPO3_DB'];

        $id = (int)$value[(string)$conf->saveto];

        $confArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cooluri']);
        $langVar = $confArray['LANGID'];
        if (empty($langVar)) {
            $langVar = 'L';
        }

        $langId = isset($value[$langVar]) ? $value[$langVar] : $GLOBALS['TSFE']->config['config']['sys_language_uid'];
        $langId = (int)$langId;

        $pagepath = Array();

        if (empty($conf->alias)) {
            $sel = (string)$conf->title;
        } else {
            $sel = (string)$conf->alias;
        }
        $sel = \TYPO3\CMS\Core\Utility\GeneralUtility::trimExplode(',', $sel);

        $max = 15;

        while ($max > 0 && $id) {
            if (!is_numeric($id)) {
                $id = $GLOBALS['TSFE']->sys_page->getPageIdFromAlias($id);
            }
            $q = $db->exec_SELECTquery('*', 'pages', 'uid=' . $id . $enable);
            $page = $db->sql_fetch_assoc($q);

            if (!$page) {
                break;
            }

            $count = self::getTemplateCount($id);
            if ($count['num'] > 0 || $page['is_siteroot'] == 1) {
                return $pagepath;
            }

            if ($langId) {
                $q = $db->exec_SELECTquery('*', 'pages_language_overlay', 'pid=' . $id . ' AND sys_language_uid=' . $langId . $enable);
                $lo = $db->sql_fetch_assoc($q);
                if ($lo) {
                    unset($lo['uid']);
                    unset($lo['pid']);
                    $page = array_merge($page, $lo);
                }
            }

            if (($page['tx_cooluri_exclude'] == 1 && !empty($pagepath)) || $page['tx_cooluri_excludealways']) {
                ++$max;
                $id = $page['pid'];
                continue;
            }

            foreach ($sel as $s) {
                $trimmed = trim($page[$s]);
                if (!empty($trimmed)) {
                    $title = $trimmed;
                    break;
                }
            }

            if (!empty($conf->sanitize) && $conf->sanitize == 1) {
                $pagepath[] = \Bednarik\Cooluri\Core\Functions::sanitize_title_with_dashes($title);
            } elseif (!empty($conf->t3conv) && $conf->t3conv == 1) {
                $pagepath[] = \Bednarik\Cooluri\Core\Functions::specCharsToASCII($title);
            } elseif (!isset($conf->urlize) || $conf->urlize != 0) {
                $pagepath[] = \Bednarik\Cooluri\Core\Functions::URLize($title);
            } else {
                $pagepath[] = urlencode($title);
            }
            $id = $page['pid'];

            --$max;

            if (!empty($conf->maxsegments) && count($pagepath) >= (int)$conf->maxsegments) {
                $max = 0;
            }
        }
        return $pagepath;
    }

    public static function getPageTitle($conf, $value)
    {
        return CoolUri::getPageTitleBE($conf, $value);
        // this function didn't work for pages with restricted access.
        // The BE function should work everywhere
    }

    private static function extractArraysFromParams($params)
    {
        // turn array back into query string
        // so it can be used with parse_str
        if (empty($params)) {
            return Array();
        }
        foreach ($params as $k => $v) $params[$k] = $k . '=' . rawurlencode($v);
        $qs = implode('&', $params);
        parse_str($qs, $output);
        return $output;
    }

    private static function isBEUserLoggedIn()
    {
        if (self::$pObj == null) return false;
        return self::$pObj->beUserLogin;
    }

    public static function pageNotFound()
    {
        $GLOBALS['TSFE']->pageNotFoundAndExit();
    }

    private static function stripSlashesOnArray(array &$theArray)
    {
        foreach ($theArray as &$value) {
            if (is_array($value)) {
                self::stripSlashesOnArray($value);
            } else {
                $value = stripslashes($value);
            }
        }
        unset($value);
        reset($theArray);
    }
}

?>
