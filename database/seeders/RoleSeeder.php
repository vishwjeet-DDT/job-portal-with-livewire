<?php

namespace Database\Seeders;

use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Role;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        $roles = [
                    ['name' => 'Super Admin', 'label' => 'Super Administrator'],
                    ['name' => 'Admin', 'label' => 'Administrator'],
                    ['name' => 'Employers', 'label' => 'Employer'],
                    ['name' => 'Candidates', 'label' => 'Candidate'],
                ];

                foreach ($roles as $roleData) {
                    Role::firstOrCreate(
                        ['name' => $roleData['name']],
                        ['label' => $roleData['label']]
                    );
                }

    }
}
