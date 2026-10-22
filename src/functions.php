<?php declare(strict_types=1);


/**
 * Reads a dot-notated item from the environment file at the project root.
 *
 * @param string $path Dot-notated key (e.g. "app.name").
 * @return mixed
 */
function env(string $path): mixed
{
    static $items = null;

    if ($items === null) {
        $file = ROOT . 'env.php';

        if ( ! file_exists($file)) {
            throw new \RuntimeException("env: file '{$file}' not found");
        }

        $items = include $file;
    }

    $item = $items;

    foreach (explode('.', $path) as $segment) {
        if ( ! is_array($item) || ! array_key_exists($segment, $item)) {
            throw new \RuntimeException("env: item '{$path}' not found");
        }

        $item = $item[$segment];
    }

    return $item;
}


/**
 * Reads a dot-notated item from a configuration file in config/.
 *
 * The first segment is the file name; the remaining segments walk the
 * returned array. When a default is given, a missing file or key answers
 * it instead of throwing.
 *
 * @param string $path Dot-notated key (e.g. "app.providers").
 * @param mixed $default Value returned when the file or key is missing.
 * @return mixed
 */
function config(string $path, mixed $default = null): mixed
{
    static $items = [];

    $hasDefault = func_num_args() > 1;
    $segments = explode('.', $path);
    $filename = $segments[0];
    $file = CONFIG . $filename . '.php';

    if ( ! isset($items[$filename])) {
        if ( ! file_exists($file)) {
            if ($hasDefault) {
                return $default;
            }

            throw new \RuntimeException("config: file '{$file}' not found");
        }

        $items[$filename] = include $file;
    }

    $item = $items[$filename];

    foreach (array_slice($segments, 1) as $segment) {
        if ( ! is_array($item) || ! array_key_exists($segment, $item)) {
            if ($hasDefault) {
                return $default;
            }

            throw new \RuntimeException("config: item '{$path}' not found");
        }

        $item = $item[$segment];
    }

    return $item;
}

function secret(string $key, ?string $fallback = null): string
{
    $path = '/run/secrets/'. $key;

    if ( ! file_exists($path)) {
        return $fallback ?? throw new \RuntimeException("secret: '{$key}' not found");
    }

    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new \RuntimeException("secret: unable to read '{$key}'");
    }

    return trim($contents);
}
