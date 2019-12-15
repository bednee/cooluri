<?php
namespace Bednarik\Cooluri\Core;
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

class DBLayer
{

    /** @var \Doctrine\DBAL\Connection */
    private $conn;
    protected static $_instance = null;


    protected function __construct()
    {

    }

    public static function getInstance()
    {
        if (!self::$_instance instanceof self) {
            self::$_instance = new self();
            $pool = \TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Database\ConnectionPool::class);
            self::$_instance->conn = $pool->getConnectionForTable('link_cache');
        }
        return self::$_instance;
    }

    public function query($stmt)
    {
        return $this->conn->query($stmt);
    }

    public function fetch(\Doctrine\DBAL\Driver\Statement $res)
    {
        return $res->fetch(\Doctrine\DBAL\FetchMode::ASSOCIATIVE);
    }

    public function fetch_row(\Doctrine\DBAL\Driver\Statement $res)
    {
        return $res->fetch(\Doctrine\DBAL\FetchMode::ASSOCIATIVE);
    }

    public function escape($string, $tp = 'link_')
    {
        return $this->conn->quote($string);
    }

    public function quoteIdentifier($string)
    {
        return $this->conn->quoteIdentifier($string);
    }

    public function error()
    {
        return $this->conn->errorCode();
    }

    public function num_rows(\Doctrine\DBAL\Driver\Statement $res)
    {
        return $res->rowCount();
    }

    public function affected_rows(\Doctrine\DBAL\Driver\Statement $res)
    {
        return $res->rowCount();
    }

}
