# Kipepeo

Kipepeo is a management system for Kipepeo Safe Space, built to track the people it serves and the resources it lends: students, volunteers, attendance, and a lending library.

## Features

### Students
- Student roster with detail pages covering grade history, guardians, and school enrollment.
- Promote or graduate students between grades.
- Attendance and book-rental history shown directly on each student's profile.

### Volunteers
- Volunteer roster with check-in/check-out and activity logging.
- Per-volunteer detail page: attendance history, logged activities, and an estimated earnings summary (KSH) based on an hourly rate and date range.

### Attendance
- Daily check-in/check-out for both students and volunteers, with running totals.
- Quick check-in flows and a data-entry mode for backfilling records.

### Library (Books)
- Book catalog with per-title copy counts.
- Rent and return books to students, with a rental history and status (borrowed/overdue/returned) on both the book and student.

### Reporting
- Attendance reports (daily breakdowns, hours by student, per-student logs) over a date range.
- Volunteer activity and stipend-estimate reports.

### Admin
- User management: create users with an admin-set password, edit roles/job titles, and reset a user's password (forces a password change on their next login — the app runs offline, so there's no email-based invite or reset-link flow).
- Settings for schools, grades, activity types, and job titles.
- Bulk import for students and books.

## Tech stack

- [Laravel 10](https://laravel.com/) with [Livewire 3](https://livewire.laravel.com/) and [Jetstream](https://jetstream.laravel.com/) (Fortify auth, two-factor auth)
- [Tailwind CSS](https://tailwindcss.com/) + [Flowbite](https://flowbite.com/) components, bundled with [Vite](https://vitejs.dev/)
- MySQL, via [Laravel Sail](https://laravel.com/docs/sail) (Docker) for local development
- [Pest](https://pestphp.com/) for testing

## Getting started

This project is set up to run via Sail (Docker):

1. Clone the repository and install PHP dependencies:
   ```bash
   composer install
   ```
2. Copy the environment file and generate an app key:
   ```bash
   cp .env.example .env
   ./vendor/bin/sail up -d
   ./vendor/bin/sail artisan key:generate
   ```
3. Run migrations (and seeders, if you want sample data):
   ```bash
   ./vendor/bin/sail artisan migrate --seed
   ```
4. Install frontend dependencies and build assets:
   ```bash
   ./vendor/bin/sail npm install
   ./vendor/bin/sail npm run dev
   ```
5. Visit the app at the URL configured in `APP_URL` (defaults to `http://localhost`).

## Running tests

```bash
./vendor/bin/sail artisan test
```
