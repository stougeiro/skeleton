<?php declare(strict_types=1);

uses(PHPUnit\Framework\TestCase::class)->in(__DIR__ . '/Unit', __DIR__ . '/Feature');


/**
 * Absolute path of a file under tests/Support/Fixtures.
 *
 * @param string $name
 * @return string
 */
function fixture(string $name): string
{
    return __DIR__ . '/Support/Fixtures/' . $name;
}


/**
 * The class names of the given instances, in order.
 *
 * @param array<int, object> $instances
 * @return array<int, string>
 */
function classes(array $instances): array
{
    return array_map(static fn (object $instance): string => $instance::class, $instances);
}
