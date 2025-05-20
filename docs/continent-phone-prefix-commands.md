# Continent Phone Prefix Commands

This document describes the console commands for managing continent phone prefixes in the database.

## Import Continent Phone Prefixes

The `app:import-continent-phone-prefix` command imports continent phone prefixes from a CSV file into the database.

### Usage

```bash
php bin/console app:import-continent-phone-prefix path/to/your/file.csv
```

### CSV File Format

The CSV file should be tab-separated and have the following format:

```
Country	Continent	Phone
Andorra	EU	376
United Arab Emirates	AS	971
Afghanistan	AS	93
...
```

The command will:
- Read the CSV file (tab-separated)
- Process the data in batches of 20 rows
- Insert or update the data in the continent_phone_prefix table

### Implementation Details

The command:
1. Accepts a CSV file path as an argument
2. Validates that the file exists and can be opened
3. Reads the CSV file line by line, skipping the header row
4. For each row, extracts the continent code and phone prefix
5. Creates or updates a ContinentPhonePrefix entity
6. Processes the data in batches of 20 rows to optimize database operations
7. Provides progress feedback using SymfonyStyle

## List Continent Phone Prefixes

The `app:list-continent-phone-prefix` command lists all continent phone prefixes in the database.

### Usage

```bash
php bin/console app:list-continent-phone-prefix
```

### Implementation Details

The command:
1. Retrieves all ContinentPhonePrefix entities from the database
2. Displays a table with phone prefixes and continent codes
3. Shows the total count of continent phone prefixes

## Entity Structure

The `ContinentPhonePrefix` entity has two fields:
- `phone_prefix` (string, primary key): The phone prefix of the country
- `continent_code` (string, 2 characters): The continent code (e.g., EU, AS, AF, etc.)

## Example

Here's an example of importing continent phone prefixes from a CSV file and then listing them:

```bash
# Import continent phone prefixes from a CSV file
php bin/console app:import-continent-phone-prefix var/test_continent_phone_prefix.csv

# List all continent phone prefixes
php bin/console app:list-continent-phone-prefix
```