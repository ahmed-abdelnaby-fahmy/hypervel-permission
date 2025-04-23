<?php

return [

    /*
    |--------------------------------------------------------------------------
    | Cache Store & Key
    |--------------------------------------------------------------------------
    |
    | You may specify a cache store (or "default" to use the default store)
    | and a cache key. Permissions & roles are cached to speed up checks.
    |
    */
    'cache' => [
        'store'          => env('PERMISSION_CACHE_STORE', 'default'),
        'key'            => env('PERMISSION_CACHE_KEY', 'hypervel.permission.cache'),
        'expiration'     => env('PERMISSION_CACHE_TTL', 3600), // seconds
    ],

    /*
    |--------------------------------------------------------------------------
    | Table Names
    |--------------------------------------------------------------------------
    */
    'table_names' => [
        'permissions'           => 'permissions',
        'roles'                 => 'roles',
        'model_has_permissions' => 'model_has_permissions',
        'model_has_roles'       => 'model_has_roles',
        'role_has_permissions'  => 'role_has_permissions',
    ],

    /*
    |--------------------------------------------------------------------------
    | Column Names
    |--------------------------------------------------------------------------
    */
    'column_names' => [
        'model_morph_key' => 'model_id',
    ],

];
