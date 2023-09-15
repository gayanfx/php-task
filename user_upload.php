<?php

class UserUploader {
    private $dbUser;
    private $dbPass;
    private $dbHost;
    private $dbName;
    private $pdo;
    private $options;

    // Constructor to initialize database credentials and connection if necessary
    public function __construct($options) {
        $this->options = $options;

        // Check if help is set in options to display help message
        if (isset($options['help'])) {
            $this->displayHelpMessage();
            exit();
        }

        // Only establish DB connection if it's not a help or dry_run command
        if (!isset($options['dry_run']) && !isset($options['create_table'])) {
            $this->initializeDBCredentials($options);
            $this->establishConnection();
        }

        // Start the process based on the options provided
        $this->run();
    }

    // Method to initialize database credentials
    private function initializeDBCredentials($options) {
        $this->dbUser = $options['u'];
        $this->dbPass = $options['p'];
        $this->dbHost = $options['h'];
        $this->dbName = $options['d'];
    }

    // Method to establish a connection to the database
    private function establishConnection() {
        try {
            $this->pdo = new PDO("mysql:host={$this->dbHost};dbname={$this->dbName}", $this->dbUser, $this->dbPass);
            $this->pdo->setAttribute(PDO::ATTR_ERRMODE, PDO::ERRMODE_EXCEPTION);
        } catch (PDOException $e) {
            echo 'Connection failed: ' . $e->getMessage();
            exit();
        }
    }

    // Method to create table in the database
    public function createTable() {
        $this->initializeDBCredentials($this->options);
        $this->establishConnection();

        try {
            $this->pdo->exec("CREATE TABLE IF NOT EXISTS users (
                                id INT AUTO_INCREMENT PRIMARY KEY,
                                name VARCHAR(255) NOT NULL,
                                surname VARCHAR(255) NOT NULL,
                                email VARCHAR(255) NOT NULL UNIQUE
                              )");
            echo "Table 'users' created successfully.\n";
        } catch (PDOException $e) {
            echo "Error: " . $e->getMessage();
            exit();
        }
    }

    // Method to validate email format
    private function validateEmail($email) {
        $email = trim($email); // Trimming whitespace and other characters from both sides of the email string
        return filter_var($email, FILTER_VALIDATE_EMAIL);
    }

    // Method to process the CSV file and handle database operations
    private function processFile($filePath, $dryRun) {
        // Check if the file has .csv extension
        if (pathinfo($filePath, PATHINFO_EXTENSION) !== 'csv') {
            die("Error: The file should have a .csv extension. Type --help for more details.\n");
        }

        // Check if the file mime type is text/csv
        $finfo = finfo_open(FILEINFO_MIME_TYPE);
        $mimeType = finfo_file($finfo, $filePath);
        finfo_close($finfo);

        if ($mimeType !== 'text/plain' && $mimeType !== 'text/csv') {
            die("Error: The file should be a valid CSV file with mime type 'text/csv' or 'text/plain'. Type --help for more details.\n");
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

            // Trim the email before validating
            $data[2] = trim($data[2]);

            // Check for valid email
            if (!$this->validateEmail($data[2])) {
                echo "Error: Invalid email format at row $rowCount. Skipping this row.\n";
                continue;
            }

            // If --dry_run option is not set, insert data into database
            if (!$dryRun) {
                try {
                    $stmt = $this->pdo->prepare("INSERT INTO users (name, surname, email) VALUES (?, ?, ?)");
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
    }

    // Method to display help message
    public function displayHelpMessage() {
        echo "=====================================================================\n";
        echo "                      CSV Upload Directive help                      \n";
        echo "=====================================================================\n";
        echo "--file [csv file name] – this is the name of the CSV to be parsed.\n";
        echo "--create_table – this will cause the MySQL users table to be built (and no further action will be taken).\n";
        echo "--dry_run – this will be used with the --file directive in case we want to run the script but not insert into the DB. All other functions will be executed, but the database won't be altered.\n";
        echo "-u – MySQL username.\n";
        echo "-p – MySQL password.\n";
        echo "-h – MySQL host.\n";
        echo "-d – MySQL database.\n";
        echo "--help – output the above list of directives with details.\n";
        echo "=====================================================================\n";
    }

    // Method to initiate the process
    private function run() {
        // Check if --create_table is set in options to create table and exit
        if (isset($this->options['create_table'])) {
            $this->createTable();
            exit();
        }

        // Check if --file is set in options to process the file
        if (isset($this->options['file'])) {
            $this->processFile($this->options['file'], isset($this->options['dry_run']));
            exit();
        }

        // If --file option is not set, display an error message
        if (!isset($this->options['file'])) {
            echo "Error: The --file option is not set. Please specify a CSV file to process. Type --help for more details.\n";
            exit();
        }

        echo "Error: Unrecognized option. Type --help for more details.\n";
        exit();
    }
}

// Get the options from the command line arguments
$options = getopt('', ['file::', 'create_table', 'dry_run', 'u::', 'p::', 'h::', 'd::', 'help']);

// Create an instance of UserUploader and pass the options
new UserUploader($options);
?>
