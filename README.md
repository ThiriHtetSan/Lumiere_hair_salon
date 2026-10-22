# Lumière Hair Salon

A responsive hair salon website with an online appointment booking system and an admin management dashboard.

This project demonstrates frontend web development, PHP backend development, MySQL database integration, and basic role-based access control.

## Features

**Customer Website**
- Responsive homepage with elegant salon design
- Services, About Us, and Contact pages
- Online appointment booking form
- Form validation and booking date validation

**Admin System**
- Secure admin login with password hashing
- View customer appointments
- Confirm or cancel pending bookings
- Owner and staff roles
- Create, enable, and disable staff accounts
- CSRF protection for administrative actions

## Technologies Used

- **Frontend:** HTML5, CSS3, JavaScript
- **Backend:** PHP
- **Database:** MySQL
- **Development Environment:** XAMPP
- **Code Editor:** Visual Studio Code

## Project Structure

```text
hair-salon/
├── api/
│   ├── create_booking.php
│   └── update_booking_status.php
├── css/
│   └── style.css
├── images/
├── js/
│   └── script.js
├── index.html
├── about.html
├── services.html
├── contact.html
├── admin_login.php
├── admin_auth.php
├── admin_dashboard.php
├── admin_logout.php
├── admin_management.php
├── create_new_admin.php
└── toggle_admin_status.php
```

## How It Works

Customers can browse salon services and submit appointment requests. Booking information is validated and stored in a MySQL database through a PHP API.

Authorized administrators can log in to the dashboard, review appointment requests, and confirm or cancel pending bookings. The owner can also manage staff accounts.

## Live Demo

GitHub Pages provides a frontend preview of the website.

**Note:** PHP and MySQL functionality, including appointment submission and admin login, requires a PHP-enabled server. These backend features do not run on GitHub Pages.

## Local Setup

1. Install XAMPP and start Apache and MySQL.
2. Place the project folder inside `xampp/htdocs/`.
3. Create a MySQL database named `lumiere_salon`.
4. Create the required database tables and configure `config/database.php` with your local database connection settings.
5. Open `http://localhost/hair-salon/` in your browser.

Database credentials and real customer records are intentionally excluded from the public repository.

## Project Purpose

This project was developed as a web development portfolio project to demonstrate responsive interface design, form handling, database integration, and administrative functionality.
