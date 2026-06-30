SPES Management System
Thesis CD Submission ReadMe

System Overview
The SPES Management System is a Laravel 12, Vue 3, and Inertia.js web application for managing the Special Program for Employment of Students. It includes role-based access for Admin, PESO, Employer, and Beneficiary users.

Hardware Requirements
- Processor: Intel Core i3 or equivalent, Intel Core i5 or higher recommended
- Memory: 4 GB RAM minimum, 8 GB RAM recommended
- Storage: At least 2 GB free disk space for the system, dependencies, and database
- Display: 1366 x 768 resolution or higher
- Network: Localhost/LAN connection if accessed by multiple computers

Software Requirements
- Operating System: Windows 10 or Windows 11
- XAMPP with Apache and MySQL/MariaDB
- PHP 8.2 or higher
- Composer
- Node.js and npm
- Web Browser: Google Chrome, Microsoft Edge, or Mozilla Firefox
- Optional: phpMyAdmin for easier database import

Database Import Instructions
1. Open XAMPP Control Panel.
2. Start Apache and MySQL.
3. Open phpMyAdmin at http://localhost/phpmyadmin.
4. Create a database named managements.
5. Select the managements database.
6. Click Import.
7. Choose the SQL file:
   Database/spes_management.sql
8. Click Go and wait for the import to finish.

Installation Instructions
1. Copy the Proposed-System/spesmanagement folder to:
   C:\xampp\htdocs\spesmanagement

2. Open a terminal or command prompt inside:
   C:\xampp\htdocs\spesmanagement

3. Copy the environment file if .env is not present:
   copy .env.example .env

4. Check the database settings in .env:
   DB_CONNECTION=mysql
   DB_HOST=127.0.0.1
   DB_PORT=3306
   DB_DATABASE=managements
   DB_USERNAME=root
   DB_PASSWORD=

5. Install PHP dependencies if the vendor folder is not included:
   composer install

6. Generate the Laravel application key if needed:
   php artisan key:generate

7. Create the storage link:
   php artisan storage:link

8. Install frontend dependencies if node_modules is not included:
   npm install

9. Build frontend assets if public/build is missing or needs to be regenerated:
   npm run build

10. Start the Laravel development server:
    php artisan serve

11. Open the system in a browser:
    http://127.0.0.1:8000

Usernames and Passwords
Admin:
- Email: admin@spes.com
- Password: password123

PESO Admin:
- Email: peso1@spes.com
- Password: password123

PESO User:
- Email: peso2@spes.com
- Password: password123

Employer:
- Email: employer1@spes.com
- Password: password123

Additional accounts may exist in the imported database. If a login fails, reset the password from the Admin account or run the appropriate Laravel seeder/reset script before demonstration.

Important Notes
- The included database file is Database/spes_management.sql.
- The frontend production build is included in public/build.
- Do not delete the public/images folder because it contains system images.
- The .env file may contain local machine settings and should be reviewed before running on another computer.
- For CD checking, use the included ReadMe.txt, database SQL file, and Proposed-System folder.
