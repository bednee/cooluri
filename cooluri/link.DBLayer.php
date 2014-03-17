<?php
/**
	This file is part of CoolUri.

    CoolUri is free software: you can redistribute it and/or modify
    it under the terms of the GNU General Public License as published by
    the Free Software Foundation, either version 3 of the License, or
    (at your option) any later version.

    CoolUri is distributed in the hope that it will be useful,
    but WITHOUT ANY WARRANTY; without even the implied warranty of
    MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
    GNU General Public License for more details.

    You should have received a copy of the GNU General Public License
    along with CoolUri. If not, see <http://www.gnu.org/licenses/>.
*/

class Link_DBLayer {

private $conn;
protected static $_instance = null;

protected function __construct() {

}

public static function getInstance() {
  if(!self::$_instance instanceof self){
    self::$_instance = new self();
  }
  return self::$_instance;
}

public function query($stmt) {
  return $GLOBALS['TYPO3_DB']->sql_query($stmt);
}

public function fetch($res) {
  return $GLOBALS['TYPO3_DB']->sql_fetch_assoc($res);
}

public function fetch_row($res) {
  return $GLOBALS['TYPO3_DB']->sql_fetch_row($res);
}

public static function escape($string,$tp = 'link_') {
  return $GLOBALS['TYPO3_DB']->fullQuoteStr($string,$tp.'cache');
}

public function error() {
  return $GLOBALS['TYPO3_DB']->sql_error();
}

public function num_rows($res) {
  return $GLOBALS['TYPO3_DB']->sql_num_rows($res);
}

public function affected_rows() {
  return $GLOBALS['TYPO3_DB']->sql_affected_rows();
}


}

?>
