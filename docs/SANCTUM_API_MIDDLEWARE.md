# Sanctum API middleware and authentication

This guide describes the API authentication setup for the POS backend, how Laravel's `auth:sanctum` guard processes a request, and what must be enabled before the business API is protected.

## Current backend state

The backend is Laravel 13 with `laravel/sanctum` 4.x. Sanctum's configuration and the `personal_access_tokens` migration are present.

Authentication is not currently applied to the POS resource routes:

- `routes/api.php` registers the `/api/user` endpoint with `auth:sanctum`.
- The routes under `routes/apis/` (users, products, categories, sales, orders, sale items, payments, and stock movements) have no authentication middleware and are public.
- `App\Models\User` does not use Sanctum's `HasApiTokens` trait, so the application has no built-in `createToken()` capability for issuing personal access tokens.
- There is no login or logout API endpoint yet. The frontend login form is currently presentation-only and does not call an authentication endpoint.
- There is no role authorization middleware or policy attached to these routes. Authenticating a user does not by itself enforce the user's `role` or `status`.

The test `tests/Feature/PosApiTest.php` currently calls the resource endpoints without logging in. Protecting those routes will require updating clients and tests to authenticate first.

## Backend startup

From the backend directory:

```bash
cd pos-backend
composer install
cp .env.example .env
php artisan key:generate
```

Set the database connection in `.env`, then apply migrations and start the API:

```bash
php artisan migrate
php artisan serve
```

The API is served below `/api` (for example, `http://localhost:8000/api/products`). The existing personal access token migration is included with the project, so `php artisan migrate` creates the `personal_access_tokens` table.

## Sanctum authentication modes

Sanctum supports two common request types:

1. **Bearer token**: a client sends `Authorization: Bearer <token>`. This is suited to mobile clients, third-party API clients, or a frontend that stores and sends an API token.
2. **First-party SPA session**: a browser SPA uses Laravel's session cookie and CSRF protection. This is suited to a frontend served from a trusted domain associated with the backend.

Both modes use the `auth:sanctum` route middleware. Sanctum first checks the guards listed by `config/sanctum.php` (currently `['web']`). If no session user is found, it checks the request for a personal access token. An unauthenticated request to a protected route receives a JSON `401` response; API exceptions are configured to render as JSON in `bootstrap/app.php`.

`auth:sanctum` is not the same as adding a `sanctum` entry to `config/auth.php`. Keep the normal `web` session guard and user provider there; the Sanctum route guard is provided by the package.

## Enable personal access tokens

Add Sanctum's token trait to `App\Models\User`:

```php
use Laravel\Sanctum\HasApiTokens;

class User extends Authenticatable
{
    use HasApiTokens, HasFactory, Notifiable;
}
```

The project already has the `personal_access_tokens` migration. Run migrations in each environment before using token authentication:

```bash
php artisan migrate
```

### Add token-based login and logout endpoints

For a bearer-token API, add `HasApiTokens` to `App\Models\User` as shown above, then create an authentication controller. This example validates the email and password, checks the password hash, and returns a Sanctum token:

```php
<?php

namespace App\Http\Controllers;

use App\Models\User;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Hash;

class AuthController extends Controller
{
    public function login(Request $request): JsonResponse
    {
        $credentials = $request->validate([
            'email' => ['required', 'email'],
            'password' => ['required', 'string'],
        ]);

        $user = User::where('email', $credentials['email'])->first();

        if (! $user || ! Hash::check($credentials['password'], $user->password)) {
            return response()->json(['message' => 'Invalid credentials.'], 401);
        }

        // If the application defines an active status, reject inactive accounts here.
        // For example: if ($user->status !== 'active') { ... }

        $token = $user->createToken('pos-client')->plainTextToken;

        return response()->json([
            'token' => $token,
            'token_type' => 'Bearer',
            'user' => $user,
        ]);
    }

    public function logout(Request $request): JsonResponse
    {
        $request->user()->currentAccessToken()->delete();

        return response()->json(null, 204);
    }
}
```

Register login outside the protected routes and logout inside them in `routes/api.php`. The existing `/api/user` route is already protected with `auth:sanctum`:

```php
use App\Http\Controllers\AuthController;
use Illuminate\Support\Facades\Route;

Route::post('/login', [AuthController::class, 'login']);

Route::post('/logout', [AuthController::class, 'logout'])->middleware('auth:sanctum');
```

Add `auth:sanctum` to the POS resource routes as described in [Protect routes with the Sanctum guard](#protect-routes-with-the-sanctum-guard). Keep `/api/login` outside that middleware so users can obtain a token.

The login endpoint is public because it is how a client obtains its first token. Logout and `/api/user` require a valid token. Apply rate limiting to login in production and use HTTPS. Avoid returning password fields; this project's `User` model hides its password in JSON by default. The `role` and `status` fields are not authorization rules by themselves, so enforce any account-status or role requirements explicitly.

After running migrations, a client can log in with JSON:

```bash
curl -X POST http://localhost:8000/api/login \
  -H 'Accept: application/json' \
  -H 'Content-Type: application/json' \
  -d '{"email":"cashier@example.com","password":"your-password"}'
```

The response includes the token once. Keep it on the client and send it with every protected API request. For a frontend using `fetch`, the calls are conceptually:

```js
const loginResponse = await fetch(`${API_BASE_URL}/login`, {
  method: 'POST',
  headers: { Accept: 'application/json', 'Content-Type': 'application/json' },
  body: JSON.stringify({ email, password }),
});
const { token, user } = await loginResponse.json();

const productsResponse = await fetch(`${API_BASE_URL}/products`, {
  headers: { Accept: 'application/json', Authorization: `Bearer ${token}` },
});
```

Treat tokens as secrets. For browser applications, prefer the first-party SPA cookie flow described below when it fits the deployment, since long-lived tokens in browser storage can be exposed by cross-site scripting. For mobile or non-browser clients, store tokens in the platform's secure storage. Logout calls `POST /api/logout` with the same bearer header and deletes the current token. Sanctum stores only a hash of a token and reveals the plain token at creation time. In production, choose an expiration/revocation policy; `config/sanctum.php` currently sets global `expiration` to `null`.

## Protect routes with the Sanctum guard

Apply `auth:sanctum` to individual routes or to a route group. For example, to require authentication for all POS resources:

```php
Route::middleware('auth:sanctum')->group(function (): void {
    require __DIR__.'/apis/product.php';
    require __DIR__.'/apis/category.php';
    require __DIR__.'/apis/stock-movement.php';
    require __DIR__.'/apis/sale.php';
    require __DIR__.'/apis/order.php';
    require __DIR__.'/apis/sale-item.php';
    require __DIR__.'/apis/payment.php';
    require __DIR__.'/apis/user.php';
});
```

Keep public routes, such as a login endpoint, outside that protected group. Alternatively, add `->middleware('auth:sanctum')` to each resource group in `routes/apis/` when they need different access rules.

An API request with a personal access token looks like this:

```bash
curl -H 'Accept: application/json' \
  -H 'Authorization: Bearer YOUR_TOKEN' \
  http://localhost:8000/api/products
```

Use `Accept: application/json` so errors and validation failures are returned as JSON. Do not put a real token in source control, logs, or screenshots.

## First-party SPA cookie setup

For browser session authentication, enable Sanctum's stateful API middleware in `bootstrap/app.php`:

```php
->withMiddleware(function (Middleware $middleware): void {
    $middleware->statefulApi();
})
```

Configure `SANCTUM_STATEFUL_DOMAINS` in `.env` to contain the frontend host and port, for example `localhost:5173,127.0.0.1:5173`. Configure the session cookie domain and CORS credentials/origins for the actual frontend and backend hosts. The frontend must send cookies (`credentials: 'include'`) and use Sanctum's CSRF-cookie flow before making state-changing requests. Keep the frontend and API on trusted related domains and use HTTPS outside local development.

In this mode, login is performed through Laravel's session guard. The SPA gets a CSRF cookie from `/sanctum/csrf-cookie`, then posts credentials to the configured session login endpoint. Subsequent API calls include the session cookie and CSRF token. `statefulApi()` is for this cookie-based flow; it is not required just to accept bearer tokens.

## Request flow

```text
Client request
    -> Laravel API routing (/api/...)
    -> route middleware `auth:sanctum`
    -> Sanctum checks configured session guards (web)
       -> session user found: authenticate as that user
       -> otherwise: inspect Authorization: Bearer token
          -> valid token: resolve token owner and authenticate request
          -> missing/invalid token: return 401 JSON
    -> controller validates input and performs the operation
    -> JSON response
```

For browser SPA requests, `statefulApi()` adds the stateful session, cookie, and CSRF middleware to eligible first-party requests before route handling. The route's `auth:sanctum` middleware still determines whether the request has an authenticated user.

## Authentication versus authorization

`auth:sanctum` answers **who is making this request?** It does not answer **may this user perform this operation?** The application stores `role` and `status` on users, but currently does not check them in API middleware, policies, or controllers. Add policies or explicit authorization middleware for rules such as restricting user management to admins, and reject inactive users where appropriate. Sanctum token abilities can further limit token capabilities, but they do not replace resource authorization.

## Useful checks

After implementing authentication and protecting routes, inspect registered API routes and exercise both unauthenticated and authenticated requests:

```bash
php artisan route:list --path=api
```

- Protected endpoint without a token/session: expect `401` JSON.
- Protected endpoint with an invalid or revoked bearer token: expect `401` JSON.
- Protected endpoint with a valid token: expect the normal controller response.
- Validation failure: expect `422` JSON with an `errors` object.

The current public resource routes will continue to return controller responses without a token until `auth:sanctum` is added to them.

## Common pitfalls & troubleshooting

### Why is the password hash always different?
- **Bcrypt salting**: Running `Hash::make('password')` generates a unique random salt each time, so the resulting hash string is never identical. Never compare hashes with `===`; always use `Hash::check($plainPassword, $hashedPassword)`.
- **Double-hashing trap**: In `App\Models\User`, `casts()` defines `'password' => 'hashed'`. If you call `Hash::make()` before passing the password to `User::create()` or updating the model, Eloquent hashes the string a second time. `Hash::check()` will then fail during login. Pass plain text to Eloquent model methods so it is hashed only once.

### Why does login fail?
1. **`404 Not Found`**: Ensure `Route::post('/login', [AuthController::class, 'login'])` is registered in `routes/api.php` outside the protected middleware group.
2. **`Call to undefined method App\Models\User::createToken()`**: Ensure `use Laravel\Sanctum\HasApiTokens;` is present in `App\Models\User`.
3. **`Table 'personal_access_tokens' doesn't exist`**: Run `php artisan migrate` to create the Sanctum table. See [FIX_PERSONAL_ACCESS_TOKENS_TABLE.md](./FIX_PERSONAL_ACCESS_TOKENS_TABLE.md) if the table was dropped.
4. **`401 Invalid credentials`**: Check for password double-hashing or incorrect credentials.

