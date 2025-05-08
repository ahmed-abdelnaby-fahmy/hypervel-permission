<?php

namespace Hypervel\Permission\Models;


use Hypervel\Database\Eloquent\Model;
use Hypervel\Permission\Contracts\PermissionContract;
use Hypervel\Permission\Services\PermissionRegistrar;

class Permission extends Model implements PermissionContract
{
    protected array $fillable = ['name', 'guard_name'];


    /**
     * Find a permission by its name and (optional) guard.
     *
     * @param  string      $name
     * @param  string|null $guardName
     * @return static
     */
    public static function findByName(string $name, ?string $guardName = null)
    {
        $guard = $guardName ?? config('auth.defaults.guard');

        return static::where('name', $name)
            ->where('guard_name', $guard)
            ->first();
    }

    /**
     * Roles that have this permission.
     */
    public function roles(): \Hyperf\Database\Model\Relations\BelongsToMany
    {
        return $this->belongsToMany(
            Role::class,
            config('permission.table_names.role_has_permissions'),
            'permission_id',
            'role_id'
        );
    }

    protected function boot(): void
    {
        parent::boot();

        static::registerCallback('saved', function () {
            app(PermissionRegistrar::class)->clearCache();
            app(PermissionRegistrar::class)->clearUserPermissionCache();
        });

        static::registerCallback('deleted', function () {
            app(PermissionRegistrar::class)->clearCache();
            app(PermissionRegistrar::class)->clearUserPermissionCache();
        });
    }

}
