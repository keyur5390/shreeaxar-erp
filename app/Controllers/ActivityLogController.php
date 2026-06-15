<?php
namespace App\Controllers;
use App\Core\Database; use App\Support\Auth;
class ActivityLogController
{ public function index(): string { Auth::requirePermission('activity-logs.view'); $rows=Database::connect()->query('SELECT a.*, u.name user_name FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 100')->fetchAll(); return view('activity.index', compact('rows')); }}
