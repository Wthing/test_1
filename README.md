# Patient Appointment Management System (Hyperf PHP 8.3)

A backend application built using **Hyperf Framework**, **PHP 8.3**, and **MySQL** for managing patient appointments.  
The project supports **Swagger documentation**, **database migrations**, and automatic server reloading during development.

---

## Requirements

- PHP **8.3+**
- MySQL **8.0+**
- Composer **2.0+**
- [Swoole Extension](https://www.swoole.co.uk/docs/get-started/installation)

---

## Installation

### 1. Install Swoole
```bash
pecl install swoole
```

### 2. Clone the repository
```bash
git clone https://github.com/Wthing/test_1.git
cd test_1
```

### 3. Install dependencies
```bash
composer install
```

### 4. Create the `.env` file  
Copy `.env-example` and rename it to `.env`, then fill in your database and app configuration:
```bash
cp .env-example .env
```

Example:
```
DB_HOST=127.0.0.1
DB_PORT=3306
DB_DATABASE=test_1
DB_USERNAME=root
DB_PASSWORD=your_password
```

---

## Database Migration

Run all database migrations with:
```bash
php bin/hyperf.php migrate
```

---

## Development Tools

To automatically restart the project when files change:
```bash
composer require hyperf/watcher --dev
```

Then run the watcher:
```bash
php bin/hyperf.php server:watch
```

---

## API Documentation (Swagger)

After starting the server, open the Swagger UI in your browser:
```
http://localhost:9503/swagger/index.html
```

---

## Notes

- All routes are auto-documented using `Hyperf\Swagger`.
- Swagger annotations are located in controller classes (e.g., `AppointmentController`).
- Make sure your database and `.env` are properly configured before running migrations.

---

## Author

**Arsen (Wthing)**  
3D Designer & Full-Stack Developer  
[GitHub Profile](https://github.com/Wthing)
