# Teknik Mimari Dokümantasyonu
## RİBA Anket Yönetim Sistemi

**Versiyon:** 1.0  
**Tarih:** 11 Ocak 2026  
**Durum:** Production Ready

---

## 📐 Mimari Genel Bakış

### Sistem Mimarisi

```
┌─────────────────────────────────────────────────────────────────┐
│                         PRESENTATION LAYER                      │
│  ┌──────────────┐  ┌──────────────┐  ┌──────────────┐         │
│  │   Web UI     │  │  Admin Panel │  │ School Panel │         │
│  │ (Bootstrap)  │  │   (super)    │  │   (school)   │         │
│  └──────┬───────┘  └──────┬───────┘  └──────┬───────┘         │
│         │                  │                  │                  │
└─────────┼──────────────────┼──────────────────┼─────────────────┘
          │                  │                  │
          ▼                  ▼                  ▼
┌─────────────────────────────────────────────────────────────────┐
│                        APPLICATION LAYER                        │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │            PHP Application (Procedural)                  │  │
│  │  • Authentication (includes/auth.php)                    │  │
│  │  • Database Connection (includes/db.php)                 │  │
│  │  • Business Logic (embedded in pages)                    │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────┬───────────────────────────────┘
                                  │
                                  ▼
┌─────────────────────────────────────────────────────────────────┐
│                          DATA LAYER                             │
│  ┌──────────────────────────────────────────────────────────┐  │
│  │                    MySQL Database                        │  │
│  │  • Users & Schools (Multi-tenant)                        │  │
│  │  • Form Templates & Questions                            │  │
│  │  • Surveys & Responses                                   │  │
│  │  • Settings & Audit (planned)                            │  │
│  └──────────────────────────────────────────────────────────┘  │
└─────────────────────────────────────────────────────────────────┘
```

---

## 🏛️ Architectural Patterns

### 1. Multi-Tenant Architecture

**Pattern:** Shared Database, Shared Schema  
**Implementation:** Row-level isolation via `school_id`

```sql
-- Tenant isolation example
SELECT * FROM surveys WHERE school_id = ?

-- Super admin can access all
SELECT * FROM surveys

-- School admin restricted
SELECT * FROM surveys WHERE school_id = :current_user_school_id
```

**Advantages:**
✅ Single codebase  
✅ Easy maintenance  
✅ Cost-effective (shared resources)  
✅ Simple backup/restore

**Considerations:**
⚠️ Careful permission checking required  
⚠️ No physical data separation  
⚠️ All tenants affected by downtime

---

### 2. Authentication & Authorization

#### Authentication Flow

```
┌─────────────┐
│   Login     │
│   Request   │
└──────┬──────┘
       │
       ▼
┌─────────────────────────────────────┐
│  1. CSRF Token Validation           │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  2. Input Sanitization              │
│     • email (filter_var)            │
│     • password (raw, for bcrypt)    │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  3. Database Lookup                 │
│     SELECT * FROM users             │
│     WHERE email = ?                 │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  4. Password Verification           │
│     password_verify()               │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  5. Status Check                    │
│     status == 'active'              │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  6. Session Creation                │
│     $_SESSION['user_id']            │
│     $_SESSION['user_role']          │
│     $_SESSION['school_id']          │
└──────┬──────────────────────────────┘
       │
       ▼
┌─────────────────────────────────────┐
│  7. Role-based Redirect             │
│     • super_admin → /admin/         │
│     • school_admin → /school/       │
└─────────────────────────────────────┘
```

#### Authorization Model

**Role-Based Access Control (RBAC)**

```php
// Permission Matrix
┌──────────────────┬──────────────┬───────────────┐
│     Resource     │ Super Admin  │ School Admin  │
├──────────────────┼──────────────┼───────────────┤
│ View All Schools │      ✅      │      ❌       │
│ Create School    │      ✅      │      ❌       │
│ Edit School      │      ✅      │      ❌       │
│ Delete School    │      ✅      │      ❌       │
│ System Settings  │      ✅      │      ❌       │
├──────────────────┼──────────────┼───────────────┤
│ View Own School  │      ✅      │      ✅       │
│ Manage Classes   │      ✅      │      ✅       │
│ Create Survey    │      ✅      │      ✅       │
│ View Responses   │      ✅      │      ✅       │
│ School Settings  │      ✅      │      ✅       │
└──────────────────┴──────────────┴───────────────┘
```

**Implementation:**

```php
// includes/auth.php
function require_role($role) {
    require_login();
    if ($_SESSION['user_role'] !== $role) {
        header('Location: /index.php');
        exit;
    }
}

// Usage in admin/schools.php
<?php
require_once '../includes/auth.php';
require_super_admin(); // Will redirect if not super admin
```

---

### 3. Data Flow Architecture

#### Survey Creation Flow

```
School Admin Dashboard
        │
        ▼
[Create Survey] Button
        │
        ▼
┌─────────────────────────────────────┐
│   survey-create.php                 │
│   • Select Form Template            │
│   • Enter Title/Description         │
│   • Choose Target Classes           │
│   • Enable/Disable Gender Field     │
└──────────┬──────────────────────────┘
           │
           ▼ POST
┌─────────────────────────────────────┐
│   Process Form                      │
│   1. CSRF Validation                │
│   2. Input Validation               │
│   3. Generate Link Token            │
│      (random_bytes(32))             │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│   Database INSERT                   │
│   INSERT INTO surveys (             │
│     school_id,                      │
│     form_template_id,               │
│     title,                          │
│     link_token,                     │
│     ...                             │
│   )                                 │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│   Redirect to surveys.php           │
│   Flash Message: "Anket oluşturuldu"│
└─────────────────────────────────────┘
```

#### Survey Response Flow

```
Participant Receives Link
https://domain.com/survey/fill.php?token=abc123...
        │
        ▼
┌─────────────────────────────────────┐
│   survey/fill.php                   │
│   • Load Survey by Token            │
│   • Check Status (active)           │
│   • Load Questions                  │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│   Display Form                      │
│   • Optional: Gender Field          │
│   • 10 Questions (A/B options)      │
│   • Submit Button                   │
└──────────┬──────────────────────────┘
           │
           ▼ POST
┌─────────────────────────────────────┐
│   Process Response                  │
│   1. Validate All Answered          │
│   2. Build JSON Answers             │
│      {"q1": "a", "q2": "b", ...}    │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│   Database Transaction              │
│   BEGIN TRANSACTION                 │
│   • INSERT INTO responses           │
│   • UPDATE surveys                  │
│     SET response_count++            │
│   COMMIT                            │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│   Redirect to thank-you.php         │
└─────────────────────────────────────┘
```

---

## 🗄️ Database Architecture

### Schema Design Philosophy

**Normalized Design:** 3NF (Third Normal Form)  
**Relationships:** Foreign Keys with Cascades  
**Data Types:** Appropriate sizing and constraints

### Entity Relationship Model

```
┌──────────────┐
│   schools    │ 1
│ id           │────────┐
│ name         │        │
│ slug         │        │
└──────────────┘        │ n
                        │
┌──────────────┐        │
│   users      │ n      │
│ id           │────────┤
│ school_id    │◄───────┘
│ role         │
│ email        │
└──────────────┘

┌──────────────┐ 1     ┌──────────────┐ 1
│form_templates│───────│   surveys    │───────┐
│ id           │       │ id           │       │
│ kademe       │   n   │ school_id    │   n   │
│ role         │       │ link_token   │       │
└──────┬───────┘       └──────────────┘       │
       │ 1                                     │
       │                                       ▼
       │ n                            ┌──────────────┐
       └──────────────────────────────│  responses   │
       ┌──────────────┐               │ id           │
       │  questions   │               │ survey_id    │
       │ id           │               │ answers(JSON)│
       │ form_temp_id │               └──────────────┘
       │ option_a     │
       │ option_b     │
       └──────────────┘
```

### Indexing Strategy

```sql
-- Primary Keys (Clustered Index)
✅ All tables have AUTO_INCREMENT PRIMARY KEY

-- Secondary Indexes
✅ users(email) - Login performance
✅ users(role) - Role filtering
✅ schools(slug) - URL lookup
✅ schools(status) - Active schools filter
✅ surveys(link_token) - Token lookup (CRITICAL)
✅ surveys(school_id) - Tenant isolation
✅ responses(survey_id) - Response aggregation
```

**Query Performance:**

```sql
-- Fast lookup by token (indexed)
SELECT * FROM surveys WHERE link_token = ?
-- Uses INDEX idx_token

-- Fast tenant queries (indexed)
SELECT * FROM surveys WHERE school_id = ?
-- Uses INDEX idx_school

-- Login query (indexed)
SELECT * FROM users WHERE email = ?
-- Uses INDEX idx_email
```

### Data Integrity

**Foreign Key Constraints:**

```sql
-- Cascade Delete Examples
users.school_id → schools.id ON DELETE CASCADE
  • Okul silinince, o okuldaki adminler de silinir

surveys.school_id → schools.id ON DELETE CASCADE
  • Okul silinince, o okuldaki anketler de silinir

responses.survey_id → surveys.id ON DELETE CASCADE
  • Anket silinince, yanıtları da silinir

questions.form_template_id → form_templates.id ON DELETE CASCADE
  • Form silinince, soruları da silinir
```

---

## 🔐 Security Architecture

### Defense in Depth

```
┌─────────────────────────────────────────────────────────┐
│              Layer 1: Network Security                  │
│  • HTTPS (TLS 1.2+)                                     │
│  • Firewall Rules                                       │
│  • DDoS Protection (CloudFlare/AWS Shield)              │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              Layer 2: Web Server Security               │
│  • Security Headers (.htaccess)                         │
│  • Directory Listing Disabled                           │
│  • Config Directory Protected                           │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              Layer 3: Application Security              │
│  • CSRF Token Protection                                │
│  • XSS Output Escaping                                  │
│  • SQL Injection Protection (PDO)                       │
│  • Session Security                                     │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              Layer 4: Authentication                    │
│  • bcrypt Password Hashing                              │
│  • Status Validation                                    │
│  • Role-Based Access Control                            │
└─────────────────────────────────────────────────────────┘
                          │
                          ▼
┌─────────────────────────────────────────────────────────┐
│              Layer 5: Data Security                     │
│  • Database Encryption (at rest)                        │
│  • Prepared Statements                                  │
│  • Input Validation                                     │
└─────────────────────────────────────────────────────────┘
```

### Token Generation Security

```php
// Survey link token generation
$token = bin2hex(random_bytes(32));
// Generates: 64 character hex string
// Entropy: 256 bits
// Collision probability: negligible (2^256 possibilities)

// Example token:
// 8f7a3b2c1d9e0f4a5b6c7d8e9f0a1b2c3d4e5f6a7b8c9d0e1f2a3b4c5d6e7f8
```

**Token Properties:**
- ✅ Cryptographically secure (`random_bytes`)
- ✅ Unpredictable (high entropy)
- ✅ URL-safe (hex encoding)
- ✅ Unique (database constraint)
- ✅ No expiration (persistent link)

---

## 📦 Component Architecture

### Core Components

#### 1. Authentication Component
**File:** `includes/auth.php`

**Responsibilities:**
- Session management
- CSRF token generation/validation
- Login/logout functions
- Role checks
- Flash messages

**Functions:**
```php
generate_csrf_token()      // Create CSRF token
verify_csrf_token($token)  // Validate CSRF token
is_logged_in()             // Check login status
logout()                   // Destroy session
require_login()            // Force login
require_role($role)        // Enforce role
require_super_admin()      // Super admin only
require_school_admin()     // School admin only
get_logged_in_user()       // Get user data
e($string)                 // XSS protection
set_flash_message()        // User feedback
get_flash_message()        // Display message
```

#### 2. Database Component
**File:** `includes/db.php`

**Responsibilities:**
- PDO connection
- Connection pooling
- Error handling
- Configuration loading

**Features:**
```php
✅ PDO with prepared statements
✅ ERRMODE_EXCEPTION
✅ FETCH_ASSOC default
✅ EMULATE_PREPARES disabled
✅ UTF8MB4 charset
```

#### 3. Layout Components

**Admin Layout:**
- `admin/header.php` - Navigation, session check
- `admin/footer.php` - Scripts, closing tags

**School Layout:**
- `school/header.php` - Navigation, session check
- `school/footer.php` - Scripts, closing tags

**Common Elements:**
```html
<!-- Bootstrap 5.3 -->
<link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css">

<!-- Font Awesome 6.4 -->
<link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css">

<!-- Bootstrap JS Bundle -->
<script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js">
```

---

## 🔄 Request Lifecycle

### Typical Request Flow

```
1. HTTP Request
   ↓
2. Web Server (Apache/Nginx)
   ↓
3. PHP Script Execution
   ├→ Session Start (auth.php)
   ├→ Database Connect (db.php)
   ├→ Authentication Check
   ├→ CSRF Validation (if POST)
   ├→ Business Logic
   ├→ Database Query (PDO)
   └→ Response Generation
   ↓
4. HTTP Response
   ├→ Headers (security headers)
   ├→ HTML (escaped output)
   └→ Session Cookie
```

### Example: Login Request

```
POST /login.php HTTP/1.1
Content-Type: application/x-www-form-urlencoded

csrf_token=abc123...&email=user@example.com&password=secret

           ↓

┌─────────────────────────────────────┐
│  1. Session Start                   │
│     • HttpOnly cookie               │
│     • Secure flag (HTTPS)           │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│  2. CSRF Validation                 │
│     verify_csrf_token()             │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│  3. Input Sanitization              │
│     filter_var(FILTER_SANITIZE_*)   │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│  4. Database Query                  │
│     SELECT * FROM users             │
│     WHERE email = ? (prepared)      │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│  5. Password Verification           │
│     password_verify()               │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│  6. Status & Role Check             │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│  7. Session Variables               │
│     $_SESSION['user_id']            │
│     $_SESSION['user_role']          │
└──────────┬──────────────────────────┘
           │
           ▼
┌─────────────────────────────────────┐
│  8. Redirect Response               │
│     HTTP/1.1 302 Found              │
│     Location: /admin/ or /school/   │
└─────────────────────────────────────┘
```

---

## 🎨 Frontend Architecture

### UI Framework Stack

```
┌──────────────────────────────────────┐
│         Bootstrap 5.3                │
│  • Grid System (12-column)           │
│  • Components (Cards, Modals, etc.)  │
│  • Utilities (Spacing, Colors)       │
└──────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│       Font Awesome 6.4               │
│  • Icons (fas, far, fab)             │
│  • 2000+ icons available             │
└──────────────────────────────────────┘
           │
           ▼
┌──────────────────────────────────────┐
│       Custom Styling                 │
│  • Gradient backgrounds              │
│  • Card shadows                      │
│  • Hover effects                     │
└──────────────────────────────────────┘
```

### Responsive Design

**Breakpoints:**
```css
/* Bootstrap 5 breakpoints */
xs: < 576px   (Extra small devices)
sm: ≥ 576px   (Small devices)
md: ≥ 768px   (Medium devices)
lg: ≥ 992px   (Large devices)
xl: ≥ 1200px  (Extra large devices)
xxl: ≥ 1400px (Extra extra large devices)
```

**Implementation:**
```html
<!-- Responsive grid -->
<div class="row">
    <div class="col-12 col-md-6 col-lg-3">
        <!-- 100% mobile, 50% tablet, 25% desktop -->
    </div>
</div>
```

---

## 🚀 Deployment Architecture

### Recommended Setup

```
┌─────────────────────────────────────────────────────────┐
│                    Load Balancer                        │
│             (CloudFlare / AWS ELB)                      │
└────────────────────┬────────────────────────────────────┘
                     │
        ┌────────────┴────────────┐
        │                         │
        ▼                         ▼
┌───────────────┐         ┌───────────────┐
│  Web Server 1 │         │  Web Server 2 │
│  (Apache/PHP) │         │  (Apache/PHP) │
└───────┬───────┘         └───────┬───────┘
        │                         │
        └────────────┬────────────┘
                     │
                     ▼
┌─────────────────────────────────────────────────────────┐
│              Database Server (MySQL)                    │
│         • Master-Slave Replication (optional)           │
│         • Automated Backups                             │
└─────────────────────────────────────────────────────────┘
```

### File Structure on Server

```
/var/www/html/              (or /home/user/public_html)
│
├── admin/                  (Super Admin UI)
├── school/                 (School Admin UI)
├── survey/                 (Public Survey Forms)
├── includes/               (Shared PHP code)
├── database/               (SQL scripts - protect!)
├── config/                 (Configuration - protect!)
│   ├── config.php
│   └── .installed
├── logs/                   (Application logs)
│   └── error.log
├── .htaccess              (Apache config)
├── index.php
├── login.php
└── install.php
```

### Environment Configuration

