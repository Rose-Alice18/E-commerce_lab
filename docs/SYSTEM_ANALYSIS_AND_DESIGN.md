# PharmaVault System Analysis & Design Document

**Project:** PharmaVault - B2B2C Pharmaceutical E-Commerce Platform
**Date:** November 26, 2025
**Version:** 1.0
**Author:** CS-MIS Department

---

## Table of Contents

1. [System Overview](#1-system-overview)
2. [Requirements Analysis](#2-requirements-analysis)
3. [System Architecture](#3-system-architecture)
4. [Technology Stack](#4-technology-stack)
5. [Database Design](#5-database-design)
6. [Design Patterns & Decisions](#6-design-patterns--decisions)
7. [Security Architecture](#7-security-architecture)
8. [API & Integration Design](#8-api--integration-design)
9. [User Interface Design](#9-user-interface-design)
10. [Sequence Diagrams](#10-sequence-diagrams)

---

## 1. System Overview

### 1.1 Purpose
PharmaVault is a B2B2C e-commerce platform designed to connect licensed pharmacies with customers in Ghana, addressing medication access challenges through a digital marketplace with prescription management and mobile money integration.

### 1.2 Scope
- **B2B Component:** Onboard licensed pharmacies as vendors
- **B2C Component:** Enable customers to browse, order, and receive medications
- **Prescription Management:** Digital prescription verification system
- **Payment Integration:** Mobile Money (MTN, Vodafone) and Paystack/Flutterwave
- **Multi-role Support:** Super Admin, Pharmacy Admin, Customer, Guest

### 1.3 System Context
```
┌─────────────────────────────────────────────────────────────┐
│                     PharmaVault System                       │
├─────────────────────────────────────────────────────────────┤
│                                                               │
│  ┌─────────────┐  ┌──────────────┐  ┌─────────────────┐   │
│  │   Customers │  │  Pharmacies  │  │  Super Admin    │   │
│  │  (Buyers)   │  │   (Vendors)  │  │  (Platform)     │   │
│  └──────┬──────┘  └──────┬───────┘  └────────┬────────┘   │
│         │                 │                    │             │
│  ┌──────▼─────────────────▼────────────────────▼────────┐  │
│  │         PharmaVault Web Application                   │  │
│  │  (MVC Architecture - PHP/MySQL/Bootstrap)            │  │
│  └──────┬─────────────────┬────────────────────┬────────┘  │
│         │                 │                    │             │
│  ┌──────▼──────┐  ┌──────▼──────┐  ┌─────────▼────────┐  │
│  │  Database   │  │  Payment    │  │  File Storage    │  │
│  │  (MySQL)    │  │  Gateway    │  │  (Images/Docs)   │  │
│  └─────────────┘  └─────────────┘  └──────────────────┘  │
└─────────────────────────────────────────────────────────────┘

External Systems:
- Paystack/Flutterwave API (Payment Processing)
- Mobile Money APIs (MTN, Vodafone)
- FDA Ghana (Pharmacy License Verification - Future)
```

---

## 2. Requirements Analysis

### 2.1 Functional Requirements

#### FR-1: User Management
- **FR-1.1** System shall support registration with email verification
- **FR-1.2** System shall authenticate users with bcrypt-hashed passwords
- **FR-1.3** System shall support three user roles: Super Admin, Pharmacy Admin, Customer
- **FR-1.4** System shall allow profile management (update info, change password)
- **FR-1.5** System shall track user activity timestamps (created_at, updated_at)

#### FR-2: Product Management
- **FR-2.1** Pharmacy admins shall add products with title, price, description, stock, category, brand
- **FR-2.2** System shall support multiple product images per product
- **FR-2.3** System shall support bulk product upload via CSV
- **FR-2.4** System shall mark products as prescription-required
- **FR-2.5** System shall track product inventory and prevent overselling
- **FR-2.6** System shall categorize products with categories and brands

#### FR-3: Shopping Cart
- **FR-3.1** System shall support guest cart (IP-based) and authenticated cart (customer_id-based)
- **FR-3.2** System shall merge guest cart with user cart upon login
- **FR-3.3** System shall validate cart quantities against available stock
- **FR-3.4** System shall update cart totals in real-time
- **FR-3.5** System shall persist cart data across sessions

#### FR-4: Order Processing
- **FR-4.1** System shall create orders with unique order IDs
- **FR-4.2** System shall store order details (items, quantities, prices at purchase time)
- **FR-4.3** System shall track order status (pending, processing, shipped, delivered, cancelled)
- **FR-4.4** System shall link orders to customers and payment records
- **FR-4.5** System shall reduce product stock upon successful order placement

#### FR-5: Prescription Management
- **FR-5.1** System shall allow customers to upload prescription images
- **FR-5.2** System shall generate unique prescription numbers
- **FR-5.3** System shall support multiple images per prescription
- **FR-5.4** System shall track prescription status (pending, verified, rejected, expired)
- **FR-5.5** System shall link prescriptions to orders
- **FR-5.6** System shall require prescription verification before fulfilling prescription-required products

#### FR-6: Payment Integration
- **FR-6.1** System shall integrate with Paystack for card payments
- **FR-6.2** System shall support Mobile Money (MTN, Vodafone)
- **FR-6.3** System shall verify payment transactions before order confirmation
- **FR-6.4** System shall store payment records with transaction IDs
- **FR-6.5** System shall handle payment callbacks and webhooks

#### FR-7: Wishlist
- **FR-7.1** System shall allow customers to add products to wishlist
- **FR-7.2** System shall prevent duplicate wishlist entries
- **FR-7.3** System shall allow customers to move wishlist items to cart

#### FR-8: Suggestions System
- **FR-8.1** System shall allow users to suggest new categories
- **FR-8.2** System shall allow users to suggest new brands
- **FR-8.3** System shall allow super admin to review and approve/reject suggestions
- **FR-8.4** System shall track suggestion status (pending, approved, rejected)

#### FR-9: Admin Dashboard
- **FR-9.1** Super Admin shall view platform-wide analytics (sales, revenue, users)
- **FR-9.2** Pharmacy Admin shall view pharmacy-specific analytics
- **FR-9.3** System shall display real-time dashboard statistics
- **FR-9.4** System shall generate revenue reports by date range
- **FR-9.5** System shall track order trends and product performance

#### FR-10: Search & Filtering
- **FR-10.1** System shall allow product search by name
- **FR-10.2** System shall filter products by category
- **FR-10.3** System shall filter products by brand
- **FR-10.4** System shall filter products by price range
- **FR-10.5** System shall sort products by price, name, date added

### 2.2 Non-Functional Requirements

#### NFR-1: Performance
- **NFR-1.1** Page load time shall not exceed 3 seconds on average internet connection
- **NFR-1.2** Database queries shall be optimized with proper indexing
- **NFR-1.3** System shall support at least 100 concurrent users

#### NFR-2: Security
- **NFR-2.1** All passwords shall be hashed using bcrypt with salt
- **NFR-2.2** All database queries shall use prepared statements to prevent SQL injection
- **NFR-2.3** Session management shall use secure cookies with HTTP-only flag
- **NFR-2.4** File uploads shall be validated for type and size
- **NFR-2.5** Payment data shall be transmitted over HTTPS only

#### NFR-3: Usability
- **NFR-3.1** System shall be responsive and mobile-friendly (Bootstrap 5.3)
- **NFR-3.2** UI shall follow consistent design patterns
- **NFR-3.3** Error messages shall be user-friendly and actionable
- **NFR-3.4** Navigation shall be intuitive with clear labels

#### NFR-4: Reliability
- **NFR-4.1** System shall have 99.5% uptime
- **NFR-4.2** Database shall have automated daily backups
- **NFR-4.3** System shall handle errors gracefully without crashing

#### NFR-5: Maintainability
- **NFR-5.1** Code shall follow MVC architecture for separation of concerns
- **NFR-5.2** Code shall be documented with inline comments
- **NFR-5.3** Database schema shall use foreign keys for referential integrity
- **NFR-5.4** System shall use version control (Git)

#### NFR-6: Scalability
- **NFR-6.1** Database design shall support future growth
- **NFR-6.2** System architecture shall support horizontal scaling
- **NFR-6.3** File storage shall be modular for cloud migration

---

## 3. System Architecture

### 3.1 MVC Architecture Overview

```
┌────────────────────────────────────────────────────────────────┐
│                      PRESENTATION LAYER                         │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                     VIEW (UI)                             │  │
│  │  - view/                 (Customer views)                │  │
│  │  - admin/                (Admin dashboards)              │  │
│  │  - login/                (Authentication)                │  │
│  │  - Bootstrap 5.3 + CSS3  (Responsive design)            │  │
│  │  - JavaScript (ES6+)     (Client-side interactions)     │  │
│  └────────────────────┬─────────────────────────────────────┘  │
│                       │ HTTP Requests                            │
└───────────────────────┼──────────────────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────────────────┐
│                    APPLICATION LAYER                             │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                   CONTROLLERS                             │  │
│  │  controllers/                                             │  │
│  │  - user_controller.php       (User operations)           │  │
│  │  - product_controller.php    (Product CRUD)              │  │
│  │  - cart_controller.php       (Cart management)           │  │
│  │  - order_controller.php      (Order processing)          │  │
│  │  - category_controller.php   (Category management)       │  │
│  │  - brand_controller.php      (Brand management)          │  │
│  │  - suggestion_controller.php (Suggestions)               │  │
│  │  - prescription_controller.php (Prescriptions)           │  │
│  │  - wishlist_controller.php   (Wishlist)                  │  │
│  └────────────────────┬─────────────────────────────────────┘  │
│                       │ Business Logic Calls                     │
│  ┌────────────────────▼─────────────────────────────────────┐  │
│  │                      ACTIONS                              │  │
│  │  actions/                                                 │  │
│  │  - Process form submissions                               │  │
│  │  - Validate inputs                                        │  │
│  │  - Call controller methods                                │  │
│  │  - Return JSON responses                                  │  │
│  └────────────────────┬─────────────────────────────────────┘  │
└───────────────────────┼──────────────────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────────────────┐
│                      BUSINESS LAYER                              │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                     MODELS (Classes)                      │  │
│  │  classes/                                                 │  │
│  │  - user_class.php         (User business logic)          │  │
│  │  - product_class.php      (Product operations)           │  │
│  │  - cart_class.php         (Cart calculations)            │  │
│  │  - order_class.php        (Order processing)             │  │
│  │  - customer_class.php     (Customer data)                │  │
│  │  - category_class.php     (Category management)          │  │
│  │  - brand_class.php        (Brand management)             │  │
│  │  - suggestion_class.php   (Suggestions logic)            │  │
│  │  - prescription_class.php (Prescription handling)        │  │
│  │  - wishlist_class.php     (Wishlist operations)          │  │
│  └────────────────────┬─────────────────────────────────────┘  │
└───────────────────────┼──────────────────────────────────────────┘
                        │
┌───────────────────────▼──────────────────────────────────────────┐
│                       DATA LAYER                                 │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                   DATABASE ACCESS                         │  │
│  │  settings/                                                │  │
│  │  - db_class.php       (Database connection wrapper)      │  │
│  │  - db_cred.php        (Database credentials)             │  │
│  │  - core.php           (Session & auth helpers)           │  │
│  └────────────────────┬─────────────────────────────────────┘  │
│                       │ SQL Queries (Prepared Statements)        │
│  ┌────────────────────▼─────────────────────────────────────┐  │
│  │                   MySQL Database                          │  │
│  │  pharmavault_db                                           │  │
│  │  - 16 Tables (Normalized, Foreign Keys)                  │  │
│  └──────────────────────────────────────────────────────────┘  │
└──────────────────────────────────────────────────────────────────┘

External Integrations:
┌────────────────────┐  ┌────────────────────┐  ┌──────────────┐
│  Paystack API      │  │  Mobile Money API  │  │ File System  │
│  (Payment)         │  │  (MTN/Vodafone)    │  │ (Uploads)    │
└────────────────────┘  └────────────────────┘  └──────────────┘
```

### 3.2 Directory Structure

```
register_sample/
│
├── actions/                    # Form handlers & AJAX endpoints
│   ├── add_to_cart_action.php
│   ├── register_user_action.php
│   ├── process_checkout_action.php
│   └── [35+ action files]
│
├── admin/                      # Admin & user dashboards
│   ├── components/             # Reusable UI components
│   │   ├── sidebar_super_admin.php
│   │   ├── sidebar_pharmacy_admin.php
│   │   └── sidebar_customer.php
│   ├── analytics.php
│   ├── orders.php
│   ├── my_products.php
│   └── [20+ admin pages]
│
├── classes/                    # Business logic (Models)
│   ├── user_class.php
│   ├── product_class.php
│   ├── cart_class.php
│   ├── order_class.php
│   ├── prescription_class.php
│   └── [10+ class files]
│
├── controllers/                # Application logic controllers
│   ├── user_controller.php
│   ├── product_controller.php
│   ├── cart_controller.php
│   └── [8+ controller files]
│
├── css/                        # Stylesheets
│   └── custom styles
│
├── db/                         # Database scripts
│   ├── pharmavault_db.sql      # Main database dump
│   ├── add_missing_tables.sql  # Additional tables
│   └── cart_improvements.sql   # Optional enhancements
│
├── images/                     # Static assets
│   ├── products/               # Product images
│   └── uploads/                # User uploads
│
├── js/                         # Client-side JavaScript
│   ├── cart.js
│   ├── checkout.js
│   └── [custom scripts]
│
├── login/                      # Authentication pages
│   ├── login.php
│   ├── register.php
│   └── logout.php
│
├── settings/                   # Configuration files
│   ├── db_class.php            # Database connection class
│   ├── db_cred.php             # Database credentials
│   └── core.php                # Core utilities & auth
│
├── view/                       # Customer-facing pages
│   ├── all_product.php
│   ├── single_product.php
│   ├── checkout.php
│   └── upload_prescription.php
│
└── index.php                   # Homepage entry point
```

### 3.3 Data Flow Example: Adding Product to Cart

```
┌─────────────┐
│   User      │
│  (Browser)  │
└──────┬──────┘
       │ 1. Click "Add to Cart"
       │
       ▼
┌──────────────────────────────────────┐
│  JavaScript (cart.js)                │
│  - Capture click event               │
│  - Get product_id, quantity          │
│  - Send AJAX POST request            │
└──────┬───────────────────────────────┘
       │ 2. POST /actions/add_to_cart_action.php
       │    {product_id: 123, qty: 2}
       ▼
┌──────────────────────────────────────┐
│  Action Handler                      │
│  (add_to_cart_action.php)            │
│  - Validate input                    │
│  - Get customer_id or IP address     │
│  - Call controller                   │
└──────┬───────────────────────────────┘
       │ 3. Call add_to_cart()
       ▼
┌──────────────────────────────────────┐
│  Controller                          │
│  (cart_controller.php)               │
│  - Instantiate cart_class            │
│  - Call model method                 │
└──────┬───────────────────────────────┘
       │ 4. Call add_to_cart_db()
       ▼
┌──────────────────────────────────────┐
│  Model (cart_class.php)              │
│  - Check product stock               │
│  - Check if item already in cart     │
│  - INSERT or UPDATE cart table       │
│  - Use prepared statements           │
└──────┬───────────────────────────────┘
       │ 5. Execute SQL via db_class
       ▼
┌──────────────────────────────────────┐
│  Database (MySQL)                    │
│  - Insert/Update cart record         │
│  - Return affected rows              │
└──────┬───────────────────────────────┘
       │ 6. Return result
       ▼
┌──────────────────────────────────────┐
│  Response (JSON)                     │
│  {"success": true,                   │
│   "message": "Added to cart",        │
│   "cart_count": 5}                   │
└──────┬───────────────────────────────┘
       │ 7. Update UI
       ▼
┌──────────────────────────────────────┐
│  JavaScript Updates:                 │
│  - Show success toast                │
│  - Update cart badge count           │
│  - Enable checkout button            │
└──────────────────────────────────────┘
```

---

## 4. Technology Stack

### 4.1 Backend Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| **PHP** | 8.1+ | Server-side programming language |
| **MySQL** | 8.0+ | Relational database management system |
| **MySQLi** | Built-in | Database driver with prepared statements |
| **bcrypt** | PHP built-in | Password hashing algorithm |
| **Sessions** | PHP native | User authentication & state management |

### 4.2 Frontend Technologies

| Technology | Version | Purpose |
|------------|---------|---------|
| **HTML5** | - | Semantic markup |
| **CSS3** | - | Styling & animations |
| **Bootstrap** | 5.3 | Responsive UI framework |
| **JavaScript** | ES6+ | Client-side interactivity |
| **AJAX** | Vanilla JS | Asynchronous API calls |
| **Font Awesome** | 6.x | Icons |

### 4.3 Third-Party Services

| Service | Purpose |
|---------|---------|
| **Paystack** | Payment gateway (cards, mobile money) |
| **Flutterwave** | Alternative payment gateway |
| **MTN Mobile Money** | Direct mobile payment integration |
| **Vodafone Cash** | Direct mobile payment integration |

### 4.4 Development Tools

| Tool | Purpose |
|------|---------|
| **XAMPP** | Local development environment (Apache + MySQL + PHP) |
| **Git** | Version control |
| **VS Code** | Code editor |
| **phpMyAdmin** | Database management GUI |
| **Chrome DevTools** | Frontend debugging |

### 4.5 Server Requirements

- **Web Server:** Apache 2.4+ with mod_rewrite enabled
- **PHP:** 8.1+ with extensions: mysqli, session, gd, fileinfo, json
- **MySQL:** 8.0+ with InnoDB engine
- **SSL Certificate:** For HTTPS (required for payment processing)
- **PHP Memory Limit:** 256MB minimum
- **Upload Max Filesize:** 10MB minimum (for prescription images)

---

## 5. Database Design

### 5.1 Entity-Relationship Diagram (ERD)

```
┌──────────────────┐
│    CUSTOMER      │
├──────────────────┤
│ PK customer_id   │
│    email         │
│    password      │──┐
│    first_name    │  │
│    last_name     │  │
│    phone         │  │
│    user_role     │  │ 1
│    created_at    │  │
└──────────────────┘  │
                      │
                      │ has
                      │
        ┌─────────────┴──────────────┬──────────────┬───────────────┐
        │                            │              │               │
        │ N                          │ N            │ N             │ N
┌───────▼──────────┐     ┌───────────▼────────┐  ┌─▼─────────┐  ┌─▼──────────────┐
│     ORDERS       │     │      CART          │  │ WISHLIST  │  │ PRESCRIPTIONS  │
├──────────────────┤     ├────────────────────┤  ├───────────┤  ├────────────────┤
│ PK order_id      │     │ PK cart_id         │  │ PK wish.. │  │ PK prescript.. │
│ FK customer_id   │     │ FK p_id (product)  │  │ FK cust.. │  │ FK customer_id │
│    order_number  │     │ FK c_id (customer) │  │ FK prod.. │  │    presc_num   │
│    order_date    │     │    ip_add          │  │    added  │  │    doctor_name │
│    order_status  │     │    qty             │  └───────────┘  │    status      │
│    total_amount  │     │    created_at      │                 │    image       │
│    payment_status│─┐   └────────────────────┘                 │    verified_at │
└──────────────────┘ │                                           └────────────────┘
        │            │
        │ has        │ processed by
        │            │
        │ N          │ 1
┌───────▼──────────┐ │   ┌────────────────────┐
│  ORDERDETAILS    │ │   │     PAYMENT        │
├──────────────────┤ │   ├────────────────────┤
│ PK order_det_id  │ │   │ PK payment_id      │
│ FK order_id      │ └──▶│ FK order_id        │
│ FK product_id    │     │    transaction_id  │
│    qty           │     │    amount          │
│    price         │     │    payment_method  │
│    subtotal      │     │    payment_status  │
└──────────────────┘     │    paid_at         │
        │                └────────────────────┘
        │ contains
        │
        │ N
        │
        │ 1
┌───────▼──────────┐
│     PRODUCTS     │
├──────────────────┤
│ PK product_id    │
│ FK product_cat   │──┐
│ FK product_brand │─┐│
│ FK pharmacy_id   │ ││
│    title         │ ││
│    price         │ ││
│    description   │ ││
│    stock         │ ││
│    image         │ ││
│    prescription  │ ││  1
│      _required   │ ││
└──────────────────┘ ││
                     ││
        ┌────────────┘│
        │             │
        │ N           │ N
        │             │
        │ 1           │ 1
┌───────▼──────────┐ │  ┌──────────────────┐
│   CATEGORIES     │ │  │     BRANDS       │
├──────────────────┤ │  ├──────────────────┤
│ PK cat_id        │ │  │ PK brand_id      │
│    cat_name      │ │  │    brand_name    │
│    description   │ │  │    description   │
└──────────────────┘ │  └──────────────────┘
                     │
                     │  ┌──────────────────┐
                     │  │  SUGGESTIONS     │
                     │  ├──────────────────┤
                     └─▶│ FK related_cat   │
                        │    type          │
                        │    name          │
                        │    status        │
                        └──────────────────┘

Additional Supporting Tables:
- product_images (multiple images per product)
- prescription_items (medications in prescription)
- order_prescriptions (link orders to prescriptions)
- prescription_verification_log (audit trail)
- prescription_images (multiple images per prescription)
```

### 5.2 Table Definitions

#### Core Tables

**1. customer**
```sql
customer_id       INT PRIMARY KEY AUTO_INCREMENT
customer_email    VARCHAR(100) UNIQUE NOT NULL
customer_pass     VARCHAR(255) NOT NULL  -- bcrypt hashed
customer_name     VARCHAR(100)
customer_contact  VARCHAR(20)
customer_country  VARCHAR(50)
user_role         TINYINT DEFAULT 1      -- 1=Customer, 2=Pharmacy, 3=Super Admin
created_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**2. products**
```sql
product_id          INT PRIMARY KEY AUTO_INCREMENT
product_cat         INT FOREIGN KEY → categories(cat_id)
product_brand       INT FOREIGN KEY → brands(brand_id)
product_title       VARCHAR(200) NOT NULL
product_price       DECIMAL(10,2) NOT NULL
product_desc        TEXT
product_image       VARCHAR(255)
product_keywords    VARCHAR(255)
product_stock       INT DEFAULT 0
prescription_req    TINYINT(1) DEFAULT 0
pharmacy_id         INT FOREIGN KEY → customer(customer_id)
created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**3. categories**
```sql
cat_id          INT PRIMARY KEY AUTO_INCREMENT
cat_name        VARCHAR(100) UNIQUE NOT NULL
cat_desc        TEXT
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**4. brands**
```sql
brand_id        INT PRIMARY KEY AUTO_INCREMENT
brand_name      VARCHAR(100) UNIQUE NOT NULL
brand_desc      TEXT
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**5. cart**
```sql
cart_id         INT PRIMARY KEY AUTO_INCREMENT
p_id            INT FOREIGN KEY → products(product_id) ON DELETE CASCADE
c_id            INT FOREIGN KEY → customer(customer_id) ON DELETE CASCADE
ip_add          VARCHAR(50)
qty             INT NOT NULL
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at      TIMESTAMP ON UPDATE CURRENT_TIMESTAMP

UNIQUE KEY (p_id, c_id, ip_add)
INDEX (c_id), INDEX (ip_add)
```

**6. orders**
```sql
order_id        INT PRIMARY KEY AUTO_INCREMENT
customer_id     INT FOREIGN KEY → customer(customer_id)
order_number    VARCHAR(50) UNIQUE NOT NULL
invoice_no      INT UNIQUE AUTO_INCREMENT
order_date      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
order_status    ENUM('pending','processing','shipped','delivered','cancelled')
                DEFAULT 'pending'
delivery_add    TEXT
payment_status  ENUM('pending','completed','failed') DEFAULT 'pending'
total_amount    DECIMAL(10,2)
```

**7. orderdetails**
```sql
order_detail_id INT PRIMARY KEY AUTO_INCREMENT
order_id        INT FOREIGN KEY → orders(order_id) ON DELETE CASCADE
product_id      INT FOREIGN KEY → products(product_id)
qty             INT NOT NULL
price           DECIMAL(10,2) NOT NULL  -- Price at time of purchase
subtotal        DECIMAL(10,2) GENERATED AS (qty * price)
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**8. payment**
```sql
payment_id      INT PRIMARY KEY AUTO_INCREMENT
order_id        INT FOREIGN KEY → orders(order_id)
transaction_id  VARCHAR(100) UNIQUE
amount          DECIMAL(10,2) NOT NULL
payment_method  ENUM('paystack','mobile_money','flutterwave')
payment_status  ENUM('pending','success','failed') DEFAULT 'pending'
payment_date    TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

#### Feature Tables

**9. wishlist**
```sql
wishlist_id     INT PRIMARY KEY AUTO_INCREMENT
customer_id     INT FOREIGN KEY → customer(customer_id) ON DELETE CASCADE
product_id      INT FOREIGN KEY → products(product_id) ON DELETE CASCADE
added_at        TIMESTAMP DEFAULT CURRENT_TIMESTAMP

UNIQUE KEY (customer_id, product_id)
```

**10. prescriptions**
```sql
prescription_id     INT PRIMARY KEY AUTO_INCREMENT
customer_id         INT FOREIGN KEY → customer(customer_id) ON DELETE CASCADE
prescription_number VARCHAR(50) UNIQUE NOT NULL
doctor_name         VARCHAR(200)
doctor_license      VARCHAR(100)
issue_date          DATE
expiry_date         DATE
prescription_image  VARCHAR(255)
prescription_notes  TEXT
status              ENUM('pending','verified','rejected','expired') DEFAULT 'pending'
verified_by         INT FOREIGN KEY → customer(customer_id)
verified_at         TIMESTAMP
rejection_reason    TEXT
uploaded_at         TIMESTAMP DEFAULT CURRENT_TIMESTAMP
updated_at          TIMESTAMP ON UPDATE CURRENT_TIMESTAMP
```

**11. prescription_items**
```sql
prescription_item_id INT PRIMARY KEY AUTO_INCREMENT
prescription_id      INT FOREIGN KEY → prescriptions(prescription_id) ON DELETE CASCADE
medication_name      VARCHAR(200) NOT NULL
dosage               VARCHAR(100)
quantity             INT NOT NULL
frequency            VARCHAR(100)
duration             VARCHAR(100)
product_id           INT FOREIGN KEY → products(product_id)
fulfilled            TINYINT(1) DEFAULT 0
created_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**12. order_prescriptions**
```sql
order_prescription_id INT PRIMARY KEY AUTO_INCREMENT
order_id              INT FOREIGN KEY → orders(order_id) ON DELETE CASCADE
prescription_id       INT FOREIGN KEY → prescriptions(prescription_id) ON DELETE CASCADE
attached_at           TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**13. prescription_images**
```sql
image_id        INT PRIMARY KEY AUTO_INCREMENT
prescription_id INT FOREIGN KEY → prescriptions(prescription_id) ON DELETE CASCADE
image_path      VARCHAR(255) NOT NULL
image_type      ENUM('front','back','detail','other') DEFAULT 'other'
is_primary      TINYINT(1) DEFAULT 0
uploaded_at     TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**14. suggestions**
```sql
suggestion_id       INT PRIMARY KEY AUTO_INCREMENT
user_id             INT FOREIGN KEY → customer(customer_id) ON DELETE CASCADE
suggestion_type     ENUM('category','brand') NOT NULL
suggestion_name     VARCHAR(100) NOT NULL
suggestion_desc     TEXT
related_category_id INT FOREIGN KEY → categories(cat_id)
status              ENUM('pending','approved','rejected') DEFAULT 'pending'
admin_notes         TEXT
created_at          TIMESTAMP DEFAULT CURRENT_TIMESTAMP
reviewed_at         TIMESTAMP
reviewed_by         INT FOREIGN KEY → customer(customer_id)
```

**15. product_images**
```sql
image_id    INT PRIMARY KEY AUTO_INCREMENT
product_id  INT FOREIGN KEY → products(product_id) ON DELETE CASCADE
image_path  VARCHAR(255) NOT NULL
is_primary  TINYINT(1) DEFAULT 0
uploaded_at TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

**16. prescription_verification_log**
```sql
log_id          INT PRIMARY KEY AUTO_INCREMENT
prescription_id INT FOREIGN KEY → prescriptions(prescription_id) ON DELETE CASCADE
action          ENUM('uploaded','verified','rejected','expired','resubmitted')
performed_by    INT FOREIGN KEY → customer(customer_id)
notes           TEXT
created_at      TIMESTAMP DEFAULT CURRENT_TIMESTAMP
```

### 5.3 Database Normalization

The database follows **Third Normal Form (3NF)**:

- **1NF:** All tables have atomic values, no repeating groups
- **2NF:** All non-key attributes fully depend on primary key
- **3NF:** No transitive dependencies (non-key attributes don't depend on other non-key attributes)

**Example:**
- `orderdetails` stores `price` at time of purchase (not dependent on current `products.product_price`)
- `categories` and `brands` are separate tables, not embedded in products
- Customer information is in `customer` table, not duplicated in orders

### 5.4 Database Indexes

```sql
-- Performance indexes
CREATE INDEX idx_product_category ON products(product_cat);
CREATE INDEX idx_product_brand ON products(product_brand);
CREATE INDEX idx_product_pharmacy ON products(pharmacy_id);
CREATE INDEX idx_order_customer ON orders(customer_id);
CREATE INDEX idx_order_status ON orders(order_status);
CREATE INDEX idx_cart_customer ON cart(c_id);
CREATE INDEX idx_cart_ip ON cart(ip_add);
CREATE INDEX idx_prescription_status ON prescriptions(status);
CREATE INDEX idx_prescription_customer ON prescriptions(customer_id);
```

---

## 6. Design Patterns & Decisions

### 6.1 MVC Pattern Implementation

**Model (classes/):**
- Encapsulates database operations
- Business logic and data validation
- Extends `db_connection` class
- Uses prepared statements for security

**View (view/, admin/, login/):**
- Presentation layer with HTML/Bootstrap
- No direct database access
- Includes JavaScript for client-side interactions
- Reusable components (sidebars, headers)

**Controller (controllers/):**
- Mediates between Model and View
- Processes user input
- Returns data to Views or Actions
- No direct database queries

**Actions (actions/):**
- Handles form submissions
- AJAX endpoint handlers
- Validates input
- Calls controllers
- Returns JSON responses

### 6.2 Key Design Decisions

#### 1. Session-Based Authentication
**Decision:** Use PHP sessions with server-side storage
**Rationale:**
- Simpler than JWT for traditional web apps
- Secure when using HTTP-only cookies
- Built-in PHP session management
- Suitable for current scale

**Alternative Considered:** JWT tokens (rejected due to complexity for current needs)

#### 2. Prepared Statements for Database Security
**Decision:** Use MySQLi prepared statements exclusively
**Rationale:**
- Prevents SQL injection attacks
- Enforced in all model classes
- Performance benefits from query compilation
- Industry best practice

**Example:**
```php
$stmt = $this->db_conn()->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
```

#### 3. B2B2C Architecture
**Decision:** Single platform for both pharmacies and customers
**Rationale:**
- Centralized inventory management
- Consistent user experience
- Simplified payment processing
- Shared product catalog

**Alternative Considered:** Separate pharmacy and customer platforms (rejected due to increased complexity)

#### 4. Guest Cart with IP Tracking
**Decision:** Allow guest users to add items to cart before login
**Rationale:**
- Reduces friction in purchase flow
- Increases conversion rates
- Merges guest cart with user cart upon login
- Common e-commerce pattern

**Implementation:**
```php
// Get cart items for guest or logged-in user
$customer_id = isset($_SESSION['customer_id']) ? $_SESSION['customer_id'] : 0;
$ip_address = $_SERVER['REMOTE_ADDR'];
```

#### 5. Prescription Required Flag
**Decision:** Add `prescription_required` boolean to products table
**Rationale:**
- Regulatory compliance
- Flexible (not all products require prescriptions)
- Enforced during checkout
- Links prescriptions to orders

#### 6. Price Storage in Order Details
**Decision:** Store product price at time of purchase in `orderdetails`
**Rationale:**
- Historical accuracy (prices may change)
- Financial integrity
- Audit trail
- Legal compliance

#### 7. Mobile Money Integration
**Decision:** Support MTN and Vodafone mobile money via Paystack
**Rationale:**
- Primary payment method in Ghana (>80% mobile money usage)
- Paystack provides unified API
- No direct telco integration needed
- Reliable callback handling

#### 8. Bulk Upload with CSV
**Decision:** Allow pharmacy admins to upload products via CSV
**Rationale:**
- Faster than manual entry
- Reduces data entry errors
- Standard format (Excel compatible)
- Scalable for large inventories

#### 9. Image Storage on File System
**Decision:** Store images as files, store paths in database
**Rationale:**
- Better performance than BLOB storage
- Easier CDN integration in future
- Direct web server serving
- Simpler backup/restore

**Alternative Considered:** Store images as BLOBs in MySQL (rejected due to performance)

#### 10. Role-Based Access Control (RBAC)
**Decision:** Use `user_role` column with integer values
**Rationale:**
- Simple to implement
- Sufficient for current needs (3 roles)
- Easy to extend in future
- Enforced in `core.php`

**Role Mapping:**
- 1 = Customer
- 2 = Pharmacy Admin
- 3 = Super Admin

---

## 7. Security Architecture

### 7.1 Authentication & Authorization

#### Password Security
```php
// Registration: Hash password with bcrypt
$hashed_password = password_hash($password, PASSWORD_BCRYPT, ['cost' => 12]);

// Login: Verify password
if (password_verify($input_password, $stored_hash)) {
    // Login successful
}
```

**Security Measures:**
- bcrypt with cost factor 12 (industry standard)
- Salt automatically generated
- Rainbow table attacks prevented
- Passwords never stored in plaintext

#### Session Management
```php
// settings/core.php
session_start();
ini_set('session.cookie_httponly', 1);  // Prevent XSS
ini_set('session.cookie_secure', 1);    // HTTPS only
ini_set('session.use_strict_mode', 1);   // Prevent session fixation

// Check if user is logged in
function check_login() {
    return isset($_SESSION['customer_id']);
}

// Check user role
function check_permission($required_role) {
    return isset($_SESSION['user_role']) && $_SESSION['user_role'] >= $required_role;
}
```

#### Access Control Matrix

| Resource | Guest | Customer | Pharmacy Admin | Super Admin |
|----------|-------|----------|----------------|-------------|
| View Products | ✅ | ✅ | ✅ | ✅ |
| Add to Cart | ✅ | ✅ | ✅ | ✅ |
| Checkout | ❌ | ✅ | ✅ | ✅ |
| Manage Orders | ❌ | ✅ (own) | ✅ (own) | ✅ (all) |
| Add Products | ❌ | ❌ | ✅ (own) | ✅ (all) |
| Manage Categories | ❌ | ❌ | ❌ | ✅ |
| View Analytics | ❌ | ❌ | ✅ (own) | ✅ (all) |
| Review Prescriptions | ❌ | ❌ | ✅ | ✅ |

### 7.2 SQL Injection Prevention

**All database queries use prepared statements:**

```php
// ❌ VULNERABLE (not used in codebase)
$query = "SELECT * FROM products WHERE product_id = " . $_GET['id'];

// ✅ SECURE (used throughout codebase)
$stmt = $this->db_conn()->prepare("SELECT * FROM products WHERE product_id = ?");
$stmt->bind_param("i", $product_id);
$stmt->execute();
$result = $stmt->get_result();
```

### 7.3 Cross-Site Scripting (XSS) Prevention

**Output Escaping:**
```php
// Escape all user-generated content before display
echo htmlspecialchars($product_title, ENT_QUOTES, 'UTF-8');
```

**Content Security Policy (CSP) Headers:**
```php
header("Content-Security-Policy: default-src 'self'; script-src 'self' 'unsafe-inline' https://cdn.jsdelivr.net;");
```

### 7.4 File Upload Security

**Prescription Image Upload:**
```php
// Validate file type
$allowed_types = ['image/jpeg', 'image/png', 'image/jpg'];
if (!in_array($_FILES['prescription']['type'], $allowed_types)) {
    die("Invalid file type");
}

// Validate file size (max 5MB)
if ($_FILES['prescription']['size'] > 5 * 1024 * 1024) {
    die("File too large");
}

// Rename file to prevent directory traversal
$extension = pathinfo($_FILES['prescription']['name'], PATHINFO_EXTENSION);
$new_filename = uniqid() . '.' . $extension;
$upload_path = 'uploads/prescriptions/' . $new_filename;

// Move file outside web root or with .htaccess protection
move_uploaded_file($_FILES['prescription']['tmp_name'], $upload_path);
```

**.htaccess for uploads directory:**
```apache
# Prevent direct PHP execution in uploads folder
<FilesMatch "\.(php|phtml|php3|php4|php5|php7|phps)$">
    Deny from all
</FilesMatch>
```

### 7.5 Payment Security

**Paystack Integration:**
```php
// 1. Initialize transaction on server-side only
$curl = curl_init();
curl_setopt_array($curl, [
    CURLOPT_URL => "https://api.paystack.co/transaction/initialize",
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_HTTPHEADER => [
        "Authorization: Bearer " . PAYSTACK_SECRET_KEY,  // From config file
        "Content-Type: application/json"
    ],
    CURLOPT_POSTFIELDS => json_encode([
        'email' => $customer_email,
        'amount' => $amount * 100,  // Convert to kobo
        'metadata' => ['order_id' => $order_id]
    ])
]);

// 2. Verify transaction callback
$response = file_get_contents("https://api.paystack.co/transaction/verify/" . $reference);
$result = json_decode($response);

if ($result->data->status === 'success') {
    // Update order and payment records
}
```

**Security Measures:**
- API keys stored in config files (not version controlled)
- Webhook signature verification
- Amount verification before order confirmation
- HTTPS required for payment pages
- No sensitive data in client-side JavaScript

### 7.6 CSRF Protection

**Token Generation:**
```php
// Generate CSRF token for forms
if (!isset($_SESSION['csrf_token'])) {
    $_SESSION['csrf_token'] = bin2hex(random_bytes(32));
}

// Include in forms
<input type="hidden" name="csrf_token" value="<?php echo $_SESSION['csrf_token']; ?>">
```

**Token Validation:**
```php
// Validate CSRF token in actions
if (!isset($_POST['csrf_token']) || $_POST['csrf_token'] !== $_SESSION['csrf_token']) {
    die("Invalid CSRF token");
}
```

### 7.7 Error Handling

```php
// Production error handling (in db_class.php)
try {
    $this->db = new mysqli(SERVER, USERNAME, PASSWD, DATABASE);
} catch (Exception $e) {
    error_log("Database connection error: " . $e->getMessage());  // Log to file
    die("Database connection failed. Please try again later.");   // Generic message to user
}

// Don't expose sensitive information
// ❌ BAD: die("Error: " . $e->getMessage());  // Exposes SQL structure
// ✅ GOOD: die("An error occurred. Please try again.");
```

---

## 8. API & Integration Design

### 8.1 Internal AJAX API Endpoints

All AJAX endpoints are in `actions/` directory and return JSON responses.

#### Response Format Standard
```json
{
    "success": true,
    "message": "Operation successful",
    "data": {},
    "errors": []
}
```

#### Key Endpoints

| Endpoint | Method | Purpose | Request Body | Response |
|----------|--------|---------|--------------|----------|
| `add_to_cart_action.php` | POST | Add product to cart | `{product_id, qty}` | `{success, message, cart_count}` |
| `update_quantity_action.php` | POST | Update cart quantity | `{cart_id, qty}` | `{success, message, new_total}` |
| `remove_from_cart_action.php` | POST | Remove from cart | `{cart_id}` | `{success, message, cart_count}` |
| `get_cart_count.php` | GET | Get cart item count | - | `{count}` |
| `process_checkout_action.php` | POST | Process checkout | `{delivery_address, payment_method}` | `{success, order_id, payment_url}` |
| `update_order_status.php` | POST | Update order status | `{order_id, status}` | `{success, message}` |
| `suggestion_actions.php` | POST | Submit suggestion | `{type, name, description, category_id}` | `{success, message}` |
| `fetch_dashboard_stats_action.php` | GET | Get dashboard data | - | `{total_sales, revenue, orders, products}` |

### 8.2 Payment Gateway Integration

#### Paystack Flow Diagram

```
┌────────────┐
│  Customer  │
│   (View)   │
└─────┬──────┘
      │ 1. Click "Pay Now"
      ▼
┌──────────────────────────────────┐
│  checkout.php                    │
│  - Validate order                │
│  - Create order record           │
│  - Call Paystack initialize      │
└─────┬────────────────────────────┘
      │ 2. POST /actions/paystack_init_transaction.php
      ▼
┌──────────────────────────────────┐
│  Server-Side Transaction Init    │
│  - Verify order_id               │
│  - Get order total               │
│  - Call Paystack API             │
│    POST https://api.paystack.co/ │
│         transaction/initialize   │
│  - Get authorization_url         │
└─────┬────────────────────────────┘
      │ 3. Return {authorization_url, reference}
      ▼
┌──────────────────────────────────┐
│  Redirect to Paystack            │
│  - Customer enters card/mobile   │
│  - Paystack processes payment    │
└─────┬────────────────────────────┘
      │ 4. Payment completed
      ▼
┌──────────────────────────────────┐
│  Paystack Callback               │
│  GET /view/paystack_callback.php │
│      ?reference=xxx              │
└─────┬────────────────────────────┘
      │ 5. Verify transaction
      ▼
┌──────────────────────────────────┐
│  Server-Side Verification        │
│  - Call Paystack verify API      │
│    GET https://api.paystack.co/  │
│        transaction/verify/{ref}  │
│  - Validate amount               │
│  - Validate status               │
└─────┬────────────────────────────┘
      │ 6. If verified
      ▼
┌──────────────────────────────────┐
│  Update Database                 │
│  - Update payment.payment_status │
│  - Update orders.payment_status  │
│  - Reduce product stock          │
│  - Clear cart                    │
│  - Send confirmation email       │
└─────┬────────────────────────────┘
      │ 7. Redirect to success page
      ▼
┌──────────────────────────────────┐
│  payment_success.php             │
│  - Show order summary            │
│  - Display tracking info         │
└──────────────────────────────────┘
```

#### Paystack API Configuration

```php
// settings/paystack_config.php
define('PAYSTACK_PUBLIC_KEY', 'pk_test_xxxxx');  // For client-side (if needed)
define('PAYSTACK_SECRET_KEY', 'sk_test_xxxxx');  // For server-side ONLY
define('PAYSTACK_CALLBACK_URL', 'https://yourdomain.com/view/paystack_callback.php');
```

#### Security Measures
- Secret key never exposed to client
- Amount verification on callback (prevent tampering)
- Transaction reference validation
- Webhook signature verification (for async notifications)

### 8.3 Mobile Money Integration

Mobile money payments are processed through Paystack's mobile money channels:

```php
// Initialize transaction with mobile money
$payload = [
    'email' => $customer_email,
    'amount' => $amount * 100,
    'currency' => 'GHS',
    'channels' => ['mobile_money'],  // Force mobile money
    'metadata' => [
        'order_id' => $order_id,
        'mobile_money' => [
            'phone' => $customer_phone,
            'provider' => 'mtn'  // or 'vod' for Vodafone
        ]
    ]
];
```

**Supported Providers:**
- MTN Mobile Money
- Vodafone Cash
- AirtelTigo Money (via Paystack)

---

## 9. User Interface Design

### 9.1 Responsive Design Strategy

**Mobile-First Approach with Bootstrap 5.3:**

```css
/* Mobile (default) */
.product-card { width: 100%; }

/* Tablet (768px+) */
@media (min-width: 768px) {
    .product-card { width: 48%; }
}

/* Desktop (992px+) */
@media (min-width: 992px) {
    .product-card { width: 31%; }
}
```

**Bootstrap Grid System:**
```html
<div class="container">
    <div class="row">
        <div class="col-12 col-md-6 col-lg-4">
            <!-- Product Card -->
        </div>
    </div>
</div>
```

### 9.2 User Role Dashboards

#### Customer Dashboard
**Components:**
- Order history
- Wishlist
- Profile management
- Prescription uploads
- Suggestions

**Layout:**
```
┌─────────────────────────────────────────┐
│  Header (Logo, Search, Cart, Profile)  │
├──────────┬──────────────────────────────┤
│          │  Dashboard Overview          │
│ Sidebar  │  - Recent Orders             │
│ - Orders │  - Quick Actions             │
│ - Profile│  - Wishlist                  │
│ - Wishlist│ - Prescriptions             │
│ - Prescr.│                              │
│ - Suggest│                              │
└──────────┴──────────────────────────────┘
```

#### Pharmacy Admin Dashboard
**Components:**
- My products
- Bulk upload
- Order management (own products)
- Analytics (own pharmacy)
- Inventory management

**Layout:**
```
┌─────────────────────────────────────────┐
│  Header (Logo, Notifications, Profile) │
├──────────┬──────────────────────────────┤
│          │  Sales Analytics             │
│ Sidebar  │  - Revenue Chart             │
│ - Dashb. │  - Top Products              │
│ - Product│  - Recent Orders             │
│ - Orders │                              │
│ - Bulk Up│  My Products Table           │
│ - Analyt.│  - Edit/Delete               │
└──────────┴──────────────────────────────┘
```

#### Super Admin Dashboard
**Components:**
- Platform-wide analytics
- All products management
- All orders management
- Categories & brands management
- Pharmacy management
- Suggestions review
- Reports

**Layout:**
```
┌─────────────────────────────────────────┐
│  Header (Logo, Notifications, Profile) │
├──────────┬──────────────────────────────┤
│          │  Platform Analytics          │
│ Sidebar  │  - Total Revenue             │
│ - Dashb. │  - All Orders                │
│ - Pharmac│  - Customer Growth           │
│ - Product│  - Pharmacy Performance      │
│ - Orders │                              │
│ - Categor│  Quick Actions               │
│ - Brands │  - Approve Suggestions       │
│ - Suggest│  - Review Prescriptions      │
│ - Reports│  - Manage Categories         │
└──────────┴──────────────────────────────┘
```

### 9.3 Key UI Components

#### Product Card
```html
<div class="card product-card">
    <img src="<?= $product_image ?>" class="card-img-top" alt="<?= $product_title ?>">
    <div class="card-body">
        <h5 class="card-title"><?= $product_title ?></h5>
        <p class="text-muted"><?= $brand_name ?></p>
        <div class="d-flex justify-content-between align-items-center">
            <span class="price">GH₵ <?= number_format($product_price, 2) ?></span>
            <span class="stock-badge <?= $product_stock > 0 ? 'in-stock' : 'out-of-stock' ?>">
                <?= $product_stock > 0 ? 'In Stock' : 'Out of Stock' ?>
            </span>
        </div>
        <?php if ($prescription_required): ?>
            <span class="badge bg-warning">Prescription Required</span>
        <?php endif; ?>
        <button class="btn btn-primary w-100 mt-2 add-to-cart" data-product-id="<?= $product_id ?>">
            Add to Cart
        </button>
    </div>
</div>
```

#### Cart Summary
```html
<div class="cart-summary">
    <h4>Cart Summary</h4>
    <div class="cart-items">
        <!-- Cart items list -->
    </div>
    <div class="cart-totals">
        <div class="subtotal">
            <span>Subtotal:</span>
            <span>GH₵ <span id="subtotal-amount">0.00</span></span>
        </div>
        <div class="total">
            <span>Total:</span>
            <span>GH₵ <span id="total-amount">0.00</span></span>
        </div>
    </div>
    <button class="btn btn-success w-100" id="checkout-btn">
        Proceed to Checkout
    </button>
</div>
```

#### Order Status Badge
```php
<?php
$status_classes = [
    'pending' => 'bg-warning',
    'processing' => 'bg-info',
    'shipped' => 'bg-primary',
    'delivered' => 'bg-success',
    'cancelled' => 'bg-danger'
];
?>
<span class="badge <?= $status_classes[$order_status] ?>">
    <?= ucfirst($order_status) ?>
</span>
```

### 9.4 Color Scheme & Branding

**Primary Colors:**
- Primary: #4CAF50 (Green - health, pharmacy)
- Secondary: #2196F3 (Blue - trust, reliability)
- Accent: #FF9800 (Orange - calls-to-action)
- Success: #28a745
- Warning: #ffc107
- Danger: #dc3545

**Typography:**
- Headings: Poppins (Google Fonts)
- Body: Open Sans (Google Fonts)
- Fallback: -apple-system, BlinkMacSystemFont, "Segoe UI", Roboto

---

## 10. Sequence Diagrams

### 10.1 User Registration Sequence

```
Customer     View         Action          Controller      Model       Database
  │           │            │                │              │            │
  │───Fill────▶│            │                │              │            │
  │ Form      │            │                │              │            │
  │           │            │                │              │            │
  │───Submit──▶│            │                │              │            │
  │           │            │                │              │            │
  │           │────POST────▶│                │              │            │
  │           │ register_  │                │              │            │
  │           │ user_action│                │              │            │
  │           │            │                │              │            │
  │           │            │─Validate Input─▶│              │            │
  │           │            │                │              │            │
  │           │            │─Check Email────▶│              │            │
  │           │            │                │─SELECT───────▶│            │
  │           │            │                │              │────Query──▶│
  │           │            │                │              │◀───Result──│
  │           │            │                │◀─────────────│            │
  │           │            │◀─Email Valid───│              │            │
  │           │            │                │              │            │
  │           │            │─Hash Password─▶│              │            │
  │           │            │                │              │            │
  │           │            │─Register User──▶│              │            │
  │           │            │                │─Insert User──▶│            │
  │           │            │                │              │────INSERT─▶│
  │           │            │                │              │◀──Success──│
  │           │            │                │◀─────────────│            │
  │           │            │◀─Success───────│              │            │
  │           │            │                │              │            │
  │           │◀───JSON────│                │              │            │
  │           │ {success}  │                │              │            │
  │           │            │                │              │            │
  │◀─Redirect─│            │                │              │            │
  │  to Login │            │                │              │            │
```

### 10.2 Checkout & Payment Sequence

```
Customer   View      Action      Controller   Model    Database   Paystack API
  │         │         │            │           │          │            │
  │─Cart────▶│         │            │           │          │            │
  │ Review  │         │            │           │          │            │
  │         │         │            │           │          │            │
  │─Checkout▶│         │            │           │          │            │
  │ Button  │         │            │           │          │            │
  │         │         │            │           │          │            │
  │         │─Fill────│            │           │          │            │
  │         │ Delivery│            │           │          │            │
  │         │ Address │            │           │          │            │
  │         │         │            │           │          │            │
  │─Submit──▶│         │            │           │          │            │
  │         │         │            │           │          │            │
  │         │──POST───▶│            │           │          │            │
  │         │ process_│            │           │          │            │
  │         │ checkout│            │           │          │            │
  │         │         │            │           │          │            │
  │         │         │─Validate───▶│           │          │            │
  │         │         │            │           │          │            │
  │         │         │─Get Cart───▶│           │          │            │
  │         │         │            │─SELECT────▶│          │            │
  │         │         │            │           │──Query──▶│            │
  │         │         │            │           │◀─Result──│            │
  │         │         │            │◀──────────│          │            │
  │         │         │            │           │          │            │
  │         │         │─Create─────▶│           │          │            │
  │         │         │  Order     │─INSERT────▶│          │            │
  │         │         │            │           │──INSERT─▶│            │
  │         │         │            │           │◀─Success─│            │
  │         │         │            │◀──────────│          │            │
  │         │         │            │           │          │            │
  │         │         │─Init Payment────────────────────────────────────▶│
  │         │         │            │           │          │   POST     │
  │         │         │            │           │          │ initialize │
  │         │         │◀──────────────────────────────────────{auth_url}│
  │         │         │            │           │          │            │
  │         │◀─JSON───│            │           │          │            │
  │         │ {auth_  │            │           │          │            │
  │         │  url}   │            │           │          │            │
  │         │         │            │           │          │            │
  │◀Redirect─────────────────────────────────────────────────────────────▶│
  │ to      │         │            │           │          │   Paystack │
  │ Paystack│         │            │           │          │   Payment  │
  │         │         │            │           │          │   Page     │
  │         │         │            │           │          │            │
  │─Pay─────────────────────────────────────────────────────────────────▶│
  │         │         │            │           │          │   Customer │
  │         │         │            │           │          │   enters   │
  │         │         │            │           │          │   card/MM  │
  │         │         │            │           │          │            │
  │◀Callback────────────────────────────────────────────────────────────◀│
  │ to our  │         │            │           │          │   reference│
  │ site    │         │            │           │          │            │
  │         │         │            │           │          │            │
  │         │─GET─────▶│            │           │          │            │
  │         │ callback│            │           │          │            │
  │         │ ?ref=xx │            │           │          │            │
  │         │         │            │           │          │            │
  │         │         │─Verify Payment──────────────────────────────────▶│
  │         │         │            │           │          │   GET      │
  │         │         │            │           │          │   verify/  │
  │         │         │◀──────────────────────────────────────{verified}│
  │         │         │            │           │          │            │
  │         │         │─Update─────▶│           │          │            │
  │         │         │  Payment   │─UPDATE────▶│          │            │
  │         │         │            │           │──UPDATE─▶│            │
  │         │         │            │           │◀─Success─│            │
  │         │         │            │◀──────────│          │            │
  │         │         │            │           │          │            │
  │         │         │─Reduce─────▶│           │          │            │
  │         │         │  Stock     │─UPDATE────▶│          │            │
  │         │         │            │           │──UPDATE─▶│            │
  │         │         │            │◀──────────│          │            │
  │         │         │            │           │          │            │
  │         │         │─Clear Cart─▶│           │          │            │
  │         │         │            │─DELETE────▶│          │            │
  │         │         │            │           │──DELETE─▶│            │
  │         │         │            │◀──────────│          │            │
  │         │         │            │           │          │            │
  │         │◀Redirect│            │           │          │            │
  │         │ success │            │           │          │            │
  │         │         │            │           │          │            │
  │◀Display Success──│            │           │          │            │
  │ Page    │         │            │           │          │            │
```

### 10.3 Prescription Upload & Verification

```
Customer  View    Action    Controller  Model   Database  Pharmacy  Super Admin
  │        │        │          │         │        │         │          │
  │─Upload─▶│        │          │         │        │         │          │
  │ Button │        │          │         │        │         │          │
  │        │        │          │         │        │         │          │
  │        │─Select─│          │         │        │         │          │
  │        │ Image  │          │         │        │         │          │
  │        │        │          │         │        │         │          │
  │─Submit─▶│        │          │         │        │         │          │
  │        │        │          │         │        │         │          │
  │        │─POST───▶│          │         │        │         │          │
  │        │ upload │          │         │        │         │          │
  │        │ _presc │          │         │        │         │          │
  │        │        │          │         │        │         │          │
  │        │        │─Validate─▶│         │        │         │          │
  │        │        │ File     │         │        │         │          │
  │        │        │          │         │        │         │          │
  │        │        │─Move File│         │        │         │          │
  │        │        │          │         │        │         │          │
  │        │        │─Generate─▶│         │        │         │          │
  │        │        │ Presc #  │         │        │         │          │
  │        │        │          │         │        │         │          │
  │        │        │─Save─────▶│         │        │         │          │
  │        │        │          │─INSERT──▶│        │         │          │
  │        │        │          │         │─INSERT─▶│         │          │
  │        │        │          │         │◀──OK───│         │          │
  │        │        │          │◀────────│        │         │          │
  │        │        │◀─Success─│         │        │         │          │
  │        │        │          │         │        │         │          │
  │        │◀─JSON──│          │         │        │         │          │
  │        │ {succ} │          │         │        │         │          │
  │        │        │          │         │        │         │          │
  │◀─Show──│        │          │         │        │         │          │
  │ Success│        │          │         │        │         │          │
  │        │        │          │         │        │         │          │
  ├────────────────NOTIFICATION SENT─────────────────────────────────────▶│
  │        │        │          │         │        │         │   Email/  │
  │        │        │          │         │        │         │   Notif   │
  │        │        │          │         │        │         │          │
  │        │        │          │         │        │         │◀─Login───│
  │        │        │          │         │        │         │          │
  │        │        │          │         │        │         │◀─View────│
  │        │        │          │         │        │         │ Pending  │
  │        │        │          │         │        │         │          │
  │        │        │          │         │        │         │◀─Review──│
  │        │        │          │         │        │         │ Image    │
  │        │        │          │         │        │         │          │
  │        │        │          │         │        │         │◀─Verify──│
  │        │        │          │         │        │         │ or       │
  │        │        │          │         │        │         │ Reject   │
  │        │        │          │         │        │         │          │
  │        │        │◀─────────UPDATE PRESCRIPTION STATUS──────────────────│
  │        │        │          │         │        │         │          │
  │        │        │          │─UPDATE──▶│        │         │          │
  │        │        │          │ status  │─UPDATE─▶│         │          │
  │        │        │          │         │◀──OK───│         │          │
  │        │        │          │◀────────│        │         │          │
  │        │        │          │         │        │         │          │
  ├────────────────NOTIFICATION SENT TO CUSTOMER──────────────────────────│
  │        │        │          │         │        │         │          │
  │◀─Email─│        │          │         │        │         │          │
  │ Verified        │          │         │        │         │          │
  │        │        │          │         │        │         │          │
  │─View───▶│        │          │         │        │         │          │
  │ Status │        │          │         │        │         │          │
  │        │        │          │         │        │         │          │
  │◀─Show──│        │          │         │        │         │          │
  │ Verified        │          │         │        │         │          │
  │ Badge  │        │          │         │        │         │          │
```

---

## 11. System Requirements Document

### 11.1 Stakeholders

| Stakeholder | Role | Interests |
|-------------|------|-----------|
| **Customers** | End users | Easy medication access, prescription management, secure payments |
| **Pharmacies** | Vendors | Product management, order fulfillment, inventory tracking |
| **Super Admin** | Platform owner | Platform analytics, quality control, revenue tracking |
| **FDA Ghana** | Regulator | Compliance with pharmaceutical laws |
| **Payment Providers** | Service providers | Transaction processing, fraud prevention |
| **Delivery Partners** | Logistics | Order fulfillment, delivery tracking |

### 11.2 System Constraints

**Technical Constraints:**
- Must run on LAMP/WAMP stack (Linux/Windows, Apache, MySQL, PHP)
- Must support PHP 8.1+
- Must use Bootstrap 5.3 for UI consistency
- Must integrate with Paystack API (no alternative payment gateways initially)

**Business Constraints:**
- Must comply with Ghana FDA regulations for online pharmacy sales
- Must verify pharmacy licenses before onboarding
- Prescription-required products must have valid prescriptions

**Security Constraints:**
- All passwords must be hashed with bcrypt
- Payment data must be encrypted in transit (HTTPS)
- User data must comply with data protection laws

**Performance Constraints:**
- Page load time < 3 seconds on average connection
- Support at least 100 concurrent users
- Database queries must return results < 1 second

### 11.3 Assumptions and Dependencies

**Assumptions:**
- Users have access to internet and smartphones/computers
- Pharmacies have valid licenses from FDA Ghana
- Payment providers (Paystack) APIs remain stable
- Users understand basic e-commerce workflows

**Dependencies:**
- Paystack API availability (99.9% SLA)
- Mobile Money network uptime (MTN, Vodafone)
- Email service for notifications
- Web hosting provider uptime
- SSL certificate validity

---

## 12. Future Enhancements

### 12.1 Planned Features (Post-Launch)

1. **AI Product Recommendations**
   - Collaborative filtering based on purchase history
   - "Customers who bought X also bought Y"
   - Personalized product suggestions on homepage

2. **Real-Time Order Tracking**
   - GPS integration for delivery tracking
   - SMS notifications for order updates
   - Estimated delivery time calculations

3. **Multi-Language Support**
   - English (default)
   - Twi
   - Ga
   - Ewe

4. **Advanced Analytics Dashboard**
   - Sales forecasting
   - Customer lifetime value (CLV)
   - Product performance heatmaps
   - A/B testing framework

5. **Loyalty Program**
   - Points-based rewards
   - Referral bonuses
   - Tier-based discounts

6. **Telemedicine Integration**
   - Virtual doctor consultations
   - Digital prescription generation
   - Integration with healthcare providers

7. **Inventory Management**
   - Low stock alerts
   - Automatic reorder points
   - Supplier management

8. **Mobile Apps**
   - Native Android app
   - Native iOS app
   - Progressive Web App (PWA)

### 12.2 Scalability Roadmap

**Phase 1 (0-1,000 users):**
- Shared hosting with cPanel
- Single MySQL database
- File-based image storage

**Phase 2 (1,000-10,000 users):**
- VPS or dedicated server
- Database read replicas
- CDN for static assets
- Redis caching layer

**Phase 3 (10,000+ users):**
- Load-balanced web servers
- Database sharding
- Cloud storage (AWS S3)
- Elasticsearch for search
- Microservices architecture

---

## 13. Conclusion

This System Analysis & Design document provides a comprehensive blueprint for the PharmaVault e-commerce platform. The system architecture follows industry best practices with MVC pattern, secure coding standards, and scalable database design.

Key highlights:
- **Robust Security:** bcrypt hashing, prepared statements, session management
- **Scalable Architecture:** MVC pattern, normalized database, modular design
- **Modern Tech Stack:** PHP 8.1+, MySQL 8.0+, Bootstrap 5.3
- **Payment Integration:** Paystack with mobile money support
- **Prescription Management:** Digital verification workflow
- **Multi-Role Support:** Customer, Pharmacy Admin, Super Admin

The platform addresses Ghana's medication access challenges through a B2B2C marketplace that connects licensed pharmacies with customers while ensuring regulatory compliance and secure transactions.

---

**Document Version History:**
- v1.0 (Nov 26, 2025): Initial system analysis and design documentation

**Approval:**
- [ ] CS-MIS Department Head
- [ ] Project Supervisor
- [ ] Technical Lead

**Next Steps:**
1. Review and approve this document
2. Create video presentation demonstrating system
3. Final project submission (Nov 30, 2025)
