# DTMS-V2 (Document Tracking Management System)

A Laravel-based Document Tracking Management System for handling document reviews, verifications, and departmental workflows.

## Installation & Setup

### Prerequisites
- PHP >= 8.1
- Composer
- Node.js & npm
- MySQL/MariaDB
- XAMPP/WAMP/MAMP (for local development)

### Clone & Setup Instructions

1. **Clone the repository**
   ```bash
   git clone https://github.com/Ryo2702/DTMS-V2.git
   cd DTMS-V2
   ```

2. **Install PHP dependencies**
   ```bash
   composer install
   ```

3. **Install Node.js dependencies**
   ```bash
   npm install
   ```

4. **Environment Configuration**
   ```bash
   # Copy the environment file
   cp .env.example .env
   
   # Generate application key
   php artisan key:generate
   ```

5. **Database Setup**
   - Create a new database in your MySQL server (e.g., `database management system`)
   - Update your `.env` file with database credentials:
     ```env
     DB_CONNECTION=mysql
     DB_HOST=127.0.0.1
     DB_PORT=3306
     DB_DATABASE=dtms_v2
     DB_USERNAME=your_username
     DB_PASSWORD=your_password
     ```

6. **Run Database Migrations & Seeders**
   ```bash
   # Run migrations
   php artisan migrate
   
   # Run seeders (for roles and initial data)
   php artisan db:seed
   ```

7. **Build Frontend Assets**
   ```bash
   # For development
   npm run dev
   
   # For production
   npm run build
   ```

8. **Storage Setup**
   ```bash
   # Create symbolic link for storage
   php artisan storage:link
   
   # Set proper permissions (if on Linux/Mac)
   chmod -R 775 storage
   chmod -R 775 bootstrap/cache
   ```

9. **Start the Development Server**
   ```bash
   php artisan serve
   ```


## Features
- Document Review Management
- Document Verification System
- Department-based User Management
- Role-based Permissions (using Spatie Laravel Permission)
- File Download Tracking
- Document Status Tracking (Pending, Approved, Rejected, Canceled)

## Technology Stack
- **Backend**: Laravel 11
- **Frontend**: Blade Templates with Tailwind CSS
- **Database**: MySQL
- **Authentication**: Laravel Sanctum
- **Permissions**: Spatie Laravel Permission
- **Build Tool**: Vite

## Project Structure
- `app/Models/`: Core models (User, Department, DocumentReview, DocumentVerification)
- `app/Http/Controllers/`: Application controllers
- `app/Policies/`: Authorization policies
- `app/Events/`: Event classes for document workflow
- `database/migrations/`: Database schema migrations
- `resources/views/`: Blade templates
- `routes/`: Application routes (web, admin)

## Default Login Credentials
After running the seeders, you can log in with:
- Check the `DatabaseSeeder.php` for default admin credentials

## Contributing
1. Fork the repository
2. Create a feature branch
3. Make your changes
4. Submit a pull request

