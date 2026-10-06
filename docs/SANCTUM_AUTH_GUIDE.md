# Complete Guide: Laravel Sanctum Authentication

This guide explains how to set up, configure, and use **Laravel Sanctum** (Personal Access Tokens) step-by-step for new learners.

---

## What is Laravel Sanctum?

Sanctum is Laravel's official, built-in API authentication system.
* **How it works**: When a user logs in with email and password, the server generates a secure, random token (e.g., `1|nK3...`), hashes it, and stores it in your database (`personal_access_tokens` table).
* **The Client**: Stores this token and sends it in the HTTP request header:
  ```http
  Authorization: Bearer 1|nK3...
  ```
* **The Server**: Checks the token against the database on each request.
* **Revocation**: You can instantly log out a user or revoke specific devices by deleting the token from the database.

---

## Step 1: Install & Migration

Sanctum is already included with Laravel 11/12/13.

1. Ensure the `personal_access_tokens` table exists by running:
   ```bash
   php artisan migrate
   ```
2. Check that `config/sanctum.php` exists. (If missing, publish it via `php artisan vendor:publish --provider="Laravel\Sanctum\SanctumServiceProvider"`).

---

## Step 2: Add `HasApiTokens` to the User Model

Open `app/Models/User.php`. You must add the `HasApiTokens` trait so your user model can generate tokens via `$user->createToken()`.

```php
<?php

namespace App\Models;

use Illuminate\Foundation\Auth\User as Authenticatable;
use Illuminate\Notifications\Notifiable;
use Illuminate\Database\Eloquent\Factories\HasFactory;
use Laravel\Sanctum\HasApiTokens; // 1. Import Sanctum trait

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable; // 2. Add trait to class

    protected $fillable = [
        'name',
        'email',
        'password',
        'role',
        'status',
    ];

    protected function casts(): array
    {
        return [
            'email_verified_at' => 'datetime',
            'password' => 'hashed', // Automatically hashes passwords!
        ];
    }
}
```

---

## Step 3: Create the Authentication Controller

Create a dedicated controller to handle `login`, `logout`, and fetching the current user profile.

Create `app/Http/Controllers/AuthController.php`:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    /**
     * User Login: Verify credentials and issue a Sanctum token.
     */
    public function login(Request $request): JsonResponse
    {
        // 1. Validate incoming credentials
        $credentials = $request->validate([
            'email'    => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        // 2. Find user by email
        $user = User::where('email', $credentials['email'])->first();

        // 3. Verify password hash
        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json([
                'message' => 'Invalid email or password.',
            ], 401);
        }

        // 4. Check account status (optional business rule)
        if ($user->status !== 'active') {
            return response()->json([
                'message' => 'Your account is inactive. Please contact support.',
            ], 403);
        }

        // 5. Create Sanctum token
        // 'pos-token' is a label for the device/client
        $token = $user->createToken('pos-token')->plainTextToken;

        // 6. Return token and user details to client
        return response()->json([
            'message'    => 'Login successful',
            'token'      => $token,
            'token_type' => 'Bearer',
            'user'       => [
                'id'    => $user->id,
                'name'  => $user->name,
                'email' => $user->email,
                'role'  => $user->role,
            ],
        ], 200);
    }

    /**
     * Get Current Authenticated User profile.
     */
    public function me(Request $request): JsonResponse
    {
        return response()->json($request->user());
    }

    /**
     * User Logout: Revoke current Sanctum token.
     */
    public function logout(Request $request): JsonResponse
    {
        // Safely delete the token used for the current request
        $request->user()?->currentAccessToken()?->delete();

        return response()->json([
            'message' => 'Logged out successfully',
        ], 200);
    }
}
```

---

## Step 4: Register Routes in `routes/api.php`

Open `routes/api.php` and configure public vs protected routes:

```php
<?php

use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

// ==========================================
// Public Routes (No Token Required)
// ==========================================
Route::post('/login', [AuthController::class, 'login']);

// ==========================================
// Protected Routes (Require Bearer Token)
// ==========================================
Route::middleware('auth:sanctum')->group(function (): void {
    // Auth helpers
    Route::get('/me', [AuthController::class, 'me']);
    Route::post('/logout', [AuthController::class, 'logout']);

    // Resource routes (Products, Categories, Sales, etc.)
    require __DIR__.'/apis/product.php';
    require __DIR__.'/apis/category.php';
    require __DIR__.'/apis/sale.php';
    require __DIR__.'/apis/sale-item.php';
    require __DIR__.'/apis/payment.php';
    require __DIR__.'/apis/stock-movement.php';
    require __DIR__.'/apis/user.php';
});
```

---

## Step 5: How Clients Use the Token

### 1. Login Request
```bash
curl -X POST http://localhost:8000/api/login \
  -H "Accept: application/json" \
  -H "Content-Type: application/json" \
  -d '{"email": "cashier@example.com", "password": "password123"}'
```

**Response:**
```json
{
  "message": "Login successful",
  "token": "1|raY8P2Z0d4LqF...",
  "token_type": "Bearer",
  "user": {
    "id": 1,
    "name": "Cashier One",
    "email": "cashier@example.com",
    "role": "cashier"
  }
}
```

### 2. Calling Protected Endpoints
Send the token in the `Authorization` header:
```bash
curl -X GET http://localhost:8000/api/products \
  -H "Accept: application/json" \
  -H "Authorization: Bearer 1|raY8P2Z0d4LqF..."
```

---

## Step 6: Testing with Pest (`tests/Feature/PosApiTest.php`)

In automated tests, authenticate mock users using `Sanctum::actingAs()`:

```php
use App\Models\User;
use Laravel\Sanctum\Sanctum;

test('authenticated user can view products', function () {
    $user = User::factory()->create();

    // Tells Laravel to treat this user as authenticated via Sanctum
    Sanctum::actingAs($user);

    $this->getJson('/api/products')
        ->assertOk();
});

test('unauthenticated request receives 401', function () {
    $this->getJson('/api/products')
        ->assertUnauthorized(); // 401
});
```
