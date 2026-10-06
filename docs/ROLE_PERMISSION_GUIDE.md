# Complete Guide: Role & Permission Configuration

This guide explains how to configure and implement **Role & Permission-based Access Control (RBAC)** in this Laravel 13 Point of Sale (POS) backend.

---

## 1. Concepts: Authentication vs. Authorization

* **Authentication (`auth:sanctum`)**: Verifies **who** is making the request (*"Is this a valid, logged-in user?"*). Returns `401 Unauthorized` if invalid.
* **Authorization / Role Check (`role:admin`)**: Verifies **what** the logged-in user is allowed to do (*"Is this user an Admin or a Cashier?"*). Returns `403 Forbidden` if denied.

In this POS application, users have one of three roles defined in the `users` table:
* **`admin`**: Full access to everything (users, reports, products, sales).
* **`manager`**: Inventory, products, categories, stock adjustments, and sales.
* **`cashier`**: Point of sale ringing up orders, payments, and product lookup.

---

## 2. Approach 1: Lightweight Role Middleware (Recommended)

Since the `users` table already has a `role` column, creating a custom middleware is the fastest, cleanest, and zero-dependency solution.

### Step 2.1: Create `CheckRole` Middleware

Create `app/Http/Middleware/CheckRole.php`:

```php
<?php

namespace App\Http\Middleware;

use Closure;
use Illuminate\Http\Request;
use Symfony\Component\HttpFoundation\Response;

class CheckRole
{
    /**
     * Handle an incoming request.
     *
     * @param  \Closure(\Illuminate\Http\Request): (\Symfony\Component\HttpFoundation\Response)  $next
     * @param  string  ...$roles  One or more allowed roles (e.g. 'admin', 'manager')
     */
    public function handle(Request $request, Closure $next, string ...$roles): Response
    {
        $user = $request->user();

        // 1. Verify user is logged in
        if (! $user) {
            return response()->json([
                'message' => 'Unauthenticated.',
            ], 401);
        }

        // 2. Verify user has at least one of the allowed roles
        if (! in_array($user->role, $roles, true)) {
            return response()->json([
                'message'        => 'Forbidden: You do not have permission to access this resource.',
                'required_roles' => $roles,
                'your_role'      => $user->role,
            ], 403);
        }

        return $next($request);
    }
}
```

---

### Step 2.2: Register Middleware Alias in `bootstrap/app.php`

In Laravel 13, register the alias inside the `withMiddleware` closure in `bootstrap/app.php`:

```php
<?php

use App\Http\Middleware\CheckRole; // 1. Import middleware
use Illuminate\Foundation\Application;
use Illuminate\Foundation\Configuration\Middleware;

return Application::configure(basePath: dirname(__DIR__))
    ->withRouting(
        web: __DIR__.'/../routes/web.php',
        api: __DIR__.'/../routes/api.php',
        commands: __DIR__.'/../routes/console.php',
        health: '/up',
    )
    ->withMiddleware(function (Middleware $middleware): void {
        // 2. Register 'role' alias:
        $middleware->alias([
            'role' => CheckRole::class,
        ]);
    })
    ->create();
```

---

### Step 2.3: Add Role Helper Methods to `app/Models/User.php`

Add convenient helper methods to check roles in your application code:

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens;

    /**
     * Check if user matches a given role or array of roles.
     */
    public function hasRole(string|array $roles): bool
    {
        if (is_array($roles)) {
            return in_array($this->role, $roles, true);
        }

        return $this->role === $roles;
    }

    public function isAdmin(): bool
    {
        return $this->role === 'admin';
    }

    public function isManager(): bool
    {
        return $this->role === 'manager';
    }

    public function isCashier(): bool
    {
        return $this->role === 'cashier';
    }
}
```

---

### Step 2.4: Protect Routes by Role in `routes/api.php`

Combine `auth:sanctum` with `role:...` to group endpoints by permission:

```php
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// Public login route
Route::post('/login', [AuthController::class, 'login']);

