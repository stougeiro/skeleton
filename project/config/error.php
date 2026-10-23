<?php declare(strict_types=1);


return
[
    'mode' => 'html', // html | json

    'development' => [
        'view' => 'error:development',
        'snippet' => 10,
    ],

    'log' => [
        'path' => LOG .'error.log',
        'trace_from' => 500,
    ],
];
