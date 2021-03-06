<?php
if (!defined('TYPO3_MODE')) {
    die('Access denied.');
}

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['linkData-PostProc']['cooluri'] = 'Bednarik\\Cooluri\\Integration\\CoolUri->params2cool';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['cooluri'] = 'Bednarik\\Cooluri\\Integration\\CoolUri->goForRedirect';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['viewOnClickClass']['cooluri'] = 'Bednarik\\Cooluri\\Integration\\BackendUtilityHook';

// registering scheduler task
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Bednarik\\Cooluri\\Task\\ForceUpdateTask'] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_cool1.xlf:mlang_scheduler_force',
    'description' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_cool1.xlf:mlang_scheduler_force_desc'
);
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['scheduler']['tasks']['Bednarik\\Cooluri\\Task\\DeleteAllTask'] = array(
    'extension' => $_EXTKEY,
    'title' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_cool1.xlf:mlang_scheduler_delete',
    'description' => 'LLL:EXT:' . $_EXTKEY . '/Resources/Private/Language/locallang_cool1.xlf:mlang_scheduler_delete_desc'
);
