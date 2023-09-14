# User Data Upload Script
This project includes a PHP script to process a CSV file containing user data and insert the data into a MySQL database. The script offers several command-line directives for different functionalities like creating a table, dry run, and more.

## Requirements
- PHP 8.1.x
- MySQL 5.7 or higher (or MariaDB 10.x)

## Installation
1. Clone the repository.
2. Get in to the repository (cd repository).
3. Copy or upload your CSV file in to the directory (root)
4. Execute commands..
  
## Usage

To run the script, you can use the following command-line options:

### Database credentials
--dry_run: When used with the --file directive, this option runs the script without inserting data into the database. All other functions will be executed, but the database won't be altered.
-u [MySQL Username]: Provide the MySQL username.
-p [MySQL Password]: Provide the MySQL password.
-h [MySQL Host]: Specify the MySQL host.
-d [MySQL Database Name]: Specify the MySQL database name.
Eg
```shell
php user_upload.php -u yourUsername -p yourPassword -h yourHost -d databaseName
```
or
```shell
php user_upload.php --u=yourUsername --p=yourPassword --h=yourHost --d=databaseName
```

## Execution

### Help
To view this list of directives with details, simply use the --help option.
```shell
php user_upload.php --help
```

### Creating the 'users' Table
If you use the --create_table directive, the script will create the 'users' table in the specified database. This step is essential before inserting data into the database.
```shell
php user_upload.php -u yourUsername -p yourPassword -h yourHost -d databaseName --create_table
```

### Dry Run Mode
When the --dry_run option is used with the --file directive, the script will process the CSV data but won't insert it into the database. Instead, it will display information about the processed rows.
```shell
php user_upload.php -u yourUsername -p yourPassword -h yourHost -d databaseName --file filename.csv --dry_run
```

### Parsing a CSV File
When you provide the --file directive along with the CSV file name, the script will:
Check if the specified file has a '.csv' extension. If not, it will display an error message and exit.
Attempt to open the specified file for reading. If it fails, an error message will be displayed, suggesting you check if the file exists and has the correct permissions.
Read the header row from the CSV file.
Loop through each row in the CSV file, validating the data (checking for missing data and a valid email format). If the data is invalid, it will display an error message and skip to the next row.
If the --dry_run option is not set, it will insert the valid data into the 'users' table in the database.
If a duplicate email address is detected, or any other database error occurs, it will handle the error separately and continue processing.
```shell
php user_upload.php -u yourUsername -p yourPassword -h yourHost -d databaseName --file users.csv
```