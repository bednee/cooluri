<?php
/***************************************************************
 *  Copyright notice
 *
 *  (c) 2011 Jan Bednarik <info@bednarik.org>
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
 *
 *  This script is distributed in the hope that it will be useful,
 *  but WITHOUT ANY WARRANTY; without even the implied warranty of
 *  MERCHANTABILITY or FITNESS FOR A PARTICULAR PURPOSE.  See the
 *  GNU General Public License for more details.
 *
 *  This copyright notice MUST APPEAR in all copies of the script!
 ***************************************************************/

if (!class_exists('tslib_pibase')) {
    require_once(PATH_tslib . 'class.tslib_pibase.php');
}

class tx_cooluritest_pi1 extends tslib_pibase {

    var $URLS = Array(
        Array(22,'&paramD=100',Array()),
        Array(24,'',Array()),
        Array(22,'',Array('paramZ'=>100)),
        Array(22,'',Array('paramZ'=>200)),
        Array(24,'&array[k1]=foo&paramE=removed',Array('array'=>array('k3'=>'foo','k2'=>'bar','k5'=>123345))),
        Array(23,'&L=1',Array('add'=>1)),
        Array(23,'&L=1',Array('add'=>2)),
        Array(25,'&paramD=view-list|page_id-142',Array('paramD'=>'view-list|page_id-142')),
        Array(25,'&atParam[@atValue]=ATVAL',Array('atParam' => array('@atValue' => 'ATVAL'))),
        Array(22,'&paramA=0&paramD=0',Array('paramA'=>'0','paramD'=>'0')),
        Array(25,'&paramX=view-list|page_id-142',Array('paramX'=>'view-list|page_id-142')),
        Array(25,'&paramA=FOO&paramB=6666'),
        Array(22,'&tx_news_pi1[@widget_0][currentPage]=123')
    );

    function main($content, $conf) {
        $GLOBALS['TSFE']->set_no_cache();

        $GLOBALS['TYPO3_DB']->exec_DELETEquery('link_cache','1=1');
        $GLOBALS['TYPO3_DB']->exec_DELETEquery('link_oldlinks','1=1');

        $lt = Link_Translate::getInstance(dirname(__FILE__).'/CoolUriConf.xml');
        $_SESSION['coolUriTransformerInstance'] = $lt;

        $links = Array();
        $links2 = Array();
        $links3 = Array();
        foreach ($this->URLS as $url) {
            $links[] = $this->cObj->typolink_URL(Array('parameter'=>$url[0],'additionalParams'=>$url[1].$this->getToQS($url[2])));
            $t = $this->pi_linkToPage("foo",$url[0],'',$url[2]);
            $links2[] = preg_replace('~.*href="([^"]+)".*~','\\1',$t);
            $links3[] = $this->cObj->typolink('Foo',Array('parameter'=>$url[0],'additionalParams'=>$url[1].$this->getToQS($url[2])));
        }
        $content = implode('<br />',$links);
        $content .= '<br /><br />';
        $content .= implode('<br />',$links2);
        $content .= '<br /><br />';
        $content .= implode('<br />',$links3);

        $params = array();
        foreach ($links as $i=>$l) {

        	if (!empty($GLOBALS['TSFE']->config['config']['absRefPrefix'])) {
        		$l = preg_replace('!^'.$GLOBALS['TSFE']->config['config']['absRefPrefix'].'!','',$l);
        	}

            $cu = new tx_cooluri();
            $p = Array();
            $p['pObj'] = $GLOBALS['TSFE'];
            $_SERVER['REQUEST_URI'] = $l;
            $p['pObj']->siteScript = $l;
            $curGet = $_GET;
            $_GET = $this->URLS[$i][2];
            $cu->cool2params($p,$GLOBALS['TSFE']);
            $r = print_r($_GET,true);
            $params[] = htmlspecialchars($r);
            $_GET = $curGet;
        }
        $content .= '<br /><br />'.implode('<br>',$params);

        return $content;
    }

    function getToQS($p) {
        if (!$p) return '';
        return '&'.http_build_query($p);
    }

}

?>