# Complete Guide: Role & Permission with `spatie/laravel-permission`

This is a dedicated, step-by-step guide to configuring and using the official **`spatie/laravel-permission`** package in this **Laravel 13** Point of Sale (POS) backend.

---

## 1. How `spatie/laravel-permission` Works

`spatie/laravel-permission` is the industry standard for Role-Based Access Control (RBAC) in Laravel.

Instead of hardcoding roles in your code, it creates 5 dedicated database tables:
* **`roles`**: Table for role names (e.g. `admin`, `manager`, `cashier`).
* **`permissions`**: Table for fine-grained permissions (e.g. `create products`, `delete users`, `process refund`).
* **`model_has_roles`**: Pivot table connecting users to roles.
* **`role_has_permissions`**: Pivot table assigning permissions to roles.
* **`model_has_permissions`**: Direct permissions assigned to specific users.

---

## 2. Installation & Setup

### Step 2.1: Install the Package
Run the composer require command:
```bash
composer require spatie/laravel-permission
```

### Step 2.2: Publish the Configuration & Migrations
```bash
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
```
This publishes:
* `config/permission.php`: Package configuration.
* A migration file under `database/migrations/` creating all permission tables.

### Step 2.3: Run Database Migrations
```bash
php artisan migrate
```

---

## 3. Configure the User Model

Open `app/Models/User.php` and add Spatie's `HasRoles` trait:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens;
use Spatie\Permission\Traits\HasRoles; // 1. Import trait

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable, HasRoles; // 2. Add trait here

    protected $fillable = [
        'name',
        'email',
        'password',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed',
        ];
    }
}
```

---

## 4. Register Middleware in `bootstrap/app.php` (Laravel 13)

In Laravel 13, register the route middleware aliases inside `bootstrap/app.php`:

```php
<?php

use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Exceptions;
use Illuminate\Foundation\Configuration\Middleware;
use Illuminate\Http\Request;
use Spatie\Permission\Exceptions\UnauthorizedException;
use Spatie\Permission\Middleware\PermissionMiddleware;
use Spatie\Permission\Middleware\RoleMiddleware;
use Spatie\Permission\Middleware\RoleOrPermissionMiddleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // Register Spatie Middleware aliases
        $middleware->alias([
            'role'               => RoleMiddleware::class,
            'permission'         => PermissionMiddleware::class,
            'role_or_permission' => RoleOrPermissionMiddleware::class,
        ]);
    })
    ->withExceptions(function (Exceptions $exceptions): void {
        // Return JSON 403 when Spatie denies permission in API requests
        $exceptions->render(function (UnauthorizedException $e, Request $request) {
            return response()->json([
                'message' => 'Forbidden: You do not have the required role or permission.',
            ], 403);
        });
    })
    ->create();
```

---

## 5. Seed Roles & Permissions (`RolePermissionSeeder.php`)

Create a seeder to define all your initial roles and permissions:

```bash
php artisan make:seeder RolePermissionSeeder
```

Open `database/seeders/RolePermissionSeeder.php`:

```php
<?php

namespace Database\Seeders;

use App\Models\User;
use Illuminate\Database\Seeder;
use Spatie\Permission\Models\Permission;
use Spatie\Permission\Models\Role;
use Spatie\Permission\PermissionRegistrar;

class RolePermissionSeeder extends Seeder
{
    public function run(): void
    {
        // 1. Reset cached roles and permissions
        app()[PermissionRegistrar::class]->forgetCachedPermissions();

        // 2. Create Permissions
        // Product permissions
        Permission::create(['name' => 'view products']);
        Permission::create(['name' => 'create products']);
        Permission::create(['name' => 'edit products']);
        Permission::create(['name' => 'delete products']);

        // User management permissions
        Permission::create(['name' => 'view users']);
        Permission::create(['name' => 'create users']);
        Permission::create(['name' => 'edit users']);
        Permission::create(['name' => 'delete users']);

        // Sales & Stock permissions
        Permission::create(['name' => 'create sales']);
        Permission::create(['name' => 'view sales']);
        Permission::create(['name' => 'refund sales']);
        Permission::create(['name' => 'manage stock']);

        // 3. Create Roles & Assign Permissions

        // Cashier Role
        $cashier = Role::create(['name' => 'cashier']);
        $cashier->givePermissionTo([
            'view products',
            'create sales',
            'view sales',
        ]);

        // Manager Role
        $manager = Role::create(['name' => 'manager']);
        $manager->givePermissionTo([
            'view products',
            'create products',
            'edit products',
            'create sales',
            'view sales',
            'refund sales',
            'manage stock',
        ]);

        // Admin Role (Gets ALL permissions)
        $admin = Role::create(['name' => 'admin']);
        $admin->givePermissionTo(Permission::all());

        // 4. Assign Roles to Demo Users
        $adminUser = User::firstOrCreate(
            ['email' => 'admin@pos.com'],
            ['name' => 'Admin User', 'password' => 'password123', 'status' => 'active']
        );
        $adminUser->assignRole('admin');

        $cashierUser = User::firstOrCreate(
            ['email' => 'cashier@pos.com'],
            ['name' => 'Cashier User', 'password' => 'password123', 'status' => 'active']
        );
        $cashierUser->assignRole('cashier');
    }
}
```

Run the seeder:
```bash
php artisan db:seed --class=RolePermissionSeeder
```

---

## 6. Protecting Routes in `routes/api.php`

You can protect your API routes by **role**, by **permission**, or by combining them with Sanctum.

Open `routes/api.php`:

```php
<?php

use App\Http\Controllers\AuthController;
use App\Http\Controllers\ProductController;
use App\Http\Controllers\UserController;
use Illuminate\Support\Facades\Route;

// Public routes
Route::post('/login', [AuthController::class, 'login']);

// Authenticated routes
Route::middleware('auth:sanctum')->group(function (): void {

    // ============================================================
    // Option A: Protect by ROLE
    // ============================================================
    
    // Only users with 'admin' role
    Route::middleware('role:admin')->group(function (): void {
        require __DIR__.'/apis/user.php';
    });

    // Users with either 'admin' OR 'manager' role (separated by pipe |)
    Route::middleware('role:admin|manager')->group(function (): void {
        require __DIR__.'/apis/category.php';
        require __DIR__.'/apis/stock-movement.php';
    });

    // ============================================================
    // Option B: Protect by fine-grained PERMISSION
    // ============================================================
    
    // Anyone who has the 'view products' permission (Cashier, Manager, Admin)
    Route::get('/products', [ProductController::class, 'index'])
        ->middleware('permission:view products');

    // Only someone with 'create products' permission
    Route::post('/products', [ProductController::class, 'store'])
        ->middleware('permission:create products');

    // Only someone with 'delete products' permission
    Route::delete('/products/{id}', [ProductController::class, 'destroy'])
        ->middleware('permission:delete products');
});
```

---

## 7. Working with Roles & Permissions in Code

Here are the most useful methods provided by Spatie on the `$user` model:

### Checking Roles:
```php
// Check single role
if ($user->hasRole('admin')) { ... }

// Check any of several roles
if ($user->hasAnyRole(['admin', 'manager'])) { ... }

// Check if user has all specified roles
if ($user->hasAllRoles(['cashier', 'supervisor'])) { ... }
```

### Checking Permissions:
```php
// Check single permission (via direct assignment OR via their role)
if ($user->can('delete products')) { ... }
// or:
if ($user->hasPermissionTo('delete products')) { ... }

// Check if user has any permission
if ($user->hasAnyPermission(['edit products', 'delete products'])) { ... }
```

### Assigning & Removing Roles/Permissions:
```php
// Assign role
$user->assignRole('manager');

// Remove role
$user->removeRole('cashier');

// Sync roles (replaces all current roles with new ones)
$user->syncRoles(['admin']);

// Direct permission assignment
$user->givePermissionTo('refund sales');
$user->revokePermissionTo('refund sales');
```

---

## 8. Testing Spatie Permissions with Pest

In your test files (e.g. `tests/Feature/PosApiTest.php`):

```php
use App\Models\User;
use Laravel\Sanctum\Sanctum;
use Spatie\Permission\Models\Role;
use Spatie\Permission\Models\Permission;

beforeEach(function () {
    // Create test roles
    Role::create(['name' => 'admin']);
    Role::create(['name' => 'cashier']);
});

test('cashier cannot delete products', function () {
    $cashier = User::factory()->create();
    $cashier->assignRole('cashier');

    Sanctum::actingAs($cashier);

    $this->deleteJson('/api/products/1')
        ->assertForbidden(); // HTTP 403
});

test('admin can delete products', function () {
    $admin = User::factory()->create();
    $admin->assignRole('admin');

    Sanctum::actingAs($admin);

    $this->deleteJson('/api/products/1')
        ->assertNoContent(); // HTTP 204
});
```

---

## 9. Returning Roles & Permissions to Frontend (React)

In your `AuthController::login` or `/api/me` endpoint, include the user's roles and permissions in the JSON response:

```php
public function me(Request $request): JsonResponse
{
    $user = $request->user();

    return response()->json([
        'user' => [
            'id'          => $user->id,
            'name'        => $user->name,
            'email'       => $user->email,
            'roles'       => $user->getRoleNames(),           // e.g. ["admin"]
            'permissions' => $user->getAllPermissions()->pluck('name'), // e.g. ["create products", ...]
        ],
    ]);
}
```

In your React components, you can easily control UI visibility:
```jsx
const user = JSON.parse(localStorage.getItem('auth_user') || '{}');

// Show Delete button only if user has permission
{user.permissions?.includes('delete products') && (
  <Button danger onClick={handleDelete}>Delete Product</Button>
)}
```