// Authenticated group (Require valid Sanctum token)
Route::middleware('auth:sanctum')->group(function (): void {
    
    // Auth profile & session
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/refresh', [AuthController::class, 'refresh']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // ========================================================
    // 1. ADMIN ONLY: Staff & User Management
    // ========================================================
    Route::middleware('role:admin')->group(function (): void {
        require __DIR__.'/apis/user.php';
    });

    // ========================================================
    // 2. ADMIN & MANAGER: Categories & Stock Movements
    // ========================================================
    Route::middleware('role:admin,manager')->group(function (): void {
        require __DIR__.'/apis/category.php';
        require __DIR__.'/apis/stock-movement.php';
    });

    // ========================================================
    // 3. ALL STAFF (Admin, Manager, Cashier): Sales, Products, Payments
    // ========================================================
    Route::middleware('role:admin,manager,cashier')->group(function (): void {
        require __DIR__.'/apis/product.php';
        require __DIR__.'/apis/sale.php';
        require __DIR__.'/apis/sale-item.php';
        require __DIR__./apis/payment.php';
        require __DIR__.'/apis/order.php';
    });

});
```

---

## 3. Approach 2: Sanctum Token Abilities (Built-In Permissions)

Laravel Sanctum supports **Token Abilities** out-of-the-box. When a user logs in, you can attach specific abilities to their token based on their role.

### Step 3.1: Assign Abilities on Login (`AuthController.php`)

```php
public function login(Request $request): JsonResponse
{
    // ... validate credentials ...

    // Define permissions based on role
    $abilities = match ($user->role) {
        'admin'   => ['*'], // wildcard: all abilities
        'manager' => ['products:manage', 'stock:manage', 'sales:manage'],
        'cashier' => ['products:read', 'sales:create', 'payments:create'],
        default   => ['products:read'],
    };

    // Issue token with specific abilities
    $token = $user->createToken('pos-client', $abilities)->plainTextToken;

    return response()->json([
        'token'     => $token,
        'abilities' => $abilities,
        'user'      => $user,
    ]);
}
```

### Step 3.2: Protect Routes with Sanctum Ability Middleware

Sanctum includes two built-in middleware:
* `ability:permission1,permission2` (User token must have **any** of the abilities)
* `abilities:permission1,permission2` (User token must have **all** of the abilities)

In `routes/api.php`:
```php
// Only tokens with 'products:manage' can create/update/delete products
Route::middleware(['auth:sanctum', 'ability:products:manage'])->group(function () {
    Route::post('/products', [ProductController::class, 'store']);
    Route::put('/products/{id}', [ProductController::class, 'update']);
    Route::delete('/products/{id}', [ProductController::class, 'destroy']);
});
```

---

## 4. Approach 3: Spatie Laravel-Permission (Enterprise Dynamic Roles)

If your project requires creating dynamic roles and permissions in a database UI (where an admin checks boxes to give custom permissions), use the package **`spatie/laravel-permission`**.

### Installation:
```bash
composer require spatie/laravel-permission
php artisan vendor:publish --provider="Spatie\Permission\PermissionServiceProvider"
php artisan migrate
```

### User Model:
```php
use Spatie\Permission\Traits\HasRoles;

class User extends Authenticatable
{
    use HasApiTokens, HasRoles;
}
```

### Route Protection:
```php
Route::middleware(['auth:sanctum', 'role:admin'])->group(function () { ... });
Route::middleware(['auth:sanctum', 'permission:edit-products'])->group(function () { ... });
```

---

## 5. Testing Role Protection with Pest (`tests/Feature/RolePermissionTest.php`)

```php
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('cashier cannot access admin user management', function () {
    $cashier = User::factory()->create(['role' => 'cashier']);
    Sanctum::actingAs($cashier);

    $this->getJson('/api/users')
        ->assertForbidden(); // HTTP 403 Forbidden
});

test('admin can access user management', function () {
    $admin = User::factory()->create(['role' => 'admin']);
    Sanctum::actingAs($admin);

    $this->getJson('/api/users')
        ->assertOk(); // HTTP 200 OK
});
```

---

## 6. Frontend Integration (React / Vite)

In your React frontend, you can store the user's role on login and hide unauthorized UI elements:

```jsx
// Example: Navigation component
function Navigation() {
  const user = JSON.parse(localStorage.getItem('auth_user') || '{}');

  return (
    <nav>
      <Link to="/products">Products</Link>
      <Link to="/sales">Sales</Link>

      {/* Only show Users Management link to Admins */}
      {user.role === 'admin' && (
        <Link to="/users">Manage Users</Link>
      )}

      {/* Only show Stock Movements to Admin and Manager */}
      {['admin', 'manager'].includes(user.role) && (
        <Link to="/stock-movements">Stock Movements</Link>
      )}
    </nav>
  );
}
```

---

## Summary Recommendation

* **For this POS Project**: Use **Approach 1 (`CheckRole` middleware)**. It directly leverages your existing `role` database column (`admin`, `manager`, `cashier`), requires zero external packages, and is the easiest to understand and maintain.
