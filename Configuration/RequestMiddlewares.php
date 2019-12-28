<?php
/**
 * An array consisting of implementations of middlewares for a middleware stack to be registered
 *
 *  'stackname' => [
 *      'middleware-identifier' => [
 *         'target' => classname or callable
 *         'before/after' => array of dependencies
 *      ]
 *   ]
 */
return [
    'frontend' => [
        'typo3/cms-frontend/page-resolver' => [
            'target' => \Bednarik\Cooluri\Integration\CoolPageResolver::class,
            'after' => [
                'typo3/cms-frontend/tsfe',
                'typo3/cms-frontend/authentication',
                'typo3/cms-frontend/backend-user-authentication',
                'typo3/cms-frontend/site',
            ]
        ],
    ]
];
