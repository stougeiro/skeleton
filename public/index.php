<?php declare(strict_types=1);

use Sourcerer\Http\Kernel;
use STDW\Http\Request;

require_once __DIR__ . '/../bootstrap/app.php';


$kernel = Kernel::create();

$kernel->handle(new Request())->send();
$kernel->terminate();
