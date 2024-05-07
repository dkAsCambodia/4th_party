<?php

namespace App\Console\Commands;

use Illuminate\Console\Command;
use Illuminate\Support\Facades\DB;
use Illuminate\Support\Facades\Route;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;

class UpdatePermissions extends Command
{
    /**
     * The name and signature of the console command.
     *
     * @var string
     */
    protected $signature = 'update:permissions';

    /**
     * The console command description.
     *
     * @var string
     */
    protected $description = 'Update role permissions';

    /**
     * Execute the console command.
     *
     * @return int
     */
    public function handle()
    {
        $routes = Route::getRoutes();
        $excludedRoutes = ['ignition.executeSolution', 'ignition.healthCheck', 'ignition.updateConfig', 'sanctum.csrf-cookie'];
        $permissions = [];

        echo "Clean up old/outdated permissions.\n";
        // Clean up old and unused permissions
        $permissions = Permission::all();
        if (!empty($permissions)) {
            foreach ($permissions as $permission) {
                $permissionData = [
                    'permission_id' => $permission['id'],
                    'role_id' => 1,
                ];
                $permissionId = $permission['id'];
                DB::statement("DELETE FROM permissions WHERE id = $permissionId");
            }
        }
        echo "All done.\n";

        $permissions = [];

        foreach ($routes as $route) {
            $name = trim($route->getName());

            if (!$name || in_array($name, $excludedRoutes)) {
                continue;
            }

            try {
                // throws an exception rather than returning null
                $permission = Permission::findByName($name, 'web');
                // dd($permission->name);
                array_push($permissions, $permission->name);
                // echo 'find- ' . $permission->name . "\n";
            } catch (\Exception $e) {
                $permission = Permission::create(['name' => $name, 'guard_name' => 'web']);
                array_push($permissions, $permission->name);
                echo 'create- ' . $permission->name . "\n";
            }
        }

        try {
            echo "Sync super admin permissions...\n";

            $superAdmin = Role::findByName('Admin', 'web');
            $permissions = Permission::all();
            if (!empty($permissions)) {
                foreach ($permissions as $permission) {
                    $permissionData = [
                        'permission_id' => $permission['id'],
                        'role_id' => $superAdmin->id,
                    ];
                    DB::table('role_has_permissions')->insert($permissionData);
                }
            }

            echo "Super admin permissions updated.\n";
        } catch (\Exception $e) {
            echo $e->getMessage();
        }
    }
}
