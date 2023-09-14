<?php
// Command line directives
$options = getopt("u:p:h:d:", ["file:", "create_table", "dry_run", "u:", "p:", "h:", "d:", "help"]);

if (isset($options['help'])) {
    displayHelpMessage();
    exit();
}

if (!isset($options['u']) || !isset($options['p']) || !isset($options['h']) || !isset($options['d'])) {
    die("Please provide u, p, h, and d options. Type --help for more details.\n"); 
}

//Established database connection using PDO
$dbUser = $options['u'];
$dbPass = $options['p'];
$dbHost = $options['h'];
$dbName = $options['d'];

// PDO instance
try {
    $pdo = new PDO("mysql:host=$dbHost;dbname=$dbName", $dbUser, $dbPass);
    $pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
} catch (PDOException $e) {
    echo 'Connection failed: Please double check your Database credential details again.';
    exit();
}

if (isset($options['create_table'])) {
    try {
        $pdo->exec("CREATE TABLE IF NOT EXISTS users (
                        id INT AUTO_INCREMENT PRIMARY KEY,
                        name VARCHAR(255) NOT NULL,
                        surname VARCHAR(255) NOT NULL,
                        email VARCHAR(255) NOT NULL UNIQUE
                    )");
        echo "Table 'users' created successfully.\n";
    } catch (PDOException $e) {
        if ($e->getCode() == "42S02") {
            echo "Please create the table first using the --create_table directive. --help for more details.\n";
        } else {
            echo "Error: " . $e->getMessage();
        }
        exit();
    }
    die();
}

// After creating the table, check and add the unique index
try {
    $result = $pdo->query("SHOW INDEXES FROM users WHERE Key_name = 'unique_email'");
    if ($result->rowCount() == 0) {
        $pdo->exec("ALTER TABLE users ADD UNIQUE INDEX `unique_email` (`email`)");
    }
} catch (PDOException $e) {
    if ($e->getCode() == "42S02") {
        echo "Please create the table first using the --create_table directive. --help for more details.\n";
    } else {
        echo "Error: " . $e->getMessage();
    }
    exit();
}

// Function to validate email
function validateEmail($email) {
    return filter_var($email, FILTER_VALIDATE_EMAIL);
}

// Reading and processing CSV file
if (isset($options['file'])) {
    
    $filePath = $options['file'];
    
    // Check if the file has .csv extension
    if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'csv') {
        die("Error: The file should have a .csv extension. Type --help for more details.\n");
    }

    // Check if the file can be opened
    if (($handle = fopen($filePath, "r")) === FALSE) {
        die("Error: Unable to open the file. Check if the file exists and has the correct permissions.\n");
    }

    $header = fgetcsv($handle);
    $rowCount = 1;

    while (($data = fgetcsv($handle)) !== FALSE) {
        $rowCount++;
        
        // Check for missing data
        if (count($data) < 3 || in_array(null, $data, true)) {
            echo "Error: Missing data at row $rowCount. Skipping this row.\n";
            continue;
        }
        
        // Check for valid email
        if (!validateEmail($data[2])) {
            echo "Error: Invalid email format at row $rowCount. Skipping this row.\n";
            continue;
        }

        // If --dry_run option is not set, insert data into database
        if (!isset($options['dry_run'])) {
            try {
                $stmt = $pdo->prepare("INSERT INTO users (name, surname, email) VALUES (?, ?, ?)");
                $stmt->execute([$data[0], $data[1], $data[2]]);
                echo "Row $rowCount processed successfully.\n";
            } catch (PDOException $e) {
                if ($e->errorInfo[1] == 1062) {
                    echo "Error: Duplicate email address at row $rowCount. Skipping this row.\n";
                } else {
                    echo "Error: Database error at row $rowCount: " . $e->getMessage() . ". Skipping this row.\n";
                }
                continue;
            }
        } else {
            echo "Dry run: Row $rowCount - Name: {$data[0]}, Surname: {$data[1]}, Email: {$data[2]} - processed successfully (Not inserted into database).\n";
        }
    }

    fclose($handle);

    echo "=====================================================================\n";
    echo "              CSV file processed successfully                        \n";
    echo "=====================================================================\n";
}

function displayHelpMessage() {
    echo "=====================================================================\n";
    echo "                      CSV Upload Directive help                      \n";
    echo "=====================================================================\n";
    echo "1) Passing Database credentials.\n";
    echo "-u – MySQL Username\n";
    echo "-p – MySQL Password\n";
    echo "-h – MySQL Host\n";
    echo "-d – MySQL Database Name\n"; // Corrected from '-h' to '-d' for database name
    echo "  Example create table ( php user_upload.php -u yourUsername -p yourPassword -h yourHost -d databaseName )\n";
    echo "-------------------------------------------------------------------\n";
    echo "2) Create a table.\n";
    echo "--create_table – this will cause the MySQL users table to be built (and no further action will be taken)\n";
    echo "  Example create table ( php user_upload.php -u yourUsername -p yourPassword -h yourHost -d databaseName --create_table )\n";
    echo "-------------------------------------------------------------------\n";
    echo "3) Dry run.\n";
    echo "--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB.\n";
    echo " All other functions will be executed, but the database won't be altered\n";
    echo "  Example create table ( php user_upload.php -u yourUsername -p yourPassword -h yourHost -d databaseName --file filename.csv --dry_run )\n";
    echo "-------------------------------------------------------------------\n";
    echo "4) Processs CSV file.\n";
    echo "--file [csv file name] – this is the name of the CSV to be parsed\n";
    echo "  Example create table ( php user_upload.php -u yourUsername -p yourPassword -h yourHost -d databaseName --file filename.csv )\n";
    echo "-------------------------------------------------------------------\n";
    echo "5) Directive help.\n";
    echo "--help – which will output the above list of directives with details\n";
    echo "=====================================================================\n";
}

// Closing the database connection
$pdo = null;

?>