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
