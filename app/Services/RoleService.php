<?php

namespace App\Services;

use Exception;
use Illuminate\Http\JsonResponse;
use Illuminate\Support\Facades\Artisan;
use Illuminate\Support\Facades\DB;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Brian2694\Toastr\Facades\Toastr;
use stdClass;

class RoleService
{
    public function paginate($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ?: 15;
            $page = $request->page ?: 1;
            $sortBy = $request->sortBy ?: 'created_at';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Role())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->name%");
            });

            $results = $query->with('permissions')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            return $this->generalErrorResponse($e);
        }
    }

    public function keyValue($request): JsonResponse
    {
        try {
            $roles = Role::all();
            $data = [];
            foreach ($roles as $key => $value) {
                // code...
                $obj = new stdClass();
                $obj->label = $value->name;
                $obj->value = $value->id;
                array_push($data, $obj);
            }

            return response()->json($data, 200);
        } catch (\Exception $e) {
            return $this->generalErrorResponse($e);
        }
    }

    public function paginatePermissions($request): JsonResponse
    {
        try {
            $perPage = $request->rowsPerPage ? $request->rowsPerPage : 15;
            $page = $request->page ? $request->page : 1;
            $sortBy = $request->sortBy ? $request->sortBy : 'name';
            $sortOrder = $request->descending == 'true' ? 'desc' : 'asc';

            $query = (new Permission())->newQuery()->orderBy($sortBy, $sortOrder);

            $query->when($request->name, function ($query) use ($request) {
                $query->where('name', 'like', "%$request->name%");
            });

            $results = $query->with('roles')->paginate($perPage, ['*'], 'page', $page);

            return response()->json($results, 200);
        } catch (\Exception $e) {
            return $this->generalErrorResponse($e);
        }
    }

    public function store(array $data)
    {
        try {
            return DB::transaction(function () use ($data) {
                $role = Role::create([
                    'name' => $data['name'],
                    'remarks' => $data['remarks'],
                    'guard_name' => 'web',
                ]);

                $role->syncPermissions($data['permissions']);

                Artisan::call('permission:cache-reset');

                $success = __('messages.Success');

                Toastr::success('Added Successfully', $success);

                return redirect()->back();

                // return response()->json([
                //     'messages' => ['Role created successfully'],
                // ], 201);
            });
        } catch (\Exception $e) {
            return $this->generalErrorResponse($e);
        }
    }

    public function update($role, array $data)
    {
        try {
            DB::transaction(function () use ($role, $data) {
                $role->update([
                    'name' => $data['name'],
                    'remarks' => $data['remarks'],
                ]);

                $role->syncPermissions($data['permissions']);
            });

            Artisan::call('permission:cache-reset');

            $success = __('messages.Success');

            Toastr::success('Updated Successfully', $success);

            return redirect()->back();

            // return response()->json([
            //     'messages' => ['Role updated successfully'],
            // ], 200);
        } catch (\Exception $e) {
            return $this->generalErrorResponse($e);
        }
    }

    public function delete($request, $role): JsonResponse
    {
        try {
            DB::transaction(function () use ($request, $role) {
                if ($request->new_role) {
                    foreach ($role->users as $user) {
                        $user->removeRole($role->name);
                        $user->assignRole($request->new_role);
                    }
                }

                $role->syncPermissions([]);
                $role->delete();
            });

            Artisan::call('permission:cache-reset');

            return response()->json([
                'messages' => ['Deleted Successfully'],
            ], 200);
        } catch (\Exception $e) {
            return $this->generalErrorResponse($e);
        }
    }


    // if (! function_exists('$this->generalErrorResponse')) {
    function generalErrorResponse(Exception $e)
    {
        return response()->json([
            'messages' => [$e->getMessage()],
            'trace' => [$e->getTrace()],
        ], 400);
    }
    // }
}
