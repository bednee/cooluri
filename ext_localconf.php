<?php

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['linkData-PostProc']['cooluri'] = 'Bednarik\\Cooluri\\Integration\\CoolUri->params2cool';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc']['cooluri'] = 'Bednarik\\Cooluri\\Integration\\CoolUri->cool2params';
$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['cooluri'] = 'Bednarik\\Cooluri\\Integration\\CoolUri->goForRedirect';

$GLOBALS['TYPO3_CONF_VARS']['SC_OPTIONS']['t3lib/class.t3lib_befunc.php']['viewOnClickClass']['cooluri'] = 'Bednarik\\Cooluri\\Integration\\BackendUtilityHook';
