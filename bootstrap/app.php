<?php

use App\Support\Env;

require_once dirname(__DIR__).'/app/Support/helpers.php';
if (is_file(dirname(__DIR__).'/vendor/autoload.php')) {
    require_once dirname(__DIR__).'/vendor/autoload.php';
} else {
    spl_autoload_register(function (string $class): void {
        $prefixes = [
            'App\\' => dirname(__DIR__).'/app/',
            'Database\\Seeders\\' => dirname(__DIR__).'/database/seeders/',
            'Database\\' => dirname(__DIR__).'/database/',
        ];
        foreach ($prefixes as $prefix => $basePath) {
            if (! str_starts_with($class, $prefix)) {
                continue;
            }
            $file = $basePath.str_replace('\\', '/', substr($class, strlen($prefix))).'.php';
            if (is_file($file)) {
                require $file;
            }
            return;
        }
    });
}
Env::load(dirname(__DIR__).'/.env');
date_default_timezone_set(config('app.timezone', 'UTC'));
