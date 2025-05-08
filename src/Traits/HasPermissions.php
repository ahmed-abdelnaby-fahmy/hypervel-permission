<?php

namespace Hypervel\Permission\Traits;

use Hyperf\Database\Model\Relations\MorphToMany;
use Hypervel\Permission\Models\Permission;
use Hypervel\Permission\Services\PermissionRegistrar;

trait HasPermissions
{
    /**
     * Get all permissions the model has, both direct and via roles.
     *
     * @return array
     */
    public function getAllPermissions(): array
    {
        return array_merge(
            $this->getDirectPermissions(),
            $this->getPermissionsViaRoles()
        );
    }

    /**
     * Get all direct permissions assigned to the model.
     *
     * @return array
     */
    public function getDirectPermissions(): array
    {
        return $this->permissions()->pluck('name')->toArray();
    }

    /**
     * Get all permissions the model has via its roles.
     *
     * @return array
     */
    public function getPermissionsViaRoles(): array
    {
        return $this->roles()->get()
            ->flatMap(function ($role) {
                return $role->permissions()->pluck('name')->toArray();
            })
            ->toArray();
    }

    /**
     * Direct permissions relationship.
     *
     * @return \Hyperf\Database\Model\Relations\MorphToMany
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
     * Assign one or more permissions to the model.
     *
     * @param mixed ...$permissions
     * @return void
     */
    public function givePermissionTo(...$permissions): void
    {
        $permissionIds = $this->getPermissionIds($permissions);
        $this->permissions()->syncWithoutDetaching($permissionIds);
        $this->refreshPermissionCache();
    }

    /**
     * Replace all existing permissions with the given ones.
     *
     * @param mixed ...$permissions
     * @return void
     */
    public function syncPermissions(...$permissions): void
    {
        $permissionIds = $this->getPermissionIds($permissions);
        $this->permissions()->sync($permissionIds);
        $this->refreshPermissionCache();
    }

    /**
     * Remove one or more permissions from the model.
     *
     * @param mixed ...$permissions
     * @return void
     */
    public function revokePermissionTo(...$permissions): void
    {
        $permissionIds = $this->getPermissionIds($permissions);
        $this->permissions()->detach($permissionIds);
        $this->refreshPermissionCache();
    }

    /**
     * Check if the model has a given permission.
     *
     * @param mixed $permission
     * @return bool
     */
    public function hasPermissionTo($permission): bool
    {
        $permissionName = is_string($permission) ? $permission : $permission->name;
        $permissions = app(PermissionRegistrar::class)->getUserPermissions();
        return in_array($permissionName, $permissions);
    }

    /**
     * Get the guard name for permissions.
     *
     * @return string
     */
    protected function getPermissionGuardName(): string
    {
        return $this->guard_name ?? auth()->getDefaultDriver();
    }

    /**
     * Convert the given permissions to an array of permission IDs.
     *
     * @param array $permissions
     * @return array
     */
    protected function getPermissionIds(array $permissions): array
    {
        return collect($permissions)
            ->flatten()
            ->map(function ($permission) {
                return $this->getStoredPermission($permission);
            })
            ->pluck('id')
            ->all();
    }

    /**
     * Get a permission instance from various input formats.
     *
     * @param mixed $permission
     * @return \Hypervel\Permission\Models\Permission
     */
    protected function getStoredPermission($permission): Permission
    {
        if (is_string($permission)) {
            return Permission::findByName($permission, $this->getPermissionGuardName());
        }

        return $permission;
    }

    /**
     * Clear the permissions cache.
     *
     * @return void
     */
    protected function refreshPermissionCache(): void
    {
        app(PermissionRegistrar::class)->clearCache();
    }
}