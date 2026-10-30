# API and frontend setup

This guide connects the Laravel API in `pos-backend` to the React app in `pos-frontend`. The API uses Sanctum bearer tokens. It does not use the cookie and CSRF flow from the linked classroom guide.

## 1. Configure and start Laravel

From `pos-backend`, prepare the app if this is a fresh setup:

```bash
composer install
cp .env.example .env
php artisan key:generate
```

Configure the database in `.env`. The example file uses SQLite. Then create the database tables and start the API:

```bash
php artisan migrate
php artisan serve --host=localhost --port=8000
```

The API base URL is `http://localhost:8000/api`. Routes are already registered in `routes/api.php`; you do not need to run `php artisan install:api` for this project.

## 2. Configure CORS for React

Laravel's CORS middleware is enabled, but this project does not currently have a published `config/cors.php`. If the browser reports a CORS error, publish the configuration from `pos-backend`:

```bash
php artisan config:publish cors
```

In `config/cors.php`, allow the React development origin and API paths:

```php
'paths' => ['api/*'],
'allowed_methods' => ['*'],
'allowed_origins' => ['http://localhost:5173'],
'allowed_headers' => ['*'],
'supports_credentials' => false,
```

This API sends tokens in the `Authorization` header, so cookie credentials are not needed. Use the same host in both app URLs: this example uses `localhost` for both. If React runs on another port, set that exact origin in `allowed_origins`.

## 3. Configure and start React

In `pos-frontend`, create or update `.env.local`:

```dotenv
VITE_API_URL=http://localhost:8000/api
```

The current frontend API files default to port `9000`. This setting points them to Laravel on port `8000`. Restart Vite whenever you change `.env.local`.

Start the frontend in a second terminal:

```bash
cd ../pos-frontend
npm install
npm run dev
```

Vite usually serves the app at `http://localhost:5173`.

## 4. Connect the login and registration forms

The current `LoginForm` only logs submitted values to the console. It uses a `username` field, but the API expects `email`. Change the form field to `email`, then call the API from its submit handler.

For the Ant Design form, the email field can look like this:

```jsx
<Form.Item
  label="Email"
  name="email"
  rules={[{ required: true, type: 'email' }]}
>
  <Input />
</Form.Item>
```

Create `src/api/authApi.js` in `pos-frontend`:

```js
const API_BASE_URL = import.meta.env.VITE_API_URL || 'http://localhost:8000/api';

async function readResponse(response) {
  const data = await response.json().catch(() => ({}));

  if (!response.ok) {
    const validationMessage = Object.values(data.errors || {}).flat().join(' ');
    throw new Error(validationMessage || data.message || `Request failed (${response.status})`);
  }

  return data;
}

export async function login(email, password) {
  const response = await fetch(`${API_BASE_URL}/login`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ email, password }),
  });

  return readResponse(response);
}

export async function register(name, email, password) {
  const response = await fetch(`${API_BASE_URL}/register`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      'Content-Type': 'application/json',
    },
    body: JSON.stringify({ name, email, password }),
  });

  return readResponse(response);
}

export async function logout(token) {
  const response = await fetch(`${API_BASE_URL}/logout`, {
    method: 'POST',
    headers: {
      Accept: 'application/json',
      Authorization: `Bearer ${token}`,
    },
  });

  return readResponse(response);
}
```

Replace the form's console-only submit handler with a call to `login`. Save the returned user and token in your React auth state or context, and show errors to the user:

```js
const onFinish = async values => {
  try {
    const result = await login(values.email, values.password);
    setUser(result.data.user);
    setToken(result.data.token);
    setError('');
  } catch (error) {
    setError(error.message);
  }
};
```

`setUser`, `setToken`, and `setError` are examples; connect them to state or context in your app. Use the same pattern for registration with `register(name, email, password)`.

Keep the token out of source code. Avoid storing bearer tokens in `localStorage` in a browser app; choose a storage approach based on your app's security requirements.

## 5. Send the token on protected requests

Send the token as a bearer header when calling a protected endpoint. For example, load the signed-in user with `GET /api/user`:

```js
const response = await fetch(`${API_BASE_URL}/user`, {
  headers: {
    Accept: 'application/json',
    Authorization: `Bearer ${token}`,
  },
});

const user = await response.json();
```

When the user logs out, call `await logout(token)`, then clear the user and token from React state. The backend deletes the token, so it cannot be used again.

Only `/api/user` and `/api/logout` currently require authentication. The inventory resource routes are public in the current backend. Add `Authorization: Bearer <token>` to those requests if you protect them later.

## API request summary

| Action | Request | Body | Result |
| --- | --- | --- | --- |
| Register | `POST /api/register` | `name`, `email`, `password` | `201` and a user plus token |
| Login | `POST /api/login` | `email`, `password` | `200` and a user plus token |
| Current user | `GET /api/user` | None | `200` and the authenticated user; requires bearer token |
| Logout | `POST /api/logout` | None | `200`; requires bearer token and revokes it |

Registration passwords must be at least 8 characters and emails must be unique. The server assigns the `cashier` role; the frontend must not send a role. The user model hashes passwords when saving them.

## Common responses and troubleshooting

| Status | Meaning |
| --- | --- |
| `401` | Login credentials are invalid, or a protected request has no valid token |
| `422` | Validation failed, such as a missing field, short password, or duplicate email |
| `500` | An unexpected server error occurred; details are reported in Laravel's logs |

- **Browser CORS error:** check `config/cors.php` and make sure `allowed_origins` exactly matches the React URL.
- **Request goes to port 9000:** confirm `pos-frontend/.env.local` contains `VITE_API_URL=http://localhost:8000/api`, then restart Vite.
- **Protected request returns 401:** send the token from `result.data.token` in the `Authorization` header.
- **Login fails with 422:** the field must be named `email`, not `username`; check the password and validation message.
- **Database table missing:** run `php artisan migrate` in `pos-backend`.

The linked classroom guide describes first-party cookie authentication. That setup requires session and CSRF configuration; the current backend instead returns bearer tokens, so do not add `/sanctum/csrf-cookie`, `withCredentials`, or `withXSRFToken` for this API flow.
