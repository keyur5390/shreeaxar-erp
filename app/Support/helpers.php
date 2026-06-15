<?php

use App\Support\Auth;
use App\Support\Csrf;
use App\Support\Flash;
use App\Support\View;

function app_path(string $path = ''): string { return dirname(__DIR__, 2).($path ? DIRECTORY_SEPARATOR.$path : ''); }
function config(string $key, mixed $default = null): mixed { static $config=[]; [$file,$item]=array_pad(explode('.', $key, 2),2,null); if(!isset($config[$file])) $config[$file]=require app_path('config/'.$file.'.php'); return $item ? ($config[$file][$item] ?? $default) : $config[$file]; }
function view(string $template, array $data = []): string { return View::render($template, $data); }
function redirect(string $path): never { header('Location: '.$path); exit; }
function e(mixed $value): string { return htmlspecialchars((string)$value, ENT_QUOTES, 'UTF-8'); }
function csrf_field(): string { return '<input type="hidden" name="_token" value="'.e(Csrf::token()).'">'; }
function old(string $key, mixed $default = ''): mixed { return $_SESSION['_old'][$key] ?? $default; }
function flash(string $key, ?string $value = null): ?string { return $value === null ? Flash::get($key) : (Flash::set($key, $value) ?? null); }
function user(): ?array { return Auth::user(); }
function can(string $permission): bool { return Auth::can($permission); }
function money(mixed $value): string { return number_format((float)$value, 2); }
