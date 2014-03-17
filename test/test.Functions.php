<?php
/**
 *  Copyright notice
 *
 *  (c) 2012 Jan Bednarik (info@bednarik.org)
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
 *  A copy is found in the textfile GPL.txt and important notices to the license
 *  from the author is found in LICENSE.txt distributed with these scripts.
 *
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

/**
 * test.Functions.php
 */

include '../cooluri/link.Functions.php';

// test lookindb
$sql = 'SELECT title FROM tx_news_domain_model_news WHERE deleted=\'0\' AND hidden=\'0\' AND (uid=$1 OR l10n_parent=$1) AND sys_language_uid={L=0}';
$param = '70';

Link_Func::lookindb($sql,$param, new stdClass(), Array('L'=>'2'));

class Link_DB {
    public static function getInstance() {
        return new Link_DB();
    }
    public static function query($sql) {
        echo($sql);
        return $sql;
    }
    public static function fetch_row($res) {
        return $res;
    }
    public static function escape($str) {
        return '\'' . mysql_real_escape_string($str) . '\'';
    }
}

?>
