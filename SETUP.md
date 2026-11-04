# ⚙️ TexQura Setup & Initialization Guide

Follow these steps to deploy the TexQura environment on a local development server or production environment.

## 1. System Prerequisites
* **Web Server:** Apache (via XAMPP, WAMP, or Laragon)
* **PHP:** Version 8.0 or higher
* **Database:** MySQL 5.7+ or MariaDB
* **Extensions:** PDO (`php_pdo_mysql`) must be enabled in your `php.ini`.

## 2. Installation Steps
1. **Clone the Repository:** 
   Extract or clone the TexQura folder into your web server's public directory:
   * XAMPP: `C:\xampp\htdocs\TexQura`
   * Laragon: `C:\laragon\www\TexQura`
2. **Start Services:** 
   Launch your control panel and start both **Apache** and **MySQL**.
3. **Verify Database Configuration:**
   Open `config/database.php` and ensure the credentials match your local MySQL setup. By default, it uses `root` with no password.

## 3. Automated System Initialization
TexQura includes an automated setup script that builds the required 14-table relational database and injects the default system roles without requiring manual SQL imports.

1. Open your web browser.
2. Navigate to: `http://localhost/TexQura/setup/initialize.php`
3. The script will dynamically drop any existing conflicting tables, build the `texqura` database, insert the core Master Data (Roles, Grade Constraints), and generate three demo user accounts.
4. Upon seeing the green "Success" message, click the link to proceed to the Login portal.

## 4. Default Credentials
* **System Administrator:** `admin@texqura.local` / `Admin@123`
* **Quality Inspector:** `inspector@texqura.local` / `Role@123`
* **Quality Manager:** `manager@texqura.local` / `Role@123`