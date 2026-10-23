<?php declare(strict_types=1);


return
[
    'environment' => config('app.environment', 'development'),

    'mode' => 'html', // html | json

    'views' => [
        400 => 'error:badrequest',
        403 => 'error:forbidden',
        404 => 'error:notfound',
        405 => 'error:notallowed',
        429 => 'error:manyrequests',
        500 => 'error:internal',
    ],

    'types' => [],

    'development' => [
        'view' => 'error:development',
        'snippet' => 10,
    ],

    'log' => [
        'path' => LOG .'error.log',
        'trace_from' => 500,
    ],
];
