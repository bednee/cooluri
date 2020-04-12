<?php
namespace Bednarik\Cooluri\Integration;

/***************************************************************
 *  Copyright notice
 *
 *  (c) 2019 Jan Bednarik <info@bednarik.org>
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


class CoolPageResolver extends \TYPO3\CMS\Frontend\Middleware\PageResolver
{

    public function process(\Psr\Http\Message\ServerRequestInterface $request, \Psr\Http\Server\RequestHandlerInterface $handler): \Psr\Http\Message\ResponseInterface {
        $parameters = CoolUri::cool2params();
        if ($parameters === false) {
            return parent::process($request, $handler);
        }

        $pageArguments = new \TYPO3\CMS\Core\Routing\PageArguments(
            $parameters['id'],
            (string)($parameters['type'] ?? '0'),
            $parameters,
            [],
            $request->getQueryParams()
        );

        $this->controller->id = $pageArguments->getPageId();
        $this->controller->type = $pageArguments->getPageType() ?? $this->controller->type;
        $this->controller->cHash = $parameters['cHash'];

        // merge the PageArguments with the request query parameters
        $queryParams = array_replace_recursive($request->getQueryParams(), $pageArguments->getArguments());
        $request = $request->withQueryParams($queryParams);
        $this->controller->setPageArguments($pageArguments);

        // At this point, we later get further route modifiers
        // for bw-compat we update $GLOBALS[TYPO3_REQUEST] to be used later in TSFE.
        $GLOBALS['TYPO3_REQUEST'] = $request;

        $this->controller->determineId();

        // No access? Then remove user & Re-evaluate the page-id
        if ($this->controller->isBackendUserLoggedIn() && !$GLOBALS['BE_USER']->doesUserHaveAccess($this->controller->page, \TYPO3\CMS\Core\Type\Bitmask\Permission::PAGE_SHOW)) {
            unset($GLOBALS['BE_USER']);
            // Register an empty backend user as aspect
            $this->setBackendUserAspect(\TYPO3\CMS\Core\Utility\GeneralUtility::makeInstance(\TYPO3\CMS\Core\Context\Context::class), null);
            $this->controller->determineId();
        }

        return $handler->handle($request);
    }
}
