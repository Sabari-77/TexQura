# 🛡️ TexQura - Enterprise Textile Quality Management System

![UI Preview](https://img.shields.io/badge/UI-Glassmorphism-8b5cf6?style=flat-square)
![Status](https://img.shields.io/badge/Status-Production_Ready-10b981?style=flat-square)
![Stack](https://img.shields.io/badge/Stack-PHP_8%20|%20MySQL%20|%20Bootstrap_5-blue?style=flat-square)

TexQura is a professional, robust Quality Control (QC) and Defect Management System designed specifically for the textile industry. It enforces strict enterprise workflows, automated grade calculations, and role-based access control (RBAC) to ensure seamless and transparent quality tracking from raw fabric receipt to final certification.

## ✨ Core Features
* **Role-Based Access Control (RBAC):** Distinct workflows for Administrators, Quality Inspectors, and Quality Managers.
* **Maker-Checker Architecture:** Built-in security constraints preventing Inspectors from approving their own quality logs to prevent fraud.
* **Automated Grade Calculation:** Dynamic algorithms that calculate final roll grades (Grade A, Grade B, Reject) based on total defect percentages against strict business rules.
* **Dynamic Inventory & Batch Tracking:** Full traceability of suppliers, fabric types, delivery batches, and individual roll lengths.
* **Constraint-Driven Validation:** JavaScript and Backend (PDO) constraints prevent data anomalies (e.g., logging a defect length larger than the roll itself).
* **Modern UI/UX:** A highly responsive, vibrant, glassmorphism-inspired interface built with Bootstrap 5.3 and custom CSS.

## 🛠️ Technology Stack
* **Backend:** PHP (Native / PDO)
* **Database:** MySQL (InnoDB for relational integrity)
* **Frontend:** HTML5, CSS3, JavaScript, Bootstrap 5.3
* **Icons:** Bootstrap Icons

## 🚀 Installation & Setup
1. Clone this repository to your local server directory (e.g., `htdocs` for XAMPP or `www` for Laragon).
2. Start your Apache and MySQL services.
3. Open your browser and navigate to the initialization script to automatically build the database schema and default roles:
   ```text
   http://localhost/TexQura/setup/initialize.php