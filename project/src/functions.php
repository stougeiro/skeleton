<?php declare(strict_types=1);


/**
 * Reads a dot-notated item from the environment file at the project root.
 *
 * The file is read once per request. The path is resolved with realpath(),
 * whose result is cached per process, so repeated calls cost no extra stat.
 * When a default is given, a missing file or key answers it instead of
 * throwing.
 *
 * @param string $path Dot-notated key (e.g. "app.name").
 * @param mixed $default Value returned when the file or key is missing.
 * @return mixed
 */
function env(string $path, mixed $default = null): mixed
{
    static $items = null;

    $hasDefault = func_num_args() > 1;

    if ($items === null) {
        $file = realpath(ROOT .'env.php');

        if ($file === false) {
            if ($hasDefault) {
                return $default;
            }

            throw new \RuntimeException("env: file 'env.php' not found");
        }

        $items = include $file;
    }

    $item = $items;

    foreach (explode('.', $path) as $segment) {
        if ( ! is_array($item) || ! array_key_exists($segment, $item)) {
            if ($hasDefault) {
                return $default;
            }

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
 * Each file is read once per request; realpath() resolves and caches the
 * path per process, so the file lookup costs no extra stat after the first
 * call.
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

    if ( ! isset($items[$filename])) {
        $file = realpath(CONFIG . $filename .'.php');

        if ($file === false) {
            if ($hasDefault) {
                return $default;
            }

            throw new \RuntimeException("config: file 'config/{$filename}.php' not found");
        }

        $items[$filename] = include $file;
    }

    $item = $items[$filename];

    for ($index = 1, $count = count($segments); $index < $count; $index++) {
        $segment = $segments[$index];

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

/**
 * Reads a secret from the container's secret mount (/run/secrets/<key>).
 *
 * Docker secrets are files, not variables, and the mount usually ends with a
 * trailing newline, so the contents are returned trimmed.
 *
 * A missing file answers the fallback when one is given; without a fallback it
 * throws. A file that exists but cannot be read always throws, so a permission
 * problem is never silently mistaken for a missing secret.
 *
 * Successful reads are cached per request: a secret used by several services is
 * read from disk only once.
 *
 * @param string $key Secret name (the file name inside /run/secrets).
 * @param string|null $fallback Value returned when the secret file does not exist.
 * @return string
 * @throws \RuntimeException When the secret is missing and no fallback is given,
 *                           or when the secret exists but cannot be read.
 */
function secret(string $key, ?string $fallback = null): string
{
    static $items = [];

    if (isset($items[$key])) {
        return $items[$key];
    }

    $path = realpath('/run/secrets/' . $key);

    if ($path === false) {
        return $fallback ?? throw new \RuntimeException("secret: '{$key}' not found");
    }

    $contents = file_get_contents($path);

    if ($contents === false) {
        throw new \RuntimeException("secret: unable to read '{$key}'");
    }

    return $items[$key] = trim($contents);
}
