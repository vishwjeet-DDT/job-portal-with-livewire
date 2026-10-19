<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Illuminate\Support\Str;
use Illuminate\Support\Facades\Artisan;

class CheckPermission
{
    /**
     * Handle an incoming request.
     *
     * @param  \Illuminate\Http\Request  $request
     * @param  \Closure  $next
     * @param  string  $permission
     * @return mixed
     */
    public function handle(Request $request, Closure $next, $permission = null)
    {
        $user = auth()->user();
        // if($user->hasRole('Super Admin')){
        //     return $next($request);
        // }
        $routeName = $request->route()->getName();

        if (!$routeName) {
            abort(403, 'Route name not defined.');
        }

        if (str_starts_with($routeName, 'admin.')) {
            $routeName = substr($routeName, strlen('admin.'));
        }else{
            return $next($request);
        }

        // Permission to check is derived from the route name
        $permissionToCheck = $routeName;
        // dd($this->createPermissionWithParent($permissionToCheck));

        if (!Auth::check()) {
            abort(403, 'Unauthorized action.');
        }

        $this->createPermissionWithParent($permissionToCheck);

        // dd($this->hasAccessToPermission($permissionToCheck));
        if (!$this->hasAccessToPermission($permissionToCheck)) {
            abort(403, 'Forbidden');
        }

        return $next($request);
    }

    /**
     * Check if the user has access to a permission and its parent.
     *
     * @param string $permission
     * @return bool
     */
    protected function hasAccessToPermission($permission)
    {
        $user = Auth::user();

        Artisan::call('permission:cache-reset');
        if ($user->can($permission)) {
            $parentPermission = $this->getParentPermission($permission);
            if ($parentPermission && !$user->can($parentPermission)) {
                return false;
            }

            return true;
        }

        return false;
    }

    /**
     * Get the parent permission of a permission string.
     *
     * @param string $permission
     * @return string|null
     */
    protected function getParentPermission($permission)
    {
        $segments = explode('.', $permission);

        array_pop($segments);
        return !empty($segments) ? implode('.', $segments) : null;
    }

    /**
     * Create permission with parent permissions if they do not exist.
     *
     * @param string $permission
     * @return void
     */
    protected function createPermissionWithParent($permission)
    {
        $segments = explode('.', $permission);
        $permissionPath = '';
        $parentPermission = null;

        // dd($segments);
        foreach ($segments as $segment) {
            $permissionPath = $permissionPath ? $permissionPath . '.' . $segment : $segment;

            $permission = Permission::firstOrCreate(
                ['name' => $permissionPath],
                ['label' => Str::title(Str::replace('.', ' ', $permissionPath))],
                ['parent_id' => $parentPermission ? $parentPermission->id : null]
            );

            $parentPermission = $permission;

            $superAdminRole = Role::where('name', 'Super Admin')->first();
            if ($superAdminRole && !$superAdminRole->hasPermissionTo($permissionPath)) {
                $superAdminRole->givePermissionTo($permission);
            }
        }
    }

}
