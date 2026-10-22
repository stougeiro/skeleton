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

it('renders the hello world module route', function () {
    $kernel = Kernel::create();
    $kernel->boot();

    $response = $kernel->handle(new Request());

    expect($response->getStatus())->toBe(200)
        ->and($response->getBody())->toContain('Hello World');
});

it('answers 404 for an unknown route', function () {
    $_SERVER['REQUEST_URI'] = '/missing';

    $kernel = Kernel::create();
    $kernel->boot();

    $response = $kernel->handle(new Request());

    expect($response->getStatus())->toBe(404);
});

it('answers 405 when the controller does not serve the verb', function () {
    $_SERVER['REQUEST_METHOD'] = 'POST';

    $kernel = Kernel::create();
    $kernel->boot();

    $response = $kernel->handle(new Request());

    expect($response->getStatus())->toBe(405);
});
