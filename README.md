# Barangay Community Damage Reporting and Monitoring System

A centralized, responsive civic reporting and operations platform built with **Laravel 13**, **Livewire**, **Tailwind CSS**, and **Leaflet (OpenStreetMap)**. It enables residents to submit photographic damage reports with GPS coordinates from their mobile devices, while allowing barangay administrators and field personnel to verify, prioritize, assign, and track repairs on an interactive desktop dashboard.

---

## 🌟 Key Features

### 📱 Resident Mobile Experience (PWA)
- **Guided Hazard Reporting**: Form for damaged roads, fallen/exposed electrical lines, clogged drainage/flooding, broken streetlights, public facility damage, fallen trees, and garbage dumping.
- **Location Picker & GPS**: Automatic browser geolocation retrieval with an interactive Leaflet draggable map pin and manual address fallback.
- **Photo Attachments**: Multi-image uploads directly from camera or gallery.
- **Critical Safety Advisories**: Built-in hazard advisories (e.g. 10-meter perimeter warning for fallen power lines) with mandatory resident safety acknowledgments.
- **My Reports & Real-Time Tracking**: Progress timeline with clear stages (*Submitted → Under Review → Verified → Assigned → In Progress → Resolved*).
- **Public Updates Stream**: Residents receive official public notifications and updates from the barangay.
- **In-App Notification Center**: Instant alerts on assignment, inspection, and completion.
- **Edit Privileges**: Permitted edits while the report is still in the `submitted` stage before official review.
- **Progressive Web App (PWA)**: Web app manifest (`manifest.json`), service worker (`sw.js`), and installability on mobile devices.

### 💻 Administrator & Staff Desktop Dashboard
- **Executive Real-Time Statistics**: Dynamic metrics for total reports, new submissions, pending verifications, urgent hazards, in-progress jobs, and resolved issues calculated from real database records.
- **Interactive Community Map**: Full-screen and embedded Leaflet maps with status/priority-colored pins, clickable popups, and category filters.
- **Comprehensive Report Management**:
  - Filter by category, status, priority, department, and barangay.
  - Switch between Table View and Geospatial Map View.
  - Priority adjustments (*Low, Medium, High, Urgent*).
  - Status workflow enforcement (*Submitted → Under Review → Verified → Assigned → In Progress → Resolved*).
  - Mandatory reason logging for **Rejections**.
  - **Duplicate Case Handling**: Link secondary duplicate reports to primary incidents.
  - **Resolution Proof**: Upload after-resolution photographs and record completion notes.
- **Internal vs. Public Notes**: Separate private staff internal notes from public updates broadcasted to the resident's timeline.
- **Staff & Department Management**: Department configuration (*Public Works, Electrical Coordination, Drainage & Flood Mitigation, Sanitation, BDRRMC*), officer accounts, and active workload tracking.
- **Analytics & Civic Exports**: Filter by date range and department, stream CSV exports, and generate print-friendly meeting reports for the Barangay Council.
- **Audit Trails**: Full activity logging and immutable status transition history.

---

## 🛠️ Technology Stack

| Layer | Technology |
|---|---|
| **Backend** | Laravel 13, PHP 8.5, Eloquent ORM, Policies, Form Requests |
| **Frontend** | Laravel Blade, Livewire 4, Tailwind CSS, Alpine.js |
| **Database** | MySQL 8.0 (with indexed geospatial latitude/longitude) |
| **Maps** | Leaflet + OpenStreetMap (No paid Google Maps API key required) |
| **Mobile Shell** | Progressive Web App (PWA) with Service Worker and Web Manifest |
| **Testing** | PHPUnit Feature Tests covering authentication, policies, and report workflows |
| **Code Style** | Laravel Pint |

---

## 🚀 Setup & Installation (Windows Environment)

### Prerequisites
- **PHP 8.5** with `pdo_mysql`, `fileinfo`, `mbstring`, `openssl`, and `gd` extensions enabled
- **Composer 2.9+**
- **Node.js v20+** and **npm**
- **MySQL Server 8.0+**

### 1. Clone & Configure Environment
If starting from a fresh clone, copy the environment configuration:
```powershell
cp .env.example .env
```

Ensure your `.env` database parameters match your local MySQL installation:
```ini
DB_CONNECTION=mysql
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=community_reporting
DB_USERNAME=root
DB_PASSWORD=your_mysql_password

FILESYSTEM_DISK=public
```

### 2. Install Dependencies
```powershell
# Install PHP dependencies
composer install

# Install NPM packages
npm install
```

### 3. Generate App Key & Link Storage
```powershell
php artisan key:generate
php artisan storage:link
```

### 4. Database Migrations & Demonstration Seeders
Create the database in MySQL:
```sql
CREATE DATABASE IF NOT EXISTS community_reporting CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
```

Run migrations with the comprehensive demonstration dataset:
```powershell
php artisan migrate:fresh --seed
```

