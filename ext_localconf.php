<?php

if (empty($TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['linkData-PostProc']['cooluri'])) {
  $TYPO3_CONF_VARS['SC_OPTIONS']['t3lib/class.t3lib_tstemplate.php']['linkData-PostProc']['cooluri'] = 'EXT:cooluri/class.tx_cooluri.php:&tx_cooluri->params2cool';
}
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['checkAlternativeIdMethods-PostProc']['cooluri'] = 'EXT:cooluri/class.tx_cooluri.php:&tx_cooluri->cool2params';
$TYPO3_CONF_VARS['SC_OPTIONS']['tslib/class.tslib_fe.php']['configArrayPostProc']['cooluri'] = 'EXT:cooluri/class.tx_cooluri.php:&tx_cooluri->goForRedirect';

?>
