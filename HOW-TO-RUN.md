# How to Run — Fee Collection & Management (School/College)

Assumes prerequisites in `PREREQUISITES.md` are already done.

## One-time setup

```
composer install
mysql -u root -P 3306 < database/schema.sql
mkdir public\uploads\roster_imports
```
(or import `database/schema.sql` via phpMyAdmin instead of the `mysql` command)

To wipe the seeded data and start over, drop the database first, then re-run the import above (re-running it directly without dropping first fails with "table already exists"):
```
mysql -u root -P 3306 -e "DROP DATABASE IF EXISTS fee_collection_management;"
```

## Start MySQL

Open **XAMPP Control Panel** and make sure **MySQL** is running.

## Run the app

From this project's folder:
```
php -S localhost:8007 -t public public/index.php
```
(`-t public` is required — without it, CSS/JS assets 404 while the HTML still renders.)

## Open it

Visit http://localhost:8007/ and log in with any of the demo credentials in `README.md` under "Default Login Credentials".

## Stop the app

`Ctrl+C` in the terminal running the `php -S` command.
