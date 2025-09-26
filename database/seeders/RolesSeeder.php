<?php

namespace Database\Seeders;

use App\Models\Role;
use Illuminate\Database\Seeder;

class RolesSeeder extends Seeder
{
    public function run(): void
    {
        $roles = [
            1 => 'Viewer',
            2 => 'Contributor',
            3 => 'Admin',
        ];

        foreach ($roles as $id => $label) {
            Role::updateOrCreate(
                ['id' => $id],
                ['label' => $label]
            );
        }
    }
}
