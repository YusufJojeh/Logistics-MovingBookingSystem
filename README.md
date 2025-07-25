# Logistics & Moving Booking System (منصة حجز خدمات النقل والنقل اللوجستي متعدد المزودين)

A full-stack, multi-provider logistics and moving booking platform built with procedural PHP, MySQLi, Bootstrap 5, and JavaScript.

## Features
- Animated marketing landing page (SVGs, counters, testimonials, CTAs)
- Multi-provider: companies & individuals can list services
- Clients can browse, compare, and book logistics/moving services
- Booking/order management, communication, reviews
- Multi-language: Arabic & English (RTL support)
- Responsive, modern UI/UX (Bootstrap 5+)
- Secure authentication, profile management
- Admin dashboard for platform management

## Tech Stack
- PHP (procedural, no OOP/frameworks)
- MySQL (MySQLi procedural)
- HTML5, CSS3, Bootstrap 5, JavaScript

## Setup Instructions
1. Import `sample_data.sql` into your MySQL database.
2. Copy the project files to your web server (e.g., XAMPP `htdocs`).
3. Update `/config/config.php` with your DB credentials.
4. Access `index.php` in your browser.

## File Structure
- `/config/` – Configuration, DB, language
- `/assets/` – CSS, JS, images, SVGs
- `/includes/` – Header, footer, nav, auth, functions
- `/pages/` – Dashboards, services, booking, profile, reviews, contact
- `/public/` – Entry points: index, register, login, logout

---

All code is procedural PHP, well-commented, and organized for easy deployment and extension. 