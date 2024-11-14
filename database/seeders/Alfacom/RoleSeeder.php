<?php

namespace Database\Seeders\Alfacom;

use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

trait RoleSeeder
{
    private $permissions = [
        'dashboard' => [
            'access',
            'view',
        ],
        'calendar' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'links' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'documents' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'customers' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'paperworks' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'mandates' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'agencies' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'brands' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'products' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'communications' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'alerts' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'tickets' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'communications' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'statements' => [
            'access',
        ],
        'users' => [
            'access',
            'view',
            'create',
            'edit',
            'delete',
        ],
        'logs' => [
            'access',
            'view',
        ],
        'reports-admin' => [
            'access',
            'view',
        ],
        'reports-production' => [
            'access',
            'view',
        ],
        'reports-appointments' => [
            'access',
            'view',
        ],
        'business-economy' => [
            'access',
            'view',
        ],
        'business-reconciliation' => [
            'access',
            'view',
        ],
        'business-appointments' => [
            'access',
            'view',
        ],
        // 'marketing-contacts' => [
        //     'view',
        // ],
        // 'marketing-requests' => [
        //     'view',
        // ],
    ];

    private function roles()
    {
        $gestione = Role::create(['name' => 'gestione', 'guard_name' => 'api']);
        $backoffice = Role::create(['name' => 'backoffice', 'guard_name' => 'api']);
        $agente = Role::create(['name' => 'agente', 'guard_name' => 'api']);
        $struttura = Role::create(['name' => 'struttura', 'guard_name' => 'api']);
        $telemarketing = Role::create(['name' => 'telemarketing', 'guard_name' => 'api']);
        $teamLeader = Role::create(['name' => 'team leader', 'guard_name' => 'api']);
        $amministrazione = Role::create(['name' => 'amministrazione', 'guard_name' => 'api']);
        // Create permissions
        foreach ($this->permissions as $subject => $actions) {
            foreach ($actions as $action) {
                Permission::create(['name' => $action . ' ' . $subject, 'guard_name' => 'api']);
            }
        }
        $this->assignPermissions($gestione);
        $this->assignPermissions($backoffice);
        $this->assignPermissions($agente);
        $this->assignPermissions($struttura);
        $this->assignPermissions($telemarketing);
        $this->assignPermissions($teamLeader);
        $this->assignPermissions($amministrazione);
    }

    private function assignPermissions($role)
    {
        if ($role->name === 'gestione') {
            foreach ($this->permissions as $subject => $actions) {
                foreach ($actions as $action) {
                    $role->givePermissionTo($action . ' ' . $subject);
                }
            }
        }

        if ($role->name === 'backoffice') {
            $exclude = ['business-economy', 'business-reconciliation', 'business-appointments'];
            foreach ($this->permissions as $subject => $actions) {
                if (in_array($subject, $exclude)) {
                    continue;
                }
                foreach ($actions as $action) {
                    $role->givePermissionTo($action . ' ' . $subject);
                }
            }
        }

        if ($role->name === 'agente' || $role->name === 'struttura') {
            foreach ($this->permissions['calendar'] as $action) {
                $role->givePermissionTo($action . ' calendar');
            }
            foreach ($this->permissions['customers'] as $action) {
                if ($action === 'delete') {
                    continue;
                }
                $role->givePermissionTo($action . ' customers');
            }
            foreach ($this->permissions['paperworks'] as $action) {
                if ($action === 'delete') {
                    continue;
                }
                $role->givePermissionTo($action . ' paperworks');
            }
            $role->givePermissionTo('view brands');
            $role->givePermissionTo('view products');
        }

        if ($role->name === 'telemarketing' || $role->name === 'team leader') {
            foreach ($this->permissions['calendar'] as $action) {
                $role->givePermissionTo($action . ' calendar');
            }
            foreach ($this->permissions['customers'] as $action) {
                if ($action === 'delete') {
                    continue;
                }
                $role->givePermissionTo($action . ' customers');
            }
            foreach ($this->permissions['tickets'] as $action) {
                if ($action === 'delete') {
                    continue;
                }
                $role->givePermissionTo($action . ' paperworks');
            }
        }

        if ($role->name === 'amministrazione') {
            foreach ($this->permissions['brands'] as $action) {
                $role->givePermissionTo($action . ' brands');
            }
            foreach ($this->permissions['products'] as $action) {
                $role->givePermissionTo($action . ' products');
            }
            foreach ($this->permissions['reports-admin'] as $action) {
                $role->givePermissionTo($action . ' reports-admin');
            }
            foreach ($this->permissions['reports-production'] as $action) {
                $role->givePermissionTo($action . ' reports-production');
            }
            foreach ($this->permissions['reports-appointments'] as $action) {
                $role->givePermissionTo($action . ' reports-appointments');
            }
        }
    }
}
