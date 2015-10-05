<?php
namespace Bednarik\Cooluri\Integration;

/*
 * This file is part of the TYPO3 CMS project.
 *
 * It is free software; you can redistribute it and/or modify it under
 * the terms of the GNU General Public License, either version 2
 * of the License, or any later version.
 *
 * For the full copyright and license information, please read the
 * LICENSE.txt file that was distributed with this source code.
 *
 * The TYPO3 project - inspiring people to share!
 */

use TYPO3\CMS\Core\Utility\GeneralUtility;

class BackendUtilityHook implements \TYPO3\CMS\Core\SingletonInterface {

    /**
     * Gets a singleton instance of this object.
     *
     * @return \Bednarik\Cooluri\Integration\BackendUtilityHook
     */
    static public function getInstance() {
        return GeneralUtility::makeInstance(__CLASS__);
    }

    /**
     * Hooks into the \TYPO3\CMS\Backend\Utility\BackendUtility::viewOnClick and adds ADMCMD_prev parameter to prevent generation of CoolUri link
     *
     * @param int $pageUid
     * @param string $backPath
     * @param array $rootLine
     * @param string $anchorSection
     * @param string $viewScript
     * @param string $additionalGetVars
     * @param bool $switchFocus
     * @return void
     */
    public function preProcess(&$pageUid, $backPath, $rootLine, $anchorSection, &$viewScript, &$additionalGetVars, $switchFocus) {
        $additionalGetVars .= '&ADMCMD_cooluri=1';
    }
}
