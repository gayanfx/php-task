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