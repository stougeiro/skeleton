<?php declare(strict_types=1);

use Tests\Support\Middleware\ControllerMiddleware;


return [
    '*' => [
        ControllerMiddleware::class,
    ],
];
