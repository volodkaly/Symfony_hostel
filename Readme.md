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
    php bin/console doctrine:migrations:migrate
    ```

---

### 📦 Seeding Data

I created a few console commands to mock the database quickly. Run them in this specific order to maintain relations:

1.  `php bin/console addRooms` — creates rooms with random names, capacities and prices.
2.  `php bin/console addUser` — creates 100 regular users with random names and emails.
3.  `php bin/console addBooking` — makes 100 random bookings with random dates for random rooms present in the DB.
4.  `php bin/console addReviews` — adds 100 reviews with random rating marks 1-5 to those bookings.

---

### 📝 Notes

- **Price Calculation:** There is a JS script in the booking form that automatically updates the total price when you change dates.
- **Admin Panel:** You can access it at `/admin`. It handles all the CRUD operations and allows you to toggle payment statuses.
- **Logs:** Custom logs are written when a new booking is created.

.
