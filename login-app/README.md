# Login App - Farmacos del Norte SAC

## Overview
This project is a web application designed for managing user authentication and access control for the Farmacos del Norte SAC system. It allows users to log in, register, and access their respective dashboards based on their roles (admin or vendor).

## Features
- User registration and login functionality.
- Role-based access control for admin and vendor users.
- Dashboard views tailored to user roles.
- Middleware for redirecting authenticated users.
- Database migrations for user and access control tables.

## Project Structure
```
login-app
├── app
│   ├── Http
│   │   ├── Controllers
│   │   │   ├── AuthController.php
│   │   │   └── DashboardController.php
│   │   └── Middleware
│   │       └── RedirectIfAuthenticated.php
│   ├── Models
│   │   ├── Usuario.php
│   │   └── AccesoWeb.php
├── config
│   └── auth.php
├── database
│   ├── migrations
│   │   ├── 2024_01_01_000000_create_usuarios_table.php
│   │   └── 2024_01_01_000001_create_acceso_web_table.php
│   └── seeders
│       └── DatabaseSeeder.php
├── public
│   └── css
│       └── login.css
├── resources
│   └── views
│       ├── login.blade.php
│       ├── register.blade.php
│       ├── admin.blade.php
│       └── vendedor.blade.php
├── routes
│   └── web.php
└── README.md
```

## Installation
1. Clone the repository:
   ```
   git clone <repository-url>
   ```
2. Navigate to the project directory:
   ```
   cd login-app
   ```
3. Install dependencies:
   ```
   composer install
   ```
4. Set up your `.env` file:
   ```
   cp .env.example .env
   ```
   Update the database configuration in the `.env` file.

5. Run migrations to set up the database:
   ```
   php artisan migrate
   ```

6. Seed the database with initial data (optional):
   ```
   php artisan db:seed
   ```

7. Start the development server:
   ```
   php artisan serve
   ```

## Usage
- Navigate to `http://localhost:8000/login` to access the login page.
- Users can register for an account and log in to access their respective dashboards.
- Admin users will be redirected to the admin dashboard, while vendor users will be redirected to the vendor dashboard.

## Contributing
Contributions are welcome! Please submit a pull request or open an issue for any enhancements or bug fixes.

## License
This project is licensed under the MIT License. See the LICENSE file for details.