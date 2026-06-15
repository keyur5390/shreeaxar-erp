<?php

use App\Support\Env;

require_once dirname(__DIR__).'/app/Support/helpers.php';
if (is_file(dirname(__DIR__).'/vendor/autoload.php')) {
    require_once dirname(__DIR__).'/vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        $prefix = 'App\\';
        if (! str_starts_with($class, $prefix)) return;
        $file = dirname(__DIR__).'/app/'.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
        if (is_file($file)) require $file;
    });
}
Env::load(dirname(__DIR__).'/.env');
date_default_timezone_set(config('app.timezone', 'UTC'));
