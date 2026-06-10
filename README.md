# 🎓 UNFPA Workshop MIS - Complete RBAC System

A comprehensive workshop management system with enterprise-grade authentication, role-based access control, and flexible permission management.

## ✨ Features Overview

### 🔐 Authentication & Security
- ✅ User registration (signup) with email validation
- ✅ Secure login with bcrypt password hashing
- ✅ Session-based authentication
- ✅ Logout with session cleanup
- ✅ Protected pages with middleware guards
- ✅ Password visibility toggle in auth forms
- ✅ Flash messages for user feedback

### 👥 Role-Based Access Control (RBAC)

**5 Predefined Roles with Hierarchy**:
```
1. Super Admin    (all access)
2. Admin          (user & participant management)
3. Manager        (workshop & analytics)
4. Lead           (dashboard & analytics)
5. User           (dashboard only)
```

### 🎯 Advanced Features
- **Role Hierarchy Enforcement**: Admins can only manage users of lower rank
- **Module-Based Permissions**: Granular feature-level access control
- **Dynamic Menu Rendering**: Navigation adapts based on permissions
- **Visual Module UI**: Modern checkbox-based module assignment
- **User Management**: Super Admin can view/assign roles to all users
- **Audit Trail**: Track which migrations have been applied

## 🚀 Quick Start

### 1️⃣ Create Database
```sql
CREATE DATABASE workshop_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### 2️⃣ Run Migrations
```bash
php migrate.php run
```

### 3️⃣ Access Application
```
http://localhost/workshop_dashboard/
```

## 🔑 Default Credentials
- **Email**: `admin@unfpa.local`
- **Password**: `Admin@123`

⚠️ Change immediately after first login!

## 📖 Documentation

- [MIGRATIONS.md](MIGRATIONS.md) — Database migration system
- [Installation Guide](#installation) — Detailed setup instructions
- [Architecture](#-system-architecture) — How RBAC works

## Key Features

| Feature | Status |
|---------|:------:|
| User Registration | ✓ |
| Secure Login | ✓ |
| Role Management | ✓ |
| Module Permissions | ✓ |
| User Hierarchy | ✓ |
| Dynamic Navigation | ✓ |
| Database Migrations | ✓ |
| Access Control | ✓ |

---

# 📋 Full Documentation

## Installation

### Step 1: Copy to Web Server

**XAMPP:**
```bash
C:\xampp\htdocs\workshop_dashboard\
```

**WAMP:**
```bash
C:\wamp64\www\workshop_dashboard\
```

### Step 2: Create Database
```sql
CREATE DATABASE workshop_dashboard CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

### Step 3: Run Migrations
```bash
php migrate.php run
```

### Step 4: Configure (Optional)
Edit `db.php`:
```php
$DB_HOST = 'localhost';
$DB_NAME = 'workshop_dashboard';
$DB_USER = 'root';
$DB_PASS = '';
```

### Step 5: Set Permissions
```bash
chmod 755 uploads/  # Linux/Mac
```

### Step 6: Access
```
http://localhost/workshop_dashboard/
```

## File Structure

```
workshop_dashboard/
├── index.php                 # Dashboard
├── participants.php          # Participant list
├── participant_form.php      # Add/edit
├── participant_delete.php    # Delete
├── workshops.php             # Workshop management
├── analytics.php             # Reports
├── export.php                # CSV export
├── login.php                 # Login form
├── signup.php                # Registration
├── logout.php                # Logout
├── users.php                 # User management (Admin+)
├── roles.php                 # Role CRUD (Super Admin)
├── modules.php               # Module CRUD (Super Admin)
├── header.php                # Dynamic sidebar
├── footer.php                # Footer
├── db.php                    # DB connection
├── auth.php                  # RBAC functions
├── migrate.php               # Migration runner
├── database.sql              # Schema dump
├── MIGRATIONS.md             # Migration docs
├── assets/style.css          # Styling
├── migrations/               # Migration files
└── uploads/                  # Photos
```

## Authorization Matrix

| Module | Super Admin | Admin | Manager | Lead | User |
|--------|:-:|:-:|:-:|:-:|:-:|
| Dashboard | ✓ | ✓ | ✓ | ✓ | ✓ |
| Participants | ✓ | ✓ | ✓ | ✗ | ✗ |
| Workshops | ✓ | ✓ | ✓ | ✗ | ✗ |
| Analytics | ✓ | ✓ | ✓ | ✓ | ✗ |
| Export | ✓ | ✓ | ✗ | ✗ | ✗ |
| Users | ✓ | ✓ | ✗ | ✗ | ✗ |
| Roles | ✓ | ✗ | ✗ | ✗ | ✗ |
| Modules | ✓ | ✗ | ✗ | ✗ | ✗ |

## Core Functions

### Session Management
```php
auth_current_user()           # Get active user
auth_is_logged_in()           # Check auth
auth_login_user($user)        # Create session
auth_logout_user()            # Destroy session
```

### Permission Checks
```php
auth_require_module('slug')   # Block if no access
auth_require_role('Admin')    # Block if role mismatch
auth_can_access_module('slug') # Boolean check
auth_user_has_role('Admin')   # Boolean check
```

### Role Hierarchy
```php
auth_get_role_rank($name)     # Get role priority
auth_can_manage_role($curr, $target)  # Hierarchy check
```

### Navigation
```php
auth_get_module_nav_items()   # Get sidebar items
auth_get_allowed_modules()    # Get module list
auth_get_user_role_name()     # Get role name
```

## Database Migrations

### Commands
```bash
php migrate.php run            # Apply migrations
php migrate.php status         # Show status
php migrate.php rollback       # Undo last
```

### Create Migration
1. Create `migrations/YYYY-MM-DD_HHmmss_description.php`
2. Write SQL code
3. Create `.rollback.php` file
4. Run `php migrate.php run`

## Usage Examples

### Protect Page
```php
<?php
require_once __DIR__.'/db.php';
auth_require_module('participants');
require_once __DIR__.'/header.php';
// Page content
?>
```

### Create Role (UI)
1. Login as Super Admin
2. Roles → Create Role
3. Enter name and select modules
4. Save

### Assign Role (UI)
1. Login as Admin
2. Users → Select role → Save

## Security

### ✅ Implemented
- Bcrypt password hashing
- SQL injection prevention
- Output escaping
- Session regeneration
- Role-based middleware
- Access denied responses

### ⚠️ Production
- Enable HTTPS
- Add CSRF tokens
- Implement logging
- Strong passwords
- Firewall rules
- Regular updates
- Backup strategy

## Troubleshooting

### "Access Denied"
- Check user role in Users page
- Verify role has modules assigned
- Clear cache and retry

### Migration fails
- Verify MySQL permissions
- Check migrations/ directory exists
- Validate SQL syntax

### Photos not uploading
- Ensure uploads/ exists and writable
- Check disk space
- Verify php.ini settings

## Architecture

```
Browser → Login/Signup
   ↓
Session Created
   ↓
auth.php Functions
   ↓
Page Request
   ↓
Permission Check (auth_require_module)
   ↓
If allowed: render with dynamic header
If denied: show access denied page
```

## Support

For issues or requests, contact your development team.

---

**UNFPA Workshop Management System** © 2026  
*Complete RBAC Implementation with Migrations*
