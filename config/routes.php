<?php
declare(strict_types=1);

use Cake\Routing\Route\DashedRoute;
use Cake\Routing\RouteBuilder;

return function (RouteBuilder $routes): void {
    $routes->setRouteClass(DashedRoute::class);

    $routes->scope('/', function (RouteBuilder $builder): void {
        $builder->connect('/', [
            'controller' => 'Pages',
            'action' => 'display',
            'home',
        ]);

        $builder->connect('/pages/*', 'Pages::display');

        $builder->scope('/api', function (RouteBuilder $builder): void {
            $builder->setExtensions(['json']);

            $builder->connect('/cep/{cep}', [
                'controller' => 'Cep',
                'action' => 'view',
            ])
                ->setPass(['cep'])
                ->setMethods(['GET']);
        });

        $builder->fallbacks();
    });
};
