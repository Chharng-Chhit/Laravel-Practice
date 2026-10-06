# POS Backend API Documentation & Learner's Guide

This documentation provides a comprehensive guide to the POS backend API architecture, explaining how **controllers**, **search queries**, **filters**, and **pagination** work step-by-step for new learners.

---

## 1. Architectural Pattern: The 5-Step Controller Pipeline

Every resource controller in this application follows a standardized 5-step pattern inside its `index()` method. This ensures predictable, readable, and maintainable code.

```
Incoming HTTP Request (e.g. ?search=water&category_id=2&per_page=10)
    │
    ▼
┌──────────────────────────────────────────────┐
│  Step 1: Validate Input Parameters           │ -> Checks types, ranges, existence
└──────────────────────────────────────────────┘
    │
    ▼
┌──────────────────────────────────────────────┐
│  Step 2: Base Query & Eager Loading          │ -> Prevents N+1 query problem via with()
└──────────────────────────────────────────────┘
    │
    ▼
┌──────────────────────────────────────────────┐
│  Step 3: Keyword Search (SQL LIKE %term%)    │ -> Groups OR conditions in subquery
└──────────────────────────────────────────────┘
    │
    ▼
┌──────────────────────────────────────────────┐
│  Step 4: Specific Exact-Match Filters        │ -> Checks foreign keys, status, type
└──────────────────────────────────────────────┘
    │
    ▼
┌──────────────────────────────────────────────┐
│  Step 5: Sorting & Pagination                │ -> orderBy() and paginate($perPage)
└──────────────────────────────────────────────┘
```

### Why We Group `WHERE` Clauses for Search
When performing a search across multiple columns (e.g. `name`, `barcode`, `sku`), beginners often write:
```php
// ❌ WRONG: Can leak inactive records or wrong category items
$query->where('category_id', 1)
      ->where('name', 'like', "%{$search}%")
      ->orWhere('sku', 'like', "%{$search}%");
```
In SQL, this evaluates as:
```sql
WHERE category_id = 1 AND name LIKE '%search%' OR sku LIKE '%search%'
```
Because `AND` has higher precedence than `OR`, this returns any product whose `sku` matches, **ignoring the category filter**!

In our controllers, we wrap search conditions inside a closure:
```php
// ✅ CORRECT: Produces proper parentheses
$query->where(function ($subQuery) use ($searchTerm) {
    $subQuery->where('name', 'like', "%{$searchTerm}%")
             ->orWhere('sku', 'like', "%{$searchTerm}%");
});
```
This produces safe SQL:
```sql
WHERE category_id = 1 AND (name LIKE '%search%' OR sku LIKE '%search%')
```

---

## 2. API Endpoints Reference

All endpoints return JSON responses. Successful collection queries return a standard Laravel `LengthAwarePaginator` object:
```json
{
  "current_page": 1,
  "data": [ ... ],
  "first_page_url": "http://localhost:8000/api/products?page=1",
  "from": 1,
  "last_page": 1,
  "per_page": 15,
  "total": 10
}
```

---

### 2.1 Products (`/api/products`)

* **Model**: `App\Models\Product`
* **Eager Loaded Relations**: `category`

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/products` | List products with search & filters |
| `POST` | `/api/products` | Create a new product |
| `GET` | `/api/products/{id}` | View single product with details |
| `PUT` | `/api/products/{id}` | Update product |
| `DELETE` | `/api/products/{id}` | Delete product |

#### Query Parameters:
* `search` *(string, optional)*: Partial match against `name`, `barcode`, or `sku`.
* `category_id` *(integer, optional)*: Exact match filter by category.
* `status` *(string, optional)*: Filter by status (e.g. `active`, `inactive`).
* `per_page` *(integer, optional, 1-100)*: Items per page (default: `15`).

#### Create Product Payload (`POST /api/products`):
```json
{
  "category_id": 1,
  "name": "Iced Latte",
  "barcode": "893452100",
  "sku": "LATTE",
  "cost_price": 1.50,
  "selling_price": 3.00,
  "stock_quantity": 50,
  "status": "active"
}
```

---

### 2.2 Categories (`/api/categories`)

* **Model**: `App\Models\Category`
* **Eager Loaded Count**: `products_count`

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/categories` | List categories with search |
| `POST` | `/api/categories` | Create a category |
| `GET` | `/api/categories/{id}` | View category with its products |
| `PUT` | `/api/categories/{id}` | Update category |
| `DELETE` | `/api/categories/{id}` | Delete category |

#### Query Parameters:
* `search` *(string, optional)*: Partial match against `name` or `description`.
* `per_page` *(integer, optional)*: Items per page (default: `15`).

---

### 2.3 Sales & Orders (`/api/sales` and `/api/orders`)

* **Model**: `App\Models\Sale`
* **Eager Loaded Relations**: `user` (cashier), `saleItems`, `payments`

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/sales` *(or `/api/orders`)* | List sales (newest first) |
| `POST` | `/api/sales` | Create a sale transaction |
| `GET` | `/api/sales/{id}` | View sale details |
| `PUT` | `/api/sales/{id}` | Update sale status |
| `DELETE` | `/api/sales/{id}` | Delete sale record |

#### Query Parameters:
* `search` *(string, optional)*: Partial match against `invoice_no` (e.g. `INV-001`).
* `cashier_id` *(integer, optional)*: Filter by cashier user ID.
* `status` *(string, optional)*: Filter by status (`completed`, `pending`, `refunded`).
* `per_page` *(integer, optional)*: Items per page (default: `15`).

---

### 2.4 Sale Items (`/api/sale-items`)

* **Model**: `App\Models\SaleItem`
* **Eager Loaded Relations**: `sale`, `product`

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/sale-items` | List sale items |
| `POST` | `/api/sale-items` | Add item to a sale |
| `GET` | `/api/sale-items/{id}` | View sale item |
| `PUT` | `/api/sale-items/{id}` | Update quantity or pricing |
| `DELETE` | `/api/sale-items/{id}` | Remove sale item |

#### Query Parameters:
* `search` *(string, optional)*: Searches related product name or sale invoice number.
* `sale_id` *(integer, optional)*: Filter items belonging to a specific sale.
* `product_id` *(integer, optional)*: Filter items by product ID.
* `per_page` *(integer, optional)*: Items per page (default: `15`).

---

### 2.5 Payments (`/api/payments`)

* **Model**: `App\Models\Payment`
* **Eager Loaded Relations**: `sale`

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/payments` | List payments |
| `POST` | `/api/payments` | Record a payment |
| `GET` | `/api/payments/{id}` | View payment |
| `PUT` | `/api/payments/{id}` | Update payment |
| `DELETE` | `/api/payments/{id}` | Delete payment |

#### Query Parameters:
* `search` *(string, optional)*: Partial match against `reference_no` or `payment_method`.
* `sale_id` *(integer, optional)*: Filter payments by sale ID.
* `payment_method` *(string, optional)*: Filter by method (`cash`, `credit_card`, `qr_code`).
* `per_page` *(integer, optional)*: Items per page (default: `15`).

---

### 2.6 Stock Movements (`/api/stock-movements`)

* **Model**: `App\Models\StockMovement`
* **Eager Loaded Relations**: `product`

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/stock-movements` | List stock audit entries |
| `POST` | `/api/stock-movements` | Create stock in/out entry |
| `GET` | `/api/stock-movements/{id}` | View stock movement |
| `PUT` | `/api/stock-movements/{id}` | Update stock movement |
| `DELETE` | `/api/stock-movements/{id}` | Delete stock movement |

#### Query Parameters:
* `search` *(string, optional)*: Partial match against `reference`, `note`, or `type`.
* `product_id` *(integer, optional)*: Filter movements for a specific product.
* `type` *(string, optional)*: Filter by movement type (`in`, `out`, `adjustment`).
* `per_page` *(integer, optional)*: Items per page (default: `15`).

---

### 2.7 Users (`/api/users`)

* **Model**: `App\Models\User`

| Method | Endpoint | Description |
| :--- | :--- | :--- |
| `GET` | `/api/users` | List staff users |
| `POST` | `/api/users` | Create staff user |
| `GET` | `/api/users/{id}` | View user details |
| `PUT` | `/api/users/{id}` | Update user profile/role |
| `DELETE` | `/api/users/{id}` | Delete user |

#### Query Parameters:
* `search` *(string, optional)*: Partial match against `name` or `email`.
* `role` *(string, optional)*: Filter by role (`admin`, `manager`, `cashier`).
* `status` *(string, optional)*: Filter by status (`active`, `inactive`).
* `per_page` *(integer, optional)*: Items per page (default: `15`).

---

## 3. Standard HTTP Status Codes

| Code | Meaning | When Returned |
| :--- | :--- | :--- |
| **`200 OK`** | Success | Successful `GET` or `PUT` request |
| **`201 Created`** | Created | Successful `POST` request (e.g. creating a product) |
| **`204 No Content`** | Deleted | Successful `DELETE` request |
| **`401 Unauthorized`** | Authentication required | Missing or invalid Bearer token |
| **`403 Forbidden`** | Permission denied | Inactive user account or insufficient permissions |
| **`404 Not Found`** | Resource missing | `findOrFail($id)` could not find the record |
| **`422 Unprocessable Entity`** | Validation error | Input failed `$request->validate()` rules |

