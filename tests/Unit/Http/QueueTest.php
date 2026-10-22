<?php declare(strict_types=1);

use STDW\Http\Middleware\MiddlewareManager;
use STDW\Http\Request;
use STDW\Http\Response;

use Tests\Support\Middleware\EarlyMiddleware;
use Tests\Support\Middleware\FinalMiddleware;
use Tests\Support\Middleware\GlobalMiddleware;

use function Sourcerer\merge;

beforeEach(function () {
    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['REQUEST_URI'] = '/';
});

it('stops the queue on an early return and carries the accumulated state into the new response', function () {
    $accumulated = new Response();

    $manager = new MiddlewareManager();
    $manager->add(new GlobalMiddleware());
    $manager->add(new EarlyMiddleware());
    $manager->setFinalHandler(new FinalMiddleware());

    $result = merge($accumulated, $manager->handle(new Request(), $accumulated));

    expect($result->getStatus())->toBe(403)
        ->and($result->getHeader('X-Early'))->toBe('1')
        ->and($result->getHeader('X-Chain'))->toBe('global')
        ->and($result->getHeader('X-Final'))->toBeNull();
});
