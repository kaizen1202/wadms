<?php

namespace Database\Seeders;

use App\Enums\UserType;
use App\Models\Role;
use Illuminate\Database\Console\Seeds\WithoutModelEvents;
use Illuminate\Database\Seeder;

class RoleSeeder extends Seeder
{
    /**
     * Run the database seeds.
     */
    public function run(): void
    {
        foreach (UserType::cases() as $type) {
            Role::firstOrCreate(
                ['slug' => str($type->value)->slug('_')],
                [
                    'name' => $type->value,
                ]
            );
        }
    }
}
