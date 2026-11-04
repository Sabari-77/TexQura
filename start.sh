#!/bin/bash
# Start MariaDB service
service mariadb start

# Initialize database schema and default admin accounts
php /var/www/html/setup/initialize.php > /dev/null 2>&1

# Start Apache in the foreground
apache2-foreground
