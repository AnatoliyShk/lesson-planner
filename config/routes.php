<?php
/**
 * Routes configuration.
 *
 * In this file, you set up routes to your controllers and their actions.
 * Routes are very important mechanism that allows you to freely connect
 * different URLs to chosen controllers and their actions (functions).
 *
 * It's loaded within the context of `Application::routes()` method which
 * receives a `RouteBuilder` instance `$routes` as method argument.
 *
 * CakePHP(tm) : Rapid Development Framework (https://cakephp.org)
 * Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 *
 * Licensed under The MIT License
 * For full copyright and license information, please see the LICENSE.txt
 * Redistributions of files must retain the above copyright notice.
 *
 * @copyright     Copyright (c) Cake Software Foundation, Inc. (https://cakefoundation.org)
 * @link          https://cakephp.org CakePHP(tm) Project
 * @license       https://opensource.org/licenses/mit-license.php MIT License
 */

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {

        $builder->connect('/', ['controller' => 'Home', 'action' => 'index']);

        $builder->connect('/pages/*', 'Pages::display');

        $builder->scope('/', ['controller' => 'Users'], function (RouteBuilder $builder): void {
            $builder->connect('/login', ['action' => 'login'], ['_name' => 'login']);
            $builder->connect('/logout', ['action' => 'logout'], ['_name' => 'logout']);
            $builder->connect('/register', ['action' => 'register'], ['_name' => 'register']);
            $builder->connect('/profile', ['action' => 'profile'], ['_name' => 'profile']);
            $builder->connect('/change-password', ['action' => 'changePassword']);
            $builder->connect('/forgot-password', ['action' => 'forgotPassword'], ['_name' => 'forgotPassword']);

            $builder->connect('/reset-password/{token}', ['action' => 'resetPassword'])
                ->setPass(['token'])
                ->setPatterns(['token' => '[a-f0-9]{64}']);
        });

        $builder->fallbacks();
    });

    /*
     * If you need a different set of middleware or none at all,
     * open new scope and define routes there.
     *
     * ```
     * $routes->scope('/api', function (RouteBuilder $builder): void {
     *     // No $builder->applyMiddleware() here.
     *
     *     // Parse specified extensions from URLs
     *     // $builder->setExtensions(['json', 'xml']);
     *
     *     // Connect API actions here.
     * });
     * ```
     */
};
