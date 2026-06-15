<?php

namespace Database\Seeders;

final class RoleSeeder extends AbstractSeeder
{
    public function run(): void
    {
        $permissions = PermissionList::all();
        foreach (['Super Admin','Admin','Sales Manager','Sales Executive','Finance'] as $role) {
            $this->insertIgnore('roles', ['name' => $role]);
        }

        $this->syncRole('Super Admin', $permissions);
        $this->syncRole('Admin', array_values(array_filter($permissions, fn ($permission) => ! str_starts_with($permission, 'activity-logs.export'))));
        $this->syncRole('Sales Manager', ['dashboard.view','customers.view','customers.create','customers.edit','products.view','quotations.view','quotations.create','quotations.edit','quotations.export','quotations.approve','reports.view','reports.export']);
        $this->syncRole('Sales Executive', ['dashboard.view','customers.view','customers.create','customers.edit','products.view','quotations.view','quotations.create','quotations.edit','reports.view']);
        $this->syncRole('Finance', ['dashboard.view','quotations.view','quotations.export','reports.view','reports.export']);
    }

    private function syncRole(string $role, array $permissions): void
    {
        $db = $this->db();
        $roleId = $this->idBy('roles', 'name', $role);
        foreach ($permissions as $permission) {
            $permissionId = $this->idBy('permissions', 'name', $permission);
            $prefix = $this->isMysql($db) ? 'INSERT IGNORE' : 'INSERT OR IGNORE';
            $db->prepare($prefix.' INTO permission_role (permission_id, role_id) VALUES (?, ?)')->execute([$permissionId, $roleId]);
        }
    }
}
