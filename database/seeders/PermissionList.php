<?php

namespace Database\Seeders;

final class PermissionList
{
    /** @return list<string> */
    public static function all(): array
    {
        return ['dashboard.view','roles.view','roles.create','roles.edit','roles.delete','users.view','users.create','users.edit','users.delete','company-settings.view','company-settings.edit','masters.view','masters.create','masters.edit','masters.delete','masters.export','customers.view','customers.create','customers.edit','customers.delete','customers.export','products.view','products.create','products.edit','products.delete','products.export','quotations.view','quotations.create','quotations.edit','quotations.delete','quotations.export','quotations.approve','reports.view','reports.export','activity-logs.view','activity-logs.export'];
    }
}
