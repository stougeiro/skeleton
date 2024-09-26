<?php declare(strict_types=1);

    defined('__DIR__') or define('__DIR__', dirname(__FILE__));

    define('DS', DIRECTORY_SEPARATOR);
    define('ROOT', __DIR__.DS.'..'.DS);
    define('APP', ROOT.'app'.DS);
    define('CONFIG', ROOT.'config'.DS);
    define('STORAGE', ROOT.'storage'.DS);

    include ROOT.'vendor/autoload.php';


    use STDW\Container\Container;
    use STDW\Contract\AppAbstracted;
    use STDW\App\App;


    $container = new Container();
    $container->set(AppAbstracted::class, App::class, true);

    return $container->get(AppAbstracted::class);