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
        'aipaperworks' => [
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
            foreach ($this->permissions['dashboard'] as $action) {
                $role->givePermissionTo($action . ' dashboard');
            }
            foreach ($this->permissions['calendar'] as $action) {
                $role->givePermissionTo($action . ' calendar');
            }
            foreach ($this->permissions['links'] as $action) {
                $role->givePermissionTo($action . ' links');
            }
            foreach ($this->permissions['brands'] as $action) {
                $role->givePermissionTo($action . ' brands');
            }
            foreach ($this->permissions['products'] as $action) {
                $role->givePermissionTo($action . ' products');
            }
            foreach ($this->permissions['documents'] as $action) {
                $role->givePermissionTo($action . ' documents');
            }
            foreach ($this->permissions['customers'] as $action) {
                $role->givePermissionTo($action . ' customers');
            }
            foreach ($this->permissions['paperworks'] as $action) {
                $role->givePermissionTo($action . ' paperworks');
            }
            foreach ($this->permissions['aipaperworks'] as $action) {
                $role->givePermissionTo($action . ' aipaperworks');
            }
            foreach ($this->permissions['mandates'] as $action) {
                $role->givePermissionTo($action . ' mandates');
            }
            foreach ($this->permissions['communications'] as $action) {
                $role->givePermissionTo($action . ' communications');
            }
            foreach ($this->permissions['tickets'] as $action) {
                $role->givePermissionTo($action . ' tickets');
            }
            foreach ($this->permissions['reports-production'] as $action) {
                $role->givePermissionTo($action . ' reports-production');
            }
            foreach ($this->permissions['reports-appointments'] as $action) {
                $role->givePermissionTo($action . ' reports-appointments');
            }
        }

        if ($role->name === 'agente' || $role->name === 'struttura') {
            foreach ($this->permissions['dashboard'] as $action) {
                $role->givePermissionTo($action . ' dashboard');
            }
            $role->givePermissionTo('access links');
            $role->givePermissionTo('view links');
            $role->givePermissionTo('access documents');
            $role->givePermissionTo('view documents');
            $role->givePermissionTo('access calendar');
            $role->givePermissionTo('view calendar');
            foreach ($this->permissions['customers'] as $action) {
                if ($action === 'delete' || $action === 'edit') {
                    continue;
                }
                $role->givePermissionTo($action . ' customers');
            }
            foreach ($this->permissions['paperworks'] as $action) {
                if ($action === 'delete' || $action === 'edit') {
                    continue;
                }
                $role->givePermissionTo($action . ' paperworks');
            }
            $role->givePermissionTo('view brands');
            $role->givePermissionTo('view products');
            $role->givePermissionTo('access communications');
            $role->givePermissionTo('view communications');
            foreach ($this->permissions['tickets'] as $action) {
                if ($action === 'delete') {
                    continue;
                }
                $role->givePermissionTo($action . ' tickets');
            }
            foreach ($this->permissions['reports-production'] as $action) {
                $role->givePermissionTo($action . ' reports-production');
            }

            if ($role->name === 'struttura') {
                $role->givePermissionTo('access users');
                $role->givePermissionTo('view users');
            }
        }

        if ($role->name === 'telemarketing' || $role->name === 'team leader') {
            $role->givePermissionTo('access links');
            $role->givePermissionTo('view links');
            $role->givePermissionTo('access documents');
            $role->givePermissionTo('view documents');
            foreach ($this->permissions['calendar'] as $action) {
                $role->givePermissionTo($action . ' calendar');
            }
            $role->givePermissionTo('access customers');
            $role->givePermissionTo('view customers');
            $role->givePermissionTo('access communications');
            $role->givePermissionTo('view communications');
            if ($role->name === 'team leader') {
                $role->givePermissionTo('access users');
                $role->givePermissionTo('view users');
            }
        }

        if ($role->name === 'amministrazione') {
            foreach ($this->permissions['dashboard'] as $action) {
                $role->givePermissionTo($action . ' dashboard');
            }
            foreach ($this->permissions['calendar'] as $action) {
                $role->givePermissionTo($action . ' calendar');
            }
            foreach ($this->permissions['links'] as $action) {
                $role->givePermissionTo($action . ' links');
            }
            foreach ($this->permissions['documents'] as $action) {
                $role->givePermissionTo($action . ' documents');
            }
            foreach ($this->permissions['customers'] as $action) {
                $role->givePermissionTo($action . ' customers');
            }
            foreach ($this->permissions['paperworks'] as $action) {
                $role->givePermissionTo($action . ' paperworks');
            }
            foreach ($this->permissions['aipaperworks'] as $action) {
                $role->givePermissionTo($action . ' aipaperworks');
            }
            foreach ($this->permissions['mandates'] as $action) {
                $role->givePermissionTo($action . ' mandates');
            }
            foreach ($this->permissions['agencies'] as $action) {
                $role->givePermissionTo($action . ' agencies');
            }
            foreach ($this->permissions['brands'] as $action) {
                $role->givePermissionTo($action . ' brands');
            }
            foreach ($this->permissions['products'] as $action) {
                $role->givePermissionTo($action . ' products');
            }
            foreach ($this->permissions['communications'] as $action) {
                $role->givePermissionTo($action . ' communications');
            }
            foreach ($this->permissions['tickets'] as $action) {
                $role->givePermissionTo($action . ' tickets');
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
            foreach ($this->permissions['business-economy'] as $action) {
                $role->givePermissionTo($action . ' business-economy');
            }
            foreach ($this->permissions['business-reconciliation'] as $action) {
                $role->givePermissionTo($action . ' business-reconciliation');
            }
        }
    }
}
