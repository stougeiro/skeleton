<?php declare(strict_types=1);

use Sourcerer\Http\Middlewares;
use STDW\Container\Container;

use Tests\Support\Middleware\ControllerMiddleware;
use Tests\Support\Middleware\GlobalMiddleware;
use Tests\Support\Middleware\ModuleMiddleware;
use Tests\Support\Module\Deep\DeepModule;
use Tests\Support\Module\FakeModule;

it('composes the globals, the module level and the controller level in order', function () {
    $middlewares = new Middlewares(new Container(), [GlobalMiddleware::class]);
    $middlewares->load(FakeModule::class, fixture('middlewares.php'));

    expect(classes($middlewares->globals()))->toBe([GlobalMiddleware::class])
        ->and(classes($middlewares->forController('Tests\Support\Module\Controller\FakeController')))
        ->toBe([ModuleMiddleware::class, ControllerMiddleware::class]);
});

it('applies the module level to every controller of the module', function () {
    $middlewares = new Middlewares(new Container());
    $middlewares->load(FakeModule::class, fixture('middlewares.php'));

    expect(classes($middlewares->forController('Tests\Support\Module\Controller\OtherController')))
        ->toBe([ModuleMiddleware::class]);
});

it('returns nothing for a controller outside any module', function () {
    $middlewares = new Middlewares(new Container());
    $middlewares->load(FakeModule::class, fixture('middlewares.php'));

    expect($middlewares->forController('Vendor\Package\Controller\SomeController'))->toBe([]);
});

it('uses the most specific module namespace', function () {
    $middlewares = new Middlewares(new Container());
    $middlewares->load(FakeModule::class, fixture('middlewares.php'));
    $middlewares->load(DeepModule::class, fixture('deep-middlewares.php'));

    expect(classes($middlewares->forController('Tests\Support\Module\Deep\Controller\XController')))
        ->toBe([ControllerMiddleware::class])
        ->and(classes($middlewares->forController('Tests\Support\Module\Controller\FakeController')))
        ->toBe([ModuleMiddleware::class, ControllerMiddleware::class]);
});

it('runs a middleware once when declared at more than one level', function () {
    $middlewares = new Middlewares(new Container());
    $middlewares->load(FakeModule::class, fixture('duplicated-middlewares.php'));

    expect(classes($middlewares->forController('Tests\Support\Module\Controller\FakeController')))
        ->toBe([ModuleMiddleware::class]);
});

it('throws when the file does not exist', function () {
    $middlewares = new Middlewares(new Container());

    $middlewares->load(FakeModule::class, fixture('missing.php'));
})->throws(RuntimeException::class);

it('throws when the file does not return a map', function () {
    $middlewares = new Middlewares(new Container());

    $middlewares->load(FakeModule::class, fixture('not-a-map.php'));
})->throws(RuntimeException::class);
