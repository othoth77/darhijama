<?php

namespace Applications\DarHijama\Application\Console;

use Illuminate\Console\Command;
use Mythos\Core\Applications\Contracts\ApplicationRegistry;
use Spatie\Permission\Contracts\Permission as PermissionContract;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class InstallDarHijamaCommand extends Command
{
    protected $signature = 'dar-hijama:install';

    protected $description = 'Install Dar Hijama roles and permissions';

    public function handle(ApplicationRegistry $registry): int
    {
        $permissions = collect($registry->permissions('dar-hijama'))
            ->map(fn ($definition) => Permission::findOrCreate(
                $definition->name,
                $definition->guard,
            ));

        Role::findOrCreate('dar-hijama-admin')->syncPermissions($permissions);
        Role::findOrCreate('dar-hijama-reception')->syncPermissions($permissions->filter(
            fn (PermissionContract $permission): bool => ! in_array($permission->name, [
                'dar-hijama.patients.view-private-notes',
                'dar-hijama.appointments.view-private-notes',
                'dar-hijama.appointments.complete',
                'dar-hijama.reports.view',
                'dar-hijama.patients.export',
            ], true),
        ));
        Role::findOrCreate('dar-hijama-practitioner')->syncPermissions($permissions->filter(
            fn (PermissionContract $permission): bool => in_array($permission->name, [
                'dar-hijama.access',
                'dar-hijama.patients.view',
                'dar-hijama.appointments.view',
                'dar-hijama.appointments.start',
                'dar-hijama.appointments.complete',
                'dar-hijama.appointments.view-private-notes',
                'dar-hijama.practitioner-availability.manage',
            ], true),
        ));
        Role::findOrCreate('dar-hijama-manager')->syncPermissions($permissions->filter(
            fn (PermissionContract $permission): bool => in_array($permission->name, [
                'dar-hijama.access',
                'dar-hijama.patients.view',
                'dar-hijama.appointments.view',
                'dar-hijama.reports.view',
            ], true),
        ));

        $this->components->info('Dar Hijama roles and permissions installed.');

        return self::SUCCESS;
    }
}
