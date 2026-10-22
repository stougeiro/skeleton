<?php declare(strict_types=1);

use STDW\Http\Response;
use STDW\Http\Response\TextResponse;

use function Sourcerer\merge;

it('carries the accumulated headers and cookies into the result', function () {
    $accumulated = (new Response())
        ->withHeader('X-Trace', 'abc')
        ->withCookie('session', 'xyz');

    $result = new TextResponse(201, ['X-Result' => '1'], 'Created');

    $merged = merge($accumulated, $result);

    expect($merged)->toBe($result)
        ->and($merged->getStatus())->toBe(201)
        ->and($merged->getBody())->toBe('Created')
        ->and($merged->getHeader('X-Trace'))->toBe('abc')
        ->and($merged->getHeader('X-Result'))->toBe('1')
        ->and($merged->getCookie('session'))->not->toBeNull();
});

it('keeps the result header when both define the same name', function () {
    $accumulated = (new Response())->withHeader('X-Who', 'middleware');

    $result = new TextResponse(200, ['X-Who' => 'controller'], 'ok');

    expect(merge($accumulated, $result)->getHeader('X-Who'))->toBe('controller');
});

it('returns the same instance when the result is the accumulated response', function () {
    $accumulated = (new Response())->withHeader('X-Trace', 'abc');

    expect(merge($accumulated, $accumulated))->toBe($accumulated);
});
