<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class RolesAndPermissionsSeeder extends Seeder
{
    public function run(): void
    {
        // Reset cached roles and permissions
        app()[\Spatie\Permission\PermissionRegistrar::class]->forgetCachedPermissions();

        // Create permissions
        $permissions = [
            // Project permissions
            'projects.view',
            'projects.create',
            'projects.edit',
            'projects.delete',
            'projects.manage_members',

            // Sprint permissions
            'sprints.view',
            'sprints.create',
            'sprints.edit',
            'sprints.delete',
            'sprints.start',
            'sprints.complete',

            // Epic permissions
            'epics.view',
            'epics.create',
            'epics.edit',
            'epics.delete',

            // Story permissions
            'stories.view',
            'stories.create',
            'stories.edit',
            'stories.delete',
            'stories.assign',
            'stories.move',

            // Task permissions
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',
            'tasks.log_time',

            // Report permissions
            'reports.view',
            'reports.export',
        ];

        foreach ($permissions as $permission) {
            Permission::create(['name' => $permission]);
        }

        // Create roles and assign permissions
        // Admin role - full access
        $adminRole = Role::create(['name' => 'admin']);
        $adminRole->givePermissionTo(Permission::all());

        // Gainline User role - full access to all projects
        $gainlineRole = Role::create(['name' => 'gainline_user']);
        $gainlineRole->givePermissionTo(Permission::all());

        // Project Manager role
        $pmRole = Role::create(['name' => 'project_manager']);
        $pmRole->givePermissionTo([
            'projects.view',
            'projects.edit',
            'projects.manage_members',
            'sprints.view',
            'sprints.create',
            'sprints.edit',
            'sprints.delete',
            'sprints.start',
            'sprints.complete',
            'epics.view',
            'epics.create',
            'epics.edit',
            'epics.delete',
            'stories.view',
            'stories.create',
            'stories.edit',
            'stories.delete',
            'stories.assign',
            'stories.move',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.delete',
            'tasks.log_time',
            'reports.view',
            'reports.export',
        ]);

        // Member role - can work on stories and tasks
        $memberRole = Role::create(['name' => 'member']);
        $memberRole->givePermissionTo([
            'projects.view',
            'sprints.view',
            'epics.view',
            'stories.view',
            'stories.create',
            'stories.edit',
            'stories.move',
            'tasks.view',
            'tasks.create',
            'tasks.edit',
            'tasks.log_time',
            'reports.view',
        ]);

        // Viewer role - read only
        $viewerRole = Role::create(['name' => 'viewer']);
        $viewerRole->givePermissionTo([
            'projects.view',
            'sprints.view',
            'epics.view',
            'stories.view',
            'tasks.view',
            'reports.view',
        ]);
    }
}
