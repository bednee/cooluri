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

class DB extends DBLayer {
  
  public function __construct() {

  }
  
  public static function getInstance() {
    if(!parent::$_instance instanceof self){
      parent::$_instance = new self();
    }
    return parent::$_instance;
  }
  
}
