<?php declare(strict_types=1);

use Tests\Support\Middleware\ModuleMiddleware;


return [
    '*' => [
        ModuleMiddleware::class,
    ],

    'Tests\Support\Module\Controller\FakeController' => [
        ModuleMiddleware::class,
    ],
];
