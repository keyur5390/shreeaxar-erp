<?php
namespace App\Controllers;
use App\Core\Database; use App\Support\Auth;
class DashboardController
{
    public function index(): string
    {
        Auth::requirePermission('dashboard.view'); $db=Database::connect();
        $counts=[]; foreach(['customers','products','quotations','users'] as $t) $counts[$t]=(int)$db->query("SELECT COUNT(*) FROM {$t} WHERE deleted_at IS NULL")->fetchColumn();
        $revenue=(float)$db->query('SELECT COALESCE(SUM(grand_total),0) FROM quotations WHERE deleted_at IS NULL')->fetchColumn();
        $recent=$db->query('SELECT q.*, c.company_name FROM quotations q JOIN customers c ON c.id=q.customer_id WHERE q.deleted_at IS NULL ORDER BY q.id DESC LIMIT 5')->fetchAll();
        $logs=$db->query('SELECT a.*, u.name user_name FROM activity_logs a LEFT JOIN users u ON u.id=a.user_id ORDER BY a.id DESC LIMIT 8')->fetchAll();
        return view('dashboard.index', compact('counts','revenue','recent','logs'));
    }
}
