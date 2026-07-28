<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Modules\Templates\Database\Seeders\TemplatesDatabaseSeeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class DatabaseSeeder extends Seeder
{
    /**
     * Seeder minimal du MVP : rôle admin, permissions Phase 1, premier compte
     * opérateur, catalogue de modèles de démonstration. Aucune donnée de
     * commande/invitation n'est créée ici — saisie manuelle par les opérateurs.
     */
    public function run(): void
    {
        $adminRole = Role::firstOrCreate(['name' => 'admin', 'guard_name' => 'web']);

        $permissions = [
            'templates.manage',
            'orders.manage',
            'invitations.manage',
            'media.manage',
        ];

        foreach ($permissions as $permission) {
            Permission::firstOrCreate(['name' => $permission, 'guard_name' => 'web']);
        }

        $adminRole->syncPermissions($permissions);

        $admin = User::firstOrCreate(
            ['email' => env('ADMIN_EMAIL', 'admin@notrejour.tn')],
            [
                'name' => 'Notre Jour Admin',
                'password' => bcrypt(env('ADMIN_PASSWORD', 'change-me-immediately')),
            ]
        );

        if (! $admin->hasRole('admin')) {
            $admin->assignRole($adminRole);
        }

        $this->call(TemplatesDatabaseSeeder::class);
    }
}
