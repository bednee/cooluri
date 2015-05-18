<?php
namespace Bednarik\Cooluri\Controller;

/***************************************************************
 *
 *  Copyright notice
 *
 *  (c) 2015 Jan Bednarik <info@bednarik.org>, Bednarik.org
 *
 *  All rights reserved
 *
 *  This script is part of the TYPO3 project. The TYPO3 project is
 *  free software; you can redistribute it and/or modify
 *  it under the terms of the GNU General Public License as published by
 *  the Free Software Foundation; either version 3 of the License, or
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

/**
 * CodeController
 */
class LinkFixController extends \TYPO3\CMS\Extbase\Mvc\Controller\ActionController {

    /**
     * @var \TYPO3\CMS\Core\DataHandling\DataHandler
     */
    protected $dataHandler;

    /**
     * @param \TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler
     *
     * @return void
     */
    public function injectDataHandler(\TYPO3\CMS\Core\DataHandling\DataHandler $dataHandler) {
        $this->dataHandler = $dataHandler;
    }

    /**
     * @param string $url
     */
    public function listAction($url = NULL) {
        $this->view->assign('url',$url);

        if (!empty($url)) {
            $urls = $this->getUrls($url);
            foreach ($urls as $u) {
                $u['parameters'] = http_build_query (unserialize($u['params']));
            }
            $this->view->assign('urls', $urls);
        }
    }

    /**
     * @param int $id
     */
    public function deleteAction($id) {
        $GLOBALS['TYPO3_DB']->exec_DELETEquery('link_cache','id = '.(int)$id);
        $this->dataHandler->start(NULL,NULL);
        $this->dataHandler->clear_cacheCmd('all');
        $this->addFlashMessage('A link has been removed from cache, please reload page where the link is present (not the page itself, but e.g. parent page with this link in menu) in order to generate it again.');
        $this->redirect('list');
    }

    private function getUrls($url) {
        if (!empty($url)) {
            $parsedUrl = parse_url($url);
            if ($parsedUrl) {
                $parsedUrl['path'] = $GLOBALS['TYPO3_DB']->quoteStr($parsedUrl['path'],null);
                $parsedUrl['host'] = $GLOBALS['TYPO3_DB']->quoteStr($parsedUrl['host'],null);
                $possibleMatches = Array();
                $possibleMatches[] = $parsedUrl['path'];
                $possibleMatches[] = $parsedUrl['host'].'@'.$parsedUrl['path'];
                if (substr($parsedUrl['path'],-1) == '/') {
                    $possibleMatches[] = substr($parsedUrl['path'],0,strlen($parsedUrl['path'])-1);
                    $possibleMatches[] = $parsedUrl['host'].'@'.substr($parsedUrl['path'],0,strlen($parsedUrl['path'])-1);
                }
                if (substr($parsedUrl['path'],0,1) == '/') {
                    $possibleMatches[] = substr($parsedUrl['path'],1,strlen($parsedUrl['path']));
                    $possibleMatches[] = $parsedUrl['host'].'@'.substr($parsedUrl['path'],1,strlen($parsedUrl['path']));
                }
                if (substr($parsedUrl['path'],-1) == '/' && substr($parsedUrl['path'],0,1) == '/') {
                    $possibleMatches[] = substr($parsedUrl['path'],1,strlen($parsedUrl['path'])-1);
                    $possibleMatches[] = $parsedUrl['host'].'@'.substr($parsedUrl['path'],1,strlen($parsedUrl['path'])-1);
                }
                $where = "(url = '".implode("' OR url='",$possibleMatches)."')";
                return $GLOBALS['TYPO3_DB']->exec_SELECTgetRows('*','link_cache',$where);
            }
        }
        return false;
    }
}