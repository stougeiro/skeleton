<?php declare(strict_types=1);

use Sourcerer\Http\Kernel;
use STDW\Http\Request;

beforeEach(function () {
    unset($_SERVER['HTTP_COOKIE']);

    $_SERVER['REQUEST_METHOD'] = 'GET';
    $_SERVER['HTTP_HOST'] = 'localhost';
    $_SERVER['SERVER_NAME'] = 'localhost';
    $_SERVER['REQUEST_URI'] = '/';
});

it('accumulates the module and controller middlewares in order', function () {
    $kernel = Kernel::create();
    $kernel->boot();

    $response = $kernel->handle(new Request());

    expect($response->getStatus())->toBe(200)
        ->and($response->getBody())->toContain('Hello World')
        ->and($response->getHeader('X-Chain'))->toBe('module controller');
});

it('carries the accumulated state into a response created as a shortcut', function () {
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $kernel = Kernel::create();
    $kernel->boot();

    $response = $kernel->handle(new Request());

    expect($response->getStatus())->toBe(405)
        ->and($response->getHeader('X-Chain'))->toBe('module controller');
});
