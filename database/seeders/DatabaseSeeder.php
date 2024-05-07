<?php

namespace Database\Seeders;

// use Illuminate\Database\Console\Seeds\WithoutModelEvents;

use App\Models\User;
use App\Models\WhitelistIP;
use Illuminate\Database\Seeder;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Facades\Hash;

class DatabaseSeeder extends Seeder
{
    /**
     * Seed the application's database.
     *
     * @return void
     */
    public function run()
    {

        $roles = [
            ['name' => 'Admin', 'guard_name' => 'web'],
        ];

        Role::upsert($roles, ['name']);

        // \App\Models\User::factory(10)->create();

        if (!User::where('email', 'admin@gmail.com')->first()) {
            DB::transaction(function () {
                $admin = User::create(
                    [
                        'name' => 'admin',
                        'user_name' => 'admin',
                        'email' => 'admin@mail.com',
                        'role_name' => 'Admin',
                        'password' => Hash::make('password'),
                    ]
                );

                Artisan::call('update:permissions');
                $admin->assignRole('Admin');
            });
        }

        WhitelistIP::create(
            [
                'address' => '103.112.243.118',
                'remarks' => 'testingass',
                'status' => '1',
            ]
        );
    }
}
