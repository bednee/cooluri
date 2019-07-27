<?php
namespace Bednarik\Cooluri\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Jan Bednarik <info@bednarik.org>, Bednarik.org
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * CodeController
 */
class CoolUriModController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    public function everythingAction() {
        $this->confArray = unserialize($GLOBALS['TYPO3_CONF_VARS']['EXT']['extConf']['cooluri']);

        if (file_exists(PATH_site . $this->confArray['XMLPATH'] . 'CoolUriConf.xml')) {
            $lt = PATH_site . $this->confArray['XMLPATH'] . 'CoolUriConf.xml';
        } elseif (file_exists(PATH_typo3conf . 'CoolUriConf.xml')) {
            $lt = PATH_typo3conf . 'CoolUriConf.xml';
        } elseif (file_exists(dirname(__FILE__) . '/../cooluri/CoolUriConf.xml')) {
            $lt = dirname(__FILE__) . '/../cooluri/CoolUriConf.xml';
        } else {
            $this->view->assign('everything','XML Config file not found');
            return;
        }
        $baseUrl = \TYPO3\CMS\Backend\Utility\BackendUtility::getModuleUrl('tools_CooluriCool1');
        $lm = new \Bednarik\Cooluri\Manager\Main($baseUrl.'&', $lt, \TYPO3\CMS\Core\Utility\PathUtility::getAbsoluteWebPath(\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::extPath('cooluri').'Resources/Public/CoolUriMod/'));

        $c = $lm->menu();
        $c .= $lm->main();

        $this->view->assign('everything',$c);
    }

}