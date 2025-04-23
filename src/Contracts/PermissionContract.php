<?php

namespace Hypervel\Permission\Contracts;


interface PermissionContract
{
    /**
     * Find a permission by its name and (optional) guard.
     *
     * @param  string      $name
     * @param  string|null $guardName
     * @return static
     */
    public static function findByName(string $name, string $guardName = null);
}
