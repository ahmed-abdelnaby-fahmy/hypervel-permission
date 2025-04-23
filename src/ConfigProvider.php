<?php

declare(strict_types=1);

namespace Hypervel\Permission;

use Hypervel\Permission\Contracts\PermissionContract;
use Hypervel\Permission\Contracts\RoleContract;
use Hypervel\Permission\Models\Permission;
use Hypervel\Permission\Models\Role;
use Hypervel\Permission\Services\PermissionRegistrar;
class ConfigProvider
{
    public function __invoke(): array
    {
        return [
            'dependencies' => [
                PermissionContract::class        => Permission::class,
                RoleContract::class              => Role::class,

                PermissionRegistrar::class       => PermissionRegistrar::class,
            ],

            'publish' => [
                [
                    'id'          => 'permission-config',
                    'description' => 'The permission & role config for Hypervel.',
                    'source'      => __DIR__ . '/../publish/config/permission.php',
                    'destination' => BASE_PATH . '/config/autoload/permission.php',
                ],
                [
                    'id'          => 'permission-migrations',
                    'description' => 'Create permission & role tables.',
                    'source'      => __DIR__ . '/../publish/migrations/create_permission_tables.php',
                    'destination' => BASE_PATH . '/database/migrations/2025_04_21_091309_create_permission_tables.php',
                ],
            ],
        ];
    }
}
