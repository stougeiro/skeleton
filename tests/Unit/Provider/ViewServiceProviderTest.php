<?php declare(strict_types=1);

use App\Provider\ViewServiceProvider;
use STDW\Container\Container;
use STDW\Contract\View\AliasManagerInterface;
use STDW\Contract\View\ViewManagerInterface;

it('binds the view manager and the alias capability to the same instance', function () {
    $container = new Container();

    $provider = new ViewServiceProvider($container);
    $provider->register();
    $provider->boot();

    $view = $container->get(ViewManagerInterface::class);

    expect($view)->toBeInstanceOf(ViewManagerInterface::class)
        ->and($container->get(AliasManagerInterface::class))->toBe($view);
});
