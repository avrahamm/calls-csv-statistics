# Resetting Migration History in Symfony

This document explains how to reset the migration history in a Symfony project to create a minimal migration that represents the current state of the database.

## Background

When working with Symfony's Doctrine migrations, you may end up with many migration files that represent the incremental changes made to the database schema over time. In some cases, you might want to consolidate these migrations into a single migration that represents the current state of the database.

## Steps to Reset Migration History

1. **Backup existing migrations**
   ```bash
   mkdir -p migrations_backup && cp migrations/*.php migrations_backup/
   ```

2. **Delete existing migration files**
   ```bash
   rm migrations/Version*.php
   ```

3. **Generate a new empty migration**
   ```bash
   bin/console doctrine:migrations:generate
   ```

4. **Extract the current database schema**
   You can use MySQL commands to extract the CREATE TABLE statements for each table in your database:
   ```bash
   docker exec -it database-container mysql -u root -ppassword -e "SHOW CREATE TABLE database.table_name\G"
   ```

5. **Modify the migration file**
   Edit the generated migration file to include the CREATE TABLE statements in the `up()` method and DROP TABLE statements in the `down()` method.

   If the tables already exist in the database, you can create a placeholder migration that doesn't actually create any tables:
   ```php
   public function up(Schema $schema): void
   {
       // This migration is just a placeholder to mark the current schema state
       // All tables already exist in the database, so we don't need to create them again
       $this->addSql('SELECT 1');
       
       // The actual schema includes the following tables:
       // - table1
       // - table2
       // - etc.
   }
   ```

6. **Clear the migration table**
   ```bash
   docker exec -it database-container mysql -u root -ppassword -e "TRUNCATE TABLE database.doctrine_migration_versions;"
   ```

7. **Execute the new migration**
   ```bash
   bin/console doctrine:migrations:execute 'DoctrineMigrations\VersionXXXXXXXXXXXXXX' --up
   ```

8. **Verify the migration status**
   ```bash
   bin/console doctrine:migrations:status
   ```

## Alternative Approaches

### Using doctrine:migrations:dump-schema

If your database doesn't use MySQL-specific types like ENUM, you can use the `doctrine:migrations:dump-schema` command to create a migration that represents the current schema:

```bash
bin/console doctrine:migrations:dump-schema
```

### Using doctrine:migrations:rollup

After dumping the schema, you can use the `doctrine:migrations:rollup` command to delete all tracked versions and insert only the new one:

```bash
bin/console doctrine:migrations:rollup
```

## Conclusion

By following these steps, you can reset your migration history to have a single migration that represents the current state of your database. This can be useful for simplifying your migration history and making it easier to manage.