<?php

namespace Database\Seeders;

use Illuminate\Database\Seeder;
use App\Models\User;
use App\Models\Role;
use Illuminate\Support\Facades\Hash;

class UserSeeder extends Seeder
{
    public function run(): void
    {
        // Define manual users
        $manualUsers = [
            ['name' => 'Jiv Codera', 'email' => 'jiv@example.com', 'user_type' => 'ADMIN'],
            ['name' => 'Jennifer Gorumba', 'email' => 'jennifer@example.com', 'user_type' => 'DEAN'],
            ['name' => 'Geryl Cataraja', 'email' => 'geryl@example.com', 'user_type' => 'TASK FORCE'],
            ['name' => 'Ruby Mary G. Encenzo', 'email' => 'ruby@example.com', 'user_type' => 'TASK FORCE'],
            ['name' => 'Levi Esmero', 'email' => 'levi@example.com', 'user_type' => 'INTERNAL ASSESSOR'],
            ['name' => 'Janet Aclao', 'email' => 'janet@example.com', 'user_type' => 'ACCREDITOR'],
        ];

        // Create manual users and attach role
        foreach ($manualUsers as $userData) {
            $user = User::create([
                'name' => $userData['name'],
                'email' => $userData['email'],
                'password' => Hash::make('password'),
                'user_type' => $userData['user_type'],
                'status' => 'Active',
                'email_verified_at' => now(),
            ]);

            // Find role by name and attach to pivot
            $role = Role::firstWhere('name', $user->user_type);
            if ($role) {
                $user->roles()->attach([$role->id]);

                $user->current_role_id = $role->id;
                $user->save();
            }
        }

        // Define factories to generate multiple users
        $factoryUsers = [
            ['user_type' => 'TASK FORCE', 'count' => 10],
            ['user_type' => 'INTERNAL ASSESSOR', 'count' => 10],
        ];

        // Create factory users and attach pivot roles
        foreach ($factoryUsers as $factoryData) {
            User::factory()
                ->count($factoryData['count'])
                ->create([
                    'user_type' => $factoryData['user_type'],
                    'status' => 'Active',
                ])
                ->each(function ($user) {
                    $role = Role::firstWhere('name', $user->user_type);
                    if ($role) {
                        $user->roles()->attach([$role->id]);

                        $user->current_role_id = $role->id;
                        $user->save();
                    }
                });
        }
    }
}
