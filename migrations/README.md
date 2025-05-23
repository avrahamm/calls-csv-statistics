# Clean Migration Files for Database Setup

This directory contains clean migration files for setting up the database structure from scratch. Each migration file creates a single table in the database.

## Migration Files

1. `Version20250523101641.php` - Creates the `calls` table
2. `Version20250523101714.php` - Creates the `calls_staging` table
3. `Version20250523101747.php` - Creates the `continent_phone_prefix` table
4. `Version20250523101815.php` - Creates the `customer_call_statistics` table
5. `Version20250523101846.php` - Creates the `ip_geolocation_cache` table
6. `Version20250523101916.php` - Creates the `messenger_messages` table
7. `Version20250523101948.php` - Creates the `uploaded_files` table
8. `Version20250523102019.php` - Creates the `doctrine_migration_versions` table

## Running the Migrations

To run all migrations and set up the database structure from scratch, use the following command:

```bash
bin/console doctrine:migrations:migrate
```

This will execute all migration files in order, creating all the necessary tables in the database.

### Table Existence Check

All migration files include the `IF NOT EXISTS` clause in their CREATE TABLE statements. This ensures that tables are only created if they don't already exist in the database. This is useful when running migrations on a database that might already have some of the tables created.

## Individual Migration Execution

If you want to run a specific migration, you can use the following command:

```bash
bin/console doctrine:migrations:execute --up 'DoctrineMigrations\Version20250523101641'
```

Replace `Version20250523101641` with the version number of the migration you want to execute.

## Reverting Migrations

To revert a specific migration, you can use the following command:

```bash
bin/console doctrine:migrations:execute --down 'DoctrineMigrations\Version20250523101641'
```

Replace `Version20250523101641` with the version number of the migration you want to revert.

## Migration Status

To check the status of all migrations, use the following command:

```bash
bin/console doctrine:migrations:status
```

This will show you which migrations have been executed and which are still pending.
