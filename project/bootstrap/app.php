<?php declare(strict_types=1);

date_default_timezone_set('America/Sao_Paulo');

const ROOT = __DIR__ . '/../';
const APP = ROOT . 'app/';
const CONFIG = ROOT . 'config/';
const STORAGE = ROOT . 'storage/';
const CACHE = STORAGE . 'cache/';
const LOG = STORAGE . 'log/';
const SESSION = STORAGE . 'session/';
const TEMP = STORAGE . 'temp/';

require_once ROOT . 'vendor/autoload.php';
