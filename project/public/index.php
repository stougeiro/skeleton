<?php declare(strict_types=1);

$start = hrtime(true);

require_once __DIR__ . '/../bootstrap/app.php';

use Sourcerer\Http\Kernel;
use STDW\Http\Request;


$kernel = Kernel::create();

$kernel->handle(new Request())->send();
$kernel->terminate();

$end = hrtime(true) - $start;
$duration = $end / 1e6;

echo "<span> PHP Duration: ". $duration ."ms</span>"; 