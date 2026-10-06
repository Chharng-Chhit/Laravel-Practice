# Troubleshooting Guide: How to Fix Missing `personal_access_tokens` Table

This guide explains how to restore and fix the **`personal_access_tokens`** table if someone accidentally dropped the table in the database, deleted the migration file, or replaced the database schema.

---

## 1. Symptoms & Error Messages

If the `personal_access_tokens` table is missing, you will see one of these errors when users attempt to log in (`createToken()`) or when calling API routes with `auth:sanctum`:

```text
SQLSTATE[42S02]: Base table or view not found: 1146 Table 'pos_db.personal_access_tokens' doesn't exist
```
or
```text
QueryException: Table 'personal_access_tokens' doesn't exist in .../vendor/laravel/sanctum/src/HasApiTokens.php
```

---

## 2. Solution Scenarios

Choose the scenario that matches your situation:

---

### Scenario A: The Migration File Still Exists, But the Table Was Dropped in Database

If someone dropped the table in MySQL/SQLite, running `php artisan migrate` might show:
```text
INFO  Nothing to migrate.
```
**Why this happens:** Laravel tracks executed migrations in the `migrations` table. If the migration was already recorded as "ran", Laravel skips it.

#### How to Fix:
1. **Remove the record from the `migrations` table** using Artisan Tinker:
   ```bash
   php artisan tinker --execute "DB::table('migrations')->where('migration', 'like', '%personal_access_tokens%')->delete();"
   ```

2. **Now re-run migrations**:
   ```bash
   php artisan migrate
   ```
   Laravel will detect the migration has not run yet and create the `personal_access_tokens` table.

> ⚠️ **Note (Local Development only)**: If you don't mind resetting test data, you can simply run:
> ```bash
> php artisan migrate:fresh --seed
> ```

---

### Scenario B: The Migration File Was Accidentally Deleted from `database/migrations/`

If someone deleted the file `database/migrations/*_create_personal_access_tokens_table.php`:

#### Method 1: Re-publish with Artisan (Fastest)
Run the Sanctum vendor publish command:
```bash
php artisan vendor:publish --tag=sanctum-migrations
```
This automatically restores the migration file to `database/migrations/`. Then run:
```bash
php artisan migrate
```

#### Method 2: Manually Re-create the Migration File
1. Generate a new migration file:
   ```bash
   php artisan make:migration create_personal_access_tokens_table
   ```

2. Open the newly generated migration in `database/migrations/` and replace its content with this exact schema:

```php
<?php

use Illuminate\Database\Migrations\Migration;
use Illuminate\Database\Schema\Blueprint;
use Illuminate\Support\Facades\Schema;

return new class extends Migration
{
    /**
     * Run the migrations.
     */
    public function up(): void
    {
        Schema::create('personal_access_tokens', function (Blueprint $table) {
            $table->id();
            $table->morphs('tokenable'); // Creates tokenable_type and tokenable_id
            $table->text('name');
            $table->string('token', 64)->unique();
            $table->text('abilities')->nullable();
            $table->timestamp('last_used_at')->nullable();
            $table->timestamp('expires_at')->nullable()->index();
            $table->timestamps();
        });
    }

    /**
     * Reverse the migrations.
     */
    public function down(): void
    {
        Schema::dropIfExists('personal_access_tokens');
    }
};
```

3. Run migrations:
   ```bash
   php artisan migrate
   ```

---

### Scenario C: Emergency SQL Fix (Create Table Directly via Database Client)

If you cannot run `php artisan migrate` (e.g. on a live server or phpMyAdmin), run the raw SQL query directly:

#### For MySQL / MariaDB:
```sql
CREATE TABLE IF NOT EXISTS `personal_access_tokens` (
  `id` bigint unsigned NOT NULL AUTO_INCREMENT,
  `tokenable_type` varchar(255) COLLATE utf8mb4_unicode_ci NOT NULL,
  `tokenable_id` bigint unsigned NOT NULL,
  `name` text COLLATE utf8mb4_unicode_ci NOT NULL,
  `token` varchar(64) COLLATE utf8mb4_unicode_ci NOT NULL,
  `abilities` text COLLATE utf8mb4_unicode_ci,
  `last_used_at` timestamp NULL DEFAULT NULL,
  `expires_at` timestamp NULL DEFAULT NULL,
  `created_at` timestamp NULL DEFAULT NULL,
  `updated_at` timestamp NULL DEFAULT NULL,
  PRIMARY KEY (`id`),
  UNIQUE KEY `personal_access_tokens_token_unique` (`token`),
  KEY `personal_access_tokens_tokenable_type_tokenable_id_index` (`tokenable_type`,`tokenable_id`),
  KEY `personal_access_tokens_expires_at_index` (`expires_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
```

#### For SQLite:
```sql
CREATE TABLE IF NOT EXISTS "personal_access_tokens" (
  "id" integer primary key autoincrement not null,
  "tokenable_type" varchar not null,
  "tokenable_id" integer not null,
  "name" text not null,
  "token" varchar not null,
  "abilities" text,
  "last_used_at" datetime,
  "expires_at" datetime,
  "created_at" datetime,
  "updated_at" datetime
);
CREATE UNIQUE INDEX IF NOT EXISTS "personal_access_tokens_token_unique" on "personal_access_tokens" ("token");
CREATE INDEX IF NOT EXISTS "personal_access_tokens_tokenable_type_tokenable_id_index" on "personal_access_tokens" ("tokenable_type", "tokenable_id");
CREATE INDEX IF NOT EXISTS "personal_access_tokens_expires_at_index" on "personal_access_tokens" ("expires_at");
```

---

## 3. Important Note: Using Custom User Tables (e.g. `user_login`)

If you change Laravel's default `users` table to a custom table like `user_login`:

```php
Schema::create('user_login', function (Blueprint $table) {
    $table->id();
    $table->string('name');
    $table->string('password');
    $table->enum('role', ['admin', 'staff'])->default('staff');
    $table->rememberToken();
    $table->timestamps();
});
```

You must ensure two things for Sanctum to work:

1. **In your Model (`app/Models/User.php` or `UserLogin.php`)**:
   Explicitly tell Eloquent your table name:
   ```php
   class User extends Authenticatable
   {
       use HasApiTokens, HasFactory, Notifiable;

       // Specify custom table name if not 'users'
       protected $table = 'user_login';
   }
   ```

2. **How Sanctum stores tokens**:
   Sanctum uses polymorphic relations (`morphs('tokenable')`):
   * `tokenable_type`: Stores the class name (`App\Models\User`).
   * `tokenable_id`: Stores the ID from your `user_login` table.
   As long as your model extends `Authenticatable` and uses `use HasApiTokens;`, Sanctum will link tokens to your `user_login` records seamlessly!

---

## 4. Verification Check

After fixing the table, verify in terminal that Sanctum can issue tokens:

```bash
php artisan tinker --execute "
  \$user = App\Models\User::first();
  if (\$user) {
      \$token = \$user->createToken('test-token')->plainTextToken;
      echo 'SUCCESS! Token generated: ' . \$token . PHP_EOL;
      \$user->tokens()->delete(); // cleanup
  } else {
      echo 'Create a user first to test.' . PHP_EOL;
  }
"
```
