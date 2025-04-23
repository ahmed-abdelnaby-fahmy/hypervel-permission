<?php

namespace Hypervel\Permission\Contracts;


interface RoleContract
{
    /**
     * Find a role by its name or create and (optional) guard.
     * @param string $name
     * @param string|null $guardName
     * @return mixed
     */
    public static function findOrCreate(string $name,string $guardName = null): mixed;

    /**
     * Find a role by its name and (optional) guard.
     *
     * @param string $name
     * @param string|null $guardName
     * @return static
     */
    public static function findByName(string $name, string $guardName = null);
}
