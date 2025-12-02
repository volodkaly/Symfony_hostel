# Symfony Hostel

A simple booking system built with **Symfony 6.4** and **MySQL**.
Users can register, book rooms, and leave reviews. Admins manage everything via a dashboard.

### 🛠 Stack

- PHP 8.1+ & Symfony 6.4
- MySQL
- EasyAdmin Bundle
- Twig + Vanilla JS

---

### 🚀 How to run it

Install PHP, Symfony CLI and Composer <br>

https://symfony.com/doc/current/setup/symfony_cli.html <br>
https://www.php.net/downloads.php <br>
https://getcomposer.org/download/ <br>

1.  **Clone the repository:**

    ```bash
    git clone https://github.com/volodkaly/Symfony_hostel.git
    cd Symfony_hostel
    ```

2.  **Install dependencies:**

    ```bash
    composer install
    ```

3.  **Configure the database:**

    - Create a `.env.local` file (or modify `.env`).
    - Fill in your DB connection parameters.

4.  **Setup the database:**
    ```bash
    php bin/console doctrine:database:create
    php bin/console make:migration
    or symfony console doctrine:migrations:generate
    php bin/console doctrine:migrations:migrate
    ```

---

### 📦 Seeding Data

I created a few console commands to mock the database quickly. Run them in this specific order to maintain relations:

```
php bin/console add100Bookings Mocking 100 bookings
php bin/console add100Reviews Mocks 100 reviews
php bin/console add100Users Mocking 100 users (not admins)
php bin/console addRoom Mocking 1 room
php bin/console addAdmin Mocking 1 admin

```

## Without admin u cannot access ~/admin routes for having a control over all enities: room, bookings, reviews, users (customers).

### 📝 Notes

- **Price Calculation:** There is a JS script in the booking form that automatically updates the total price when you change dates.
- **Admin Panel:** You can access it at `/admin`. It handles all the CRUD operations and allows you to toggle payment statuses.
- **Logs:** Custom logs are written when a new booking is created.
- **Flash messages:** Informative msgs ensure attractive UI experience.
