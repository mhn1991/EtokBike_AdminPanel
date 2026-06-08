# EtokBike Admin

Laravel and Filament admin panel for managing EtokBike mobile app content, shop catalogue, orders, services, programs, customer profiles, and mobile analytics.

## Local Setup

```bash
composer install
npm install
php artisan migrate --seed
npm run build
php artisan serve --host=127.0.0.1 --port=8001
```

The seeded admin account is:

```text
Email: admin@example.com
Password: password
```

## Useful Commands

```bash
php artisan test
php artisan test tests/Feature/FilamentAdminPagesSmokeTest.php
npm run build
```

## Admin Areas

- Inbox: customer messages and departments.
- Orders: order fulfilment and delivery methods.
- Catalog: products and product categories shown in the mobile shop.
- Services: bookings, offerings, categories, and service time slots.
- Programs: events, bookings, and program categories.
- Mobile App Content: server-driven app pages and telemetry logs.
- Customers: customer and bike profiles.
- Settings: store profile and operational details.
