<?php declare(strict_types=1);


    // general

    if ( ! function_exists('slugify'))
    {
        function slugify(string $text): string
        {
            // replace non letter or digits by -
            $text = preg_replace('~[^\pL\d]+~u', '-', $text);
            // transliterate
            $text = iconv('utf-8', 'us-ascii//TRANSLIT', $text);
            // remove unwanted characters
            $text = preg_replace('~[^-\w]+~', '', $text);
            // trim
            $text = trim($text, '-');
            // remove duplicate -
            $text = preg_replace('~-+~', '-', $text);
            // lowercase
            $text = strtolower($text);

            return $text;
        }
    }

    if ( ! function_exists('filter_object'))
    {
        function filter_object(false|object|null $object, array $properties): false|object
        {
            $new = new stdClass;

            if ( ! is_object($object)) {
                return false;
            }

            foreach ($properties as $property) {
                if ( ! property_exists($object, $property)) {
                    return false;
                }

                $new->{$property} = $object->{$property};
            }

            return $new;
        }
    }

    if ( ! function_exists('date_convert'))
    {
        function date_convert(?string $date, string $from_format = 'Y-m-d', $to_format = 'd/m/Y'): string
        {
            if (empty($date)) {
                return '';
            }

            return date_format( date_create_from_format($from_format, $date), $to_format);
        }
    }

    // request

    if ( ! function_exists('secure_var'))
    {
        function secure_var(mixed &$var): mixed
        {
            if (is_array($var)) {
                foreach ($var as $key => $item) {
                    $var[$key] = secure_var($item);
                }

                return $var;
            }

            return htmlspecialchars( strip_tags($var), ENT_NOQUOTES, 'UTF-8');;
        }
    }

    if ( ! function_exists('get'))
    {
        function get(string $var, mixed $default = null): mixed
        {
            if ( ! isset($_GET[$var])) {
                return $default;
            }

            return secure_var($_GET[$var]);
        }
    }

    if ( ! function_exists('post'))
    {
        function post(string $var, mixed $default = null): mixed
        {
            if ( ! isset($_POST[$var])) {
                return $default;
            }

            return secure_var($_POST[$var]);
        }
    }

    if ( ! function_exists('request_uri'))
    {
        function request_uri(): string
        {
            $uri = $_SERVER['REQUEST_URI'];
            $url = parse_url($uri, PHP_URL_PATH);
    
            if (false === $url) {
                return $uri;
            }
    
            $url = explode('/', $url);
    
            if (isset($url[0]) && $url[0] == '') {
                array_shift($url);
            }
    
            $last = count($url) - 1;
    
            if (isset($url[$last]) && $url[$last] == '') {
                array_pop($url);
            }
    
            return '/'. implode('/', $url) . (count($url) ? '/' : '');
        }
    }

    if ( ! function_exists('request_method'))
    {
        function request_method(): string
        {
            $method = strtolower($_SERVER['REQUEST_METHOD']);
    
            if ($method == 'post') {
                $spoofing_variable = '_method_';
                $spoofed_method = post($spoofing_variable);
    
                $method = ($spoofed_method) ? strtolower($spoofed_method) : $method;
            }
    
            return $method;
        }
    }

    if ( ! function_exists('redirect'))
    {
        function redirect(string $location): void
        {
            header("Location: $location");
            exit();
        }
    }

    if ( ! function_exists('uploadedfile'))
    {
        function uploadedfile(string $key)
        {
            $input = isset($_FILES[$key]) ? $_FILES[$key] : null;

            return $input;
        }
    }

    // paths

    if ( ! function_exists('absolute_path'))
    {
        function absolute_path(string $path): string
        {
            $path = str_replace(['/', '\\'], DS, $path);
            $parts = array_filter( explode(DS, $path), 'strlen');
            $absolutes = [];

            foreach ($parts as $part) {
                if ('.' == $part) {
                    continue;
                }

                if ('..' == $part) {
                    array_pop($absolutes);
                } else {
                    $absolutes[] = $part;
                }
            }

            return DS . implode(DS, $absolutes) . DS;
        }
    }

    if ( ! function_exists('app_path'))
    {
        function app_path(string $path): string
        {
            return absolute_path(APP . $path);
        }
    }

    if ( ! function_exists('public_path'))
    {
        function public_path(string $path): string
        {
            return absolute_path(ROOT .'public'. DS . $path);
        }
    }

    if ( ! function_exists('storage_path'))
    {
        function storage_path(string $path): string
        {
            return absolute_path(STORAGE . $path);
        }
    }

    // debug

    if ( ! function_exists('debug'))
    {
        function debug(mixed $item, bool $dump = false, bool $exit = false): void
        {
            echo "<pre>";

                if (is_array($item) || is_object($item)) {
                    print_r($item);
                } else if (is_bool($item)) {
                    echo $item ? 'true' : 'false';
                } else {
                    echo $item;
                }

                if ($dump) {
                    var_dump($item);
                }

            echo "</pre>";

            if ($exit) {
                exit();
            }
        }
    }