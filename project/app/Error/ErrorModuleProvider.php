<?php declare(strict_types=1);

namespace App\Error;

use Sourcerer\Module\ModuleProviderAbstracted;


final class ErrorModuleProvider extends ModuleProviderAbstracted
{
    public function configure(): void
    {
        $this->views('error', __DIR__ . '/View');
    }
}
