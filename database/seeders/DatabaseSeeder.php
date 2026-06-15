<?php

namespace Database\Seeders;

final class DatabaseSeeder
{
    public function run(): void
    {
        (new PermissionSeeder())->run();
        (new RoleSeeder())->run();
        (new UserSeeder())->run();
        (new MasterSeeder())->run();
        (new CompanySettingSeeder())->run();
    }
}
