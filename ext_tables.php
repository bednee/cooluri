<?php
if (!defined('TYPO3_MODE')) die ('Access denied.');

if (TYPO3_MODE === 'BE') {
    /**
     * Registers a Backend Module
     */
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Bednarik.' . $_EXTKEY,
        'tools',
        'cool1',	// Submodule key
        '',						// Position
        array(
            'CoolUriMod' => 'everything',
        ),
        array(
            'access' => 'admin',
            'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/mod.gif',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_cool1.xlf',
        )
    );
    \TYPO3\CMS\Extbase\Utility\ExtensionUtility::registerModule(
        'Bednarik.' . $_EXTKEY,
        'web',
        'fix1',	// Submodule key
        '',						// Position
        array(
            'LinkFix' => 'list,delete',
        ),
        array(
            'access' => 'user,group',
            'icon'   => 'EXT:' . $_EXTKEY . '/Resources/Public/Icons/mod.gif',
            'labels' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_fix1.xlf',
        )
    );

}

$TCA['pages']['columns']['tx_realurl_pathsegment'] = array(
    'label' => 'LLL:EXT:cooluri/locallang_db.php:pages.tx_cooluri_pathsegment',
    'exclude' => 1,
    'config' => Array(
        'type' => 'input',
        'size' => '50',
        'max' => '255',
        'eval' => 'trim,nospace,lower,uniqueInPid'
    )
);

$TCA['pages']['columns']['tx_cooluri_exclude'] = array(
    'label' => 'LLL:EXT:cooluri/locallang_db.php:pages.tx_cooluri_exclude',
    'exclude' => 1,
    'config' => Array(
        'type' => 'check',
        'default' => '0'
    )
);

$TCA['pages']['columns']['tx_cooluri_excludealways'] = array(
    'label' => 'LLL:EXT:cooluri/locallang_db.php:pages.tx_cooluri_excludealways',
    'exclude' => 1,
    'config' => Array(
        'type' => 'check',
        'default' => '0'
    )
);

$TCA['pages_language_overlay']['columns']['tx_realurl_pathsegment'] = array(
    'label' => 'LLL:EXT:cooluri/locallang_db.php:pages.tx_cooluri_pathsegment',
    'exclude' => 1,
    'config' => Array(
        'type' => 'input',
        'size' => '50',
        'max' => '255',
        'eval' => 'trim,nospace,lower,uniqueInPid'
    )
);

\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages', 'tx_realurl_pathsegment,tx_cooluri_exclude,tx_cooluri_excludealways', '1,2,5,4,254', 'after:nav_title');
\TYPO3\CMS\Core\Utility\ExtensionManagementUtility::addToAllTCAtypes('pages_language_overlay', 'tx_realurl_pathsegment', '1,2,5,4,254', 'after:nav_title');
