<?php

namespace Hypervel\Permission\Models;

use Hyperf\Database\Model\Relations\BelongsToMany;
use Hypervel\Database\Eloquent\Model;
use Hypervel\Permission\Contracts\RoleContract;
use Hypervel\Permission\Traits\HasPermissions;

class Role extends Model implements RoleContract
{
    use HasPermissions;

    protected array $fillable = ['name', 'guard_name'];


    /**
     * Find a role by its name and (optional) guard.
     *
     * @param string $name
     * @param string|null $guardName
     * @return static
     */
    public static function findByName(string $name, ?string $guardName = null)
    {
        $guard = $guardName ?? config('auth.defaults.guard');

        return static::where('name', $name)
            ->where('guard_name', $guard)
            ->first();
    }

    public function boot(): void
    {
        static::registerCallback('creating', function ($model) {
            $model->guard_name = !empty($model->guard_name) ? $model->guard_name : config('auth.defaults.guard');
        });
    }

    /**
     * Permissions that belong to this role.
     */
    public function permissions(): BelongsToMany
    {
        return $this->belongsToMany(
            Permission::class,
            config('permission.table_names.role_has_permissions'),
            'role_id',
            'permission_id'
        );
    }

    /**
     * @param string $name
     * @param string|null $guardName
     * @return mixed
     */
    public static function findOrCreate(string $name, ?string $guardName = null): mixed
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        $role = Role::where('name', $name)->where('guard_name', $guardName)->first();
        if (!$role)
            $role = Role::create(['name' => $name, 'guard_name' => $guardName]);
        return $role;
    }

    /**
     * @param string $name
     * @param string|null $guardName
     * @return mixed
     */
    public static function findOrFail(string $name, string $guardName = null): mixed
    {
        $guardName = $guardName ?? config('auth.defaults.guard');
        return Role::where('name', $name)
            ->where('guard_name', $guardName)
            ->firstOrFail();
    }
}
