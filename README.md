# Hypervel Permission

Role & Permission management for the **[Hypervel Framework](https://hypervel.dev)**, with first-class cache support.

> Effortlessly assign **roles** and **permissions** to your Hypervel users, speed-boosted by smart caching.

---

## Table of Contents

1. [Features](#features)
2. [Installation](#installation)
3. [Configuration](#configuration)
4. [Basic Usage](#basic-usage)
5. [Advanced Usage](#advanced-usage)
6. [Testing](#testing)

---

## Features

- 🔑 **Create** roles & permissions via models or artisan commands
- 🧑‍🤝‍🧑 **Assign** multiple roles to users (or vice versa)
- 🛂 **Authorize** via roles *or* direct permissions
- 🚀 **Cache** layer with configurable store, key & TTL
- 🛡 **Multi-guard** support out of the box
- 🔍 Clean, expressive API (`hasRole()`, `hasPermissionTo()`, …)
- 🧪 Thoroughly tested & production-ready

---

## Installation

```bash
composer require aef/hypervel-permission
```

## Configuration
Publish the config file and the migrations:

```bash
php artisan vendor:publish permission-config
php artisan vendor:publish permission-migrations
```
In the providers array, add the following line:

```php
'providers' => [
    // Other service providers...
    
    Hypervel\Permission\PermissionServiceProvider::class,
],
```
## Basic Usage
Creating permissions & roles

```php
use Hypervel\Permission\Models\Permission;
use Hypervel\Permission\Models\Role;

Permission::create(['name' => 'edit articles']);
Permission::create(['name' => 'delete articles']);

$writer = Role::create(['name' => 'writer']);
$writer->givePermissionTo('edit articles', 'delete articles');

$admin = Role::create(['name' => 'admin']);
$admin->givePermissionTo(Permission::all());
```

## Assigning and Revoking Roles/Permissions
```php
$user = \App\Models\User::find(1);

$user->assignRole('writer', 'admin');
$user->syncRoles(['writer', 'admin']);

$user->removeRole('admin');

$user->givePermissionTo('publish articles');
$user->syncPermissions(['edit articles', 'delete articles']);

$user->revokePermissionTo('edit articles');  // Remove single permission
$user->revokePermissionTo('edit articles', 'delete articles');  // Remove multiple
```

## Checking abilities
```php
$user->hasPermissionTo('edit articles');          // true/false
$user->hasRole('writer');                        // true/false
$user->hasAnyRole(['writer', 'moderator']);      // true/false
$user->hasAllPermissions(['edit', 'publish']);   // true/false
```
### Check if the current user has permission

```php
Gate::allows('edit articles')
```

## Getting all permissions
```php
$user->getAllPermissions();      // direct + via roles
$user->getPermissionsViaRoles(); // only via roles
$user->permissions;              // only direct
```

## Advanced Usage

Caching is on by default and automatically flushed whenever a permission, role, or related pivot changes.

```php
'cache' => [
'store'      => env('PERMISSION_CACHE_STORE', 'redis'),
'key'        => env('PERMISSION_CACHE_KEY', 'hypervel.permission.cache'),
'expiration' => env('PERMISSION_CACHE_TTL', 3600), // seconds
],
```

## Multiple guards

```php
// creating
Permission::create(['name' => 'edit articles', 'guard_name' => 'api']);
Role::create(['name' => 'writer', 'guard_name' => 'api']);

// checking (guard auto-detected from current auth)
$user->hasPermissionTo('edit articles');        // defaults to current guard
$user->hasPermissionTo('edit articles', 'api'); // explicit
```