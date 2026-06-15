<?php

use App\Core\Router;
use App\Support\Csrf;
use App\Controllers\ActivityLogController;
use App\Controllers\AuthController;
use App\Controllers\CompanySettingsController;
use App\Controllers\CustomerController;
use App\Controllers\DashboardController;
use App\Controllers\MasterController;
use App\Controllers\ProductController;
use App\Controllers\QuotationController;
use App\Controllers\ReportController;
use App\Controllers\RoleController;
use App\Controllers\UserController;

require dirname(__DIR__).'/bootstrap/app.php';
session_start();
Csrf::verify();
$router = new Router();
$router->get('/', fn() => redirect('/dashboard'));
$router->get('/login', [AuthController::class, 'showLogin']);
$router->post('/login', [AuthController::class, 'login']);
$router->post('/logout', [AuthController::class, 'logout']);
$router->get('/dashboard', [DashboardController::class, 'index']);
foreach ([['roles', RoleController::class], ['users', UserController::class], ['customers', CustomerController::class], ['products', ProductController::class], ['quotations', QuotationController::class]] as [$name, $class]) {
    $router->get('/'.$name, [$class, 'index']);
    $router->get('/'.$name.'/create', [$class, 'create']);
    $router->post('/'.$name, [$class, 'store']);
    $router->get('/'.$name.'/{id}/edit', [$class, 'edit']);
    $router->post('/'.$name.'/{id}', [$class, 'update']);
    $router->post('/'.$name.'/{id}/delete', [$class, 'delete']);
}
$router->get('/masters/{type}', [MasterController::class, 'index']);
$router->get('/masters/{type}/create', [MasterController::class, 'create']);
$router->post('/masters/{type}', [MasterController::class, 'store']);
$router->get('/masters/{type}/{id}/edit', [MasterController::class, 'edit']);
$router->post('/masters/{type}/{id}', [MasterController::class, 'update']);
$router->post('/masters/{type}/{id}/toggle', [MasterController::class, 'toggle']);
$router->post('/masters/{type}/{id}/delete', [MasterController::class, 'delete']);
$router->get('/company-settings', [CompanySettingsController::class, 'edit']);
$router->post('/company-settings', [CompanySettingsController::class, 'update']);
$router->post('/quotations/{id}/send', [QuotationController::class, 'send']);
$router->post('/quotations/{id}/approve', [QuotationController::class, 'approve']);
$router->post('/quotations/{id}/reject', [QuotationController::class, 'reject']);
$router->post('/quotations/{id}/duplicate', [QuotationController::class, 'duplicate']);
$router->get('/quotations/{id}/pdf', [QuotationController::class, 'pdf']);
$router->get('/quotations/{id}/print', [QuotationController::class, 'pdf']);
$router->get('/reports/{type}', [ReportController::class, 'show']);
$router->get('/reports/{type}/export', [ReportController::class, 'export']);
$router->get('/activity-logs', [ActivityLogController::class, 'index']);
echo $router->dispatch($_SERVER['REQUEST_METHOD'], $_SERVER['REQUEST_URI']);