```php
// config/config.php
<?php
define('DB_HOST', 'localhost');
define('DB_NAME', 'riba_system');
define('DB_USER', 'riba_user');
define('DB_PASS', 'secure_password_here');

// Environment
define('ENV', 'production'); // or 'development'
define('DEBUG_MODE', false); // NEVER true in production

// URLs
define('BASE_URL', 'https://yourdomain.com');

// Paths
define('ROOT_PATH', __DIR__ . '/..');
define('LOG_PATH', ROOT_PATH . '/logs');
```

---

## 📊 Performance Considerations

### Database Optimization

```sql
-- Query optimization examples

-- Good: Uses index
SELECT * FROM surveys 
WHERE link_token = 'abc123' 
LIMIT 1;

-- Good: Uses index + filters
SELECT * FROM surveys 
WHERE school_id = 5 
  AND status = 'active'
ORDER BY created_at DESC 
LIMIT 10;

-- Avoid: Full table scan
SELECT * FROM responses 
WHERE JSON_EXTRACT(answers, '$.question_1') = 'a';
```

### Caching Strategy

**Current:** No caching implemented

**Recommended:**
```php
// PHP Opcache (recommended for production)
opcache.enable=1
opcache.memory_consumption=128
opcache.max_accelerated_files=10000

// Application-level caching (future)
- Form templates (static data)
- School list (super admin)
- Dashboard statistics (cache 5 min)
```

### Asset Optimization

**Current:**
- ✅ CDN for Bootstrap/Font Awesome
- ❌ No custom CSS minification
- ❌ No custom JS minification
- ❌ No image optimization

**Recommended:**
```html
<!-- Preload critical resources -->
<link rel="preload" href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" as="style">

<!-- Defer non-critical JS -->
<script src="app.js" defer></script>

<!-- Lazy load images -->
<img src="image.jpg" loading="lazy">
```

---

## 🔧 Maintenance & Operations

### Backup Strategy

```bash
# Database backup (daily)
mysqldump -u riba_user -p riba_system > backup_$(date +%Y%m%d).sql

# Full backup (weekly)
tar -czf full_backup_$(date +%Y%m%d).tar.gz /var/www/html

# Retention: 30 days daily, 12 weeks weekly
```

### Monitoring

**Recommended Monitoring:**
```
• Database size growth
• Response time (< 200ms target)
• Error rate (< 0.1% target)
• Login failure rate
• Survey response rate
• Disk space usage
• MySQL connection count
```

### Logging

**Current:** Minimal logging

**Recommended:**
```php
// Error logging
error_log("[ERROR] " . $message, 3, "/var/www/html/logs/error.log");

// Access logging (Apache)
CustomLog /var/log/apache2/riba_access.log combined

// Application logging
[2026-01-11 10:30:15] INFO: Survey created (ID: 123, School: 5)
[2026-01-11 10:31:22] WARNING: Failed login attempt (user@example.com)
[2026-01-11 10:32:10] ERROR: Database connection failed
```

---

## 📈 Scalability

### Current Limitations

```
• Single database server (SPOF)
• No connection pooling
• No caching layer
• Session in filesystem (not distributed)
```

### Scaling Strategy

#### Vertical Scaling (Short-term)
```
• Upgrade server resources (CPU, RAM, disk)
• MySQL tuning (buffer pools, query cache)
• PHP-FPM optimization
• SSD storage
```

#### Horizontal Scaling (Long-term)
```
1. Application Tier
   • Multiple web servers behind load balancer
   • Shared filesystem (NFS) or S3
   • Redis for session storage

2. Database Tier
   • Master-Slave replication (read replicas)
   • Connection pooling (ProxySQL, PgBouncer)
   • Query caching (Redis, Memcached)

3. CDN
   • Static assets to CDN
   • CloudFlare/CloudFront
```

---

## 🎯 Best Practices Implemented

✅ **Security First**
- PDO prepared statements
- bcrypt password hashing
- CSRF protection
- XSS output escaping
- Session security

✅ **Code Organization**
- Modular includes
- Separation of admin/school panels
- Reusable auth functions

✅ **Database Design**
- Normalized schema
- Foreign key constraints
- Proper indexing
- UTF8MB4 support

✅ **User Experience**
- Responsive design
- Flash messages
- Intuitive navigation
- Modern UI

✅ **Maintainability**
- Clean code structure
- Consistent naming
- Turkish documentation
- Easy deployment

---

**Document Version:** 1.0  
**Last Updated:** 11 Ocak 2026  
**Maintained By:** Development Team
