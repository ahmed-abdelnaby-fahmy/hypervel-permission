<?php

namespace Hypervel\Permission\Services;

use Hypervel\Database\Eloquent\Collection;
use Hypervel\Permission\Contracts\PermissionContract;
use Hypervel\Permission\Contracts\RoleContract;
use Hypervel\Support\Facades\Cache;

class PermissionRegistrar
{
    protected string $cacheKey;
    protected ?string $cacheStore;
    protected int $expiration;
    protected bool $cacheEnabled;

    public function __construct()
    {
        $cfg = config('permission.cache');
        $this->cacheKey = $cfg['key'] ?? 'hypervel.permission.cache';
        $this->cacheStore = $cfg['store'] !== 'default' ? $cfg['store'] : null;
        $this->expiration = $cfg['expiration'] ?? 3600;
        $this->cacheEnabled = $cfg['enabled'] ?? false;
    }

    /**
     * Get the permission class instance.
     */
    public function getPermissionClass()
    {
        return app(PermissionContract::class);
    }

    /**
     * Get the role class instance.
     */
    public function getRoleClass()
    {
        return app(RoleContract::class);
    }

    /**
     * Get & cache all permissions.
     */
    public function getAllPermissions(): Collection
    {
        if (!$this->cacheEnabled) {
            return $this->getPermissionClass()::all();
        }

        return Cache::store($this->cacheStore)
            ->remember($this->getPermissionsCacheKey(), $this->expiration, function () {
                return $this->getPermissionClass()::all();
            });
    }

    /**
     * Get & cache all roles.
     */
    public function getAllRoles(): Collection
    {
        if (!$this->cacheEnabled) {
            return $this->getRoleClass()::all();
        }

        return Cache::store($this->cacheStore)
            ->remember($this->getRolesCacheKey(), $this->expiration, function () {
                return $this->getRoleClass()::all();
            });
    }

    /**
     * Get permissions for a specific user.
     */
    public function getUserPermissions(): array
    {
        $this->clearCache();
        $this->clearUserPermissionCache();
        if (!$this->cacheEnabled) {
            return $this->getDirectAndRolePermissions();
        }
        $cacheKey = $this->getUserPermissionsCacheKey();

        return Cache::store($this->cacheStore)
            ->remember($cacheKey, $this->expiration, function () {
                return $this->getDirectAndRolePermissions();
            });
    }

    /**
     * Get permissions from DB for a specific user.
     */
    protected function getDirectAndRolePermissions(): array
    {
        $directPermissions = auth()->user()->permissions()->where('guard_name', auth()->getDefaultDriver())->pluck('name')->toArray();
        $rolePermissions = auth()->user()->roles()->where('guard_name', auth()->getDefaultDriver())->get()
            ->flatMap(function ($role) {
                return $role->permissions()->where('guard_name', auth()->getDefaultDriver())->pluck('name')->toArray();
            })->toArray();

        return array_merge($directPermissions, $rolePermissions);
    }

    public function getPermissionsViaRoles()
    {
        return auth()->user()->roles()->where('guard_name', auth()->getDefaultDriver())->get()
            ->flatMap(function ($role) {
                return $role->permissions()->where('guard_name', auth()->getDefaultDriver())->pluck('name')->toArray();
            })->toArray();
    }

    /**
     * Clear all cache.
     */
    public function clearCache(): void
    {
        if (!$this->cacheEnabled) {
            return;
        }

        Cache::store($this->cacheStore)->forget($this->getPermissionsCacheKey());
        Cache::store($this->cacheStore)->forget($this->getRolesCacheKey());
    }

    /**
     * Clear cache for a specific user.
     */
    public function clearUserPermissionCache(int $userId = null): void
    {
        if (!$this->cacheEnabled) {
            return;
        }

        $cacheKey = $this->getUserPermissionsCacheKey($userId);
        Cache::store($this->cacheStore)->forget($cacheKey);
    }

    /**
     * Cache key for permissions.
     */
    protected function getPermissionsCacheKey(): string
    {
        return $this->cacheKey . '.permissions';
    }

    /**
     * Cache key for roles.
     */
    protected function getRolesCacheKey(): string
    {
        return $this->cacheKey . '.roles';
    }

    /**
     * Cache key for user permissions.
     */
    protected function getUserPermissionsCacheKey(int $userId = null): string
    {
        $guardName = auth()->getDefaultDriver();
        $userId = $userId ?? auth()->user()->id;
        return $this->cacheKey . '.user.' . $userId . '.' . $guardName;
    }
}