<?php

namespace Hypervel\Permission\Traits;

use Hyperf\Collection\Collection;
use Hyperf\Database\Model\Relations\MorphToMany;
use Hypervel\Permission\Models\Role;
use Hypervel\Permission\Services\PermissionRegistrar;

trait HasRoles
{
    use HasPermissions;

    /**
     * Check if model has the permission via a role.
     */
    public function hasPermissionViaRole($permission): bool
    {
        if (is_string($permission)) {
            try {
                $permission = app(\Hypervel\Permission\Models\Permission::class)::findByName(
                    $permission,
                    auth()->getDefaultDriver()
                );
            } catch (\Exception $e) {
                return false;
            }
        }

        foreach ($this->roles as $role) {
            if ($role->permissions->contains('id', $permission->id)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Roles assigned to the model.
     */
    public function roles(): MorphToMany
    {
        return $this->morphToMany(
            config('permission.models.role', Role::class),
            'model',
            config('permission.table_names.model_has_roles'),
            config('permission.column_names.model_morph_key'),
            'role_id'
        );
    }

    /**
     * Assign one or more roles.
     */
    public function assignRole(...$roles): self
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (is_string($role)) {
                    return Role::findOrCreate($role, auth()->getDefaultDriver());
                }
                return $role;
            })
            ->pluck('id')
            ->all();

        $this->roles()->syncWithoutDetaching($roles);
        app(PermissionRegistrar::class)->clearCache();

        return $this;
    }

    /**
     * Remove one or more roles.
     */
    public function removeRole(...$roles): self
    {
        $roles = collect($roles)
            ->flatten()
            ->map(function ($role) {
                if (is_string($role)) {
                    return Role::findByName($role, auth()->getDefaultDriver());
                }
                return $role;
            })
            ->pluck('id')
            ->all();

        $this->roles()->detach($roles);
        app(PermissionRegistrar::class)->clearCache();

        return $this;
    }

    /**
     * Sync roles to the model.
     */
    public function syncRoles(...$roles): self
    {
        $this->roles()->detach();

        return $this->assignRole($roles);
    }

    /**
     * Check if the model has a given role.
     */
    public function hasRole($role): bool
    {
        if (is_string($role)) {
            return $this->roles->contains('name', $role);
        }

        return $this->roles->contains('id', $role->id);
    }

    /**
     * Check if the model has any of the given roles.
     */
    public function hasAnyRole(...$roles): bool
    {
        foreach ($roles as $role) {
            if ($this->hasRole($role)) {
                return true;
            }
        }

        return false;
    }

    /**
     * Check if the model has all of the given roles.
     */
    public function hasAllRoles(...$roles): bool
    {
        foreach ($roles as $role) {
            if (!$this->hasRole($role)) {
                return false;
            }
        }

        return true;
    }

    /**
     * Get all roles that belong to the model.
     *
     * @return Collection
     */
    public function getRoles(): Collection
    {
        return $this->roles;
    }

    /**
     * Determine if the model has any roles.
     */
    public function hasRoles(): bool
    {
        return $this->roles->isNotEmpty();
    }

    /**
     * Get role names that belong to the model.
     */
    public function getRoleNames(): Collection
    {
        return $this->roles->pluck('name');
    }

    /**
     * Determine the guard name to use.
     */
    protected function getGuardName(): string
    {
        return $this->guard_name ?? auth()->getDefaultDriver();
    }
}