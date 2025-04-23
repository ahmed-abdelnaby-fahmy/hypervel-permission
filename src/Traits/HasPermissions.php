<?php

namespace Hypervel\Permission\Traits;

use Hyperf\Database\Model\Relations\MorphToMany;
use Hypervel\Permission\Models\Permission;
use Hypervel\Permission\Services\PermissionRegistrar;

trait HasPermissions
{
    public function getAllPermissions()
    {
        $directPermissions = $this->permissions()->pluck('name')->toArray();
        $rolePermissions = $this->roles()->get()
            ->flatMap(function ($role) {
                return $role->permissions()->pluck('name')->toArray();
            })->toArray();

        return array_merge($directPermissions, $rolePermissions);
    }

    public function getPermissionsViaRoles()
    {
        return $this->roles()->get()
            ->flatMap(function ($role) {
                return $role->permissions()->pluck('name')->toArray();
            })->toArray();
    }

    /**
     * Direct permissions on the model.
     */
    public function permissions(): MorphToMany
    {
        return $this->morphToMany(
            Permission::class,
            'model',
            config('permission.table_names.model_has_permissions'),
            config('permission.column_names.model_morph_key'),
            'permission_id'
        );
    }

    /**
     * Assign one or more permissions.
     */
    public function givePermissionTo(...$permissions): void
    {
        $ids = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (is_string($permission)) {
                    return Permission::findByName($permission, $this->getPermissionGuardName());
                }
                return $permission;
            })
            ->pluck('id')
            ->all();
        $this->permissions()->syncWithoutDetaching($ids);
        app(PermissionRegistrar::class)->clearCache();
    }

    /**
     * Remove one or more permissions.
     */
    public function revokePermissionTo(...$permissions): void
    {
        $ids = collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                if (is_string($permission)) {
                    return Permission::findByName($permission, $this->getPermissionGuardName());
                }
                return $permission;
            })
            ->pluck('id')
            ->all();
        $this->permissions()->detach($ids);
        app(PermissionRegistrar::class)->clearCache();
    }

    /**
     * Check if the model has a given permission.
     */
    public function hasPermissionTo($permission): bool
    {
        if (!is_string($permission))
            $permission = $permission->name;
        $permissions = app(PermissionRegistrar::class)->getUserPermissions();
        return in_array($permission, $permissions);
    }

    /**
     * Determine the guard name to use.
     */
    protected function getPermissionGuardName(): string
    {
        return $this->guard_name ?? auth()->getDefaultDriver();
    }
}