### 5. Compile Frontend Assets
```powershell
# For production build
npm run build

# Or for local development hot-reload
npm run dev
```

### 6. Start the Development Server
```powershell
php artisan serve
```
Open [http://127.0.0.1:8000](http://127.0.0.1:8000) in your browser.

---

## 🔐 Demonstration Accounts & Credentials

The database seeder pre-configures realistic accounts across both user roles:

| Role | Email | Password | Access Scope |
|---|---|---|---|
| **Administrator** | `admin@barangay.gov.ph` | `password123` | Complete administrative dashboard, department assignments, verification, map monitoring, analytics |
| **Resident** | `maria.santos@gmail.com` | `password123` | Mobile reporting interface, photo uploads, GPS coordinates, My Reports, personal alerts |
| **Resident** | `juan.delacruz@gmail.com` | `password123` | Mobile reporting, issue tracking, status notifications |

> **Quick Fill Tip**: The login screen features one-click demo login buttons to switch instantly between **Barangay Admin** and **Resident User** profiles during evaluation.

---

## 🛡️ Creating New Administrator Accounts Securely

To avoid hardcoded credentials in production environments, use the built-in CLI command:
```powershell
php artisan make:admin
```
Interactive prompt:
```
Administrator Full Name: Capt. Juan Dela Cruz
Administrator Email: captain@barangaysanisidro.gov.ph
Administrator Password: [hidden]
```
Alternatively, pass flags directly:
```powershell
php artisan make:admin --name="Admin Name" --email="admin2@barangay.gov.ph" --password="SecurePassword123"
```

---

## 🚀 Production Deployment Checklist

When deploying to a staging or production server, execute these commands to enable class-map autoloader optimization, warm route/config/view caches, and run pending migrations:

```bash
# 1. Install PHP dependencies with optimized autoloader (skip dev packages)
composer install --optimize-autoloader --no-dev

# 2. Run pending database migrations
php artisan migrate --force

# 3. Cache configuration, routes, Blade templates, and events
php artisan config:cache
php artisan route:cache
php artisan view:cache
php artisan event:cache

# 4. Build minified frontend assets
npm ci
npm run build

# 5. Create storage symlink if not already linked
php artisan storage:link

# 6. Restart queue worker (if running asynchronous background jobs)
php artisan queue:restart
```

### Server-Level Gzip / Brotli & Static Caching (Nginx Example)
Ensure Nginx or Apache has gzip/brotli and long-lived client caching enabled for static assets and photo thumbnails:
```nginx
# Nginx Gzip configuration
gzip on;
gzip_comp_level 5;
gzip_min_length 256;
gzip_proxied any;
gzip_types application/javascript application/json application/xml image/svg+xml text/css text/plain text/xml;

# Static asset and evidence thumbnail caching
location ~* \.(ico|jpg|jpeg|png|gif|webp|svg|woff2?|css|js)$ {
    expires 1y;
    add_header Cache-Control "public, max-age=31536000, immutable";
    access_log off;
}
```

---

## 🧪 Automated Testing

Execute the PHPUnit feature test suite covering authorization gates, policies, role access controls, resident submission flows, status workflows, and duplicate handling:
```powershell
php artisan test
```

---

## ⚡ Emergency Services & Electrical Hazard Disclaimer

> [!WARNING]
> This application serves as a municipal and barangay maintenance coordination system. It does **not** provide direct telemetry or automatic dispatch to utility companies or 911.
> For fallen, submerged, or sparking electrical lines, residents and staff are instructed to maintain a **10-meter (33-foot) safety perimeter** and contact emergency dispatch (**911**) or Meralco (**16211**) immediately.

---

## 📂 Project Structure Overview

```
CRuv/
├── app/
│   ├── Console/Commands/CreateAdminUser.php
│   ├── Http/
│   │   ├── Controllers/AuthController.php
│   │   └── Middleware/EnsureUserRole.php
│   ├── Livewire/
│   │   ├── Admin/ (Dashboard, Reports, Map, Staff, Analytics)
│   │   └── Resident/ (Home, SubmitReport, ReportList, ReportDetail, NotificationCenter)
│   ├── Models/ (Report, User, Department, ReportCategory, ReportImage, ReportAssignment, ...)
│   └── Policies/ReportPolicy.php
├── database/
│   ├── migrations/ (10 indexed, constrained migrations)
│   └── seeders/DatabaseSeeder.php
├── public/
│   ├── icons/ (PWA vector icons)
│   ├── manifest.json (PWA configuration)
│   └── sw.js (Service worker cache & offline fallbacks)
├── resources/
│   ├── views/ (Blade templates & Livewire views)
│   ├── css/app.css (Tailwind CSS v4 & Leaflet CSS)
│   └── js/app.js (Leaflet & application bootstrapping)
└── tests/
    └── Feature/ (AuthWorkflowTest, ReportWorkflowTest)
```
