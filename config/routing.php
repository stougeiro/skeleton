<?php declare(strict_types=1);

    return
    [
        'caching' => false,

        'base_path' => '/',

        'placeholders' =>
        [
            'any'   => '.*',
            'num'   => '[0-9]+',
            'word'  => '[a-zA-Z]+',
            'slug'  => '[a-z0-9\-]+',
            'hexa'  => '[a-fA-F0-9]+',
            'year'  => '[0-9]{4}',
            'month' => '[0][1-9]|[1][0-2]',
            'day'   => '[0][1-9]|[12][0-9]|[3][01]',
            'uuid'  => '[a-f0-9]{8}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{4}\-[a-f0-9]{12}',
        ],
    ];