<?php

// Start a loop from 1 to 100
for ($i = 1; $i <= 100; $i++) {
    
    // Check if the current number is divisible by both 3 and 5
    if ($i % 3 == 0 && $i % 5 == 0) {
        // If yes, print "foobar"
        echo "foobar";
    } 
    // If the above condition fails, check if the number is divisible by 3
    else if ($i % 3 == 0) {
        // If yes, print "foo"
        echo "foo";
    } 
    // If the above two conditions fail, check if the number is divisible by 5
    else if ($i % 5 == 0) {
        // If yes, print "bar"
        echo "bar";
    } 
    // If none of the above conditions are met, simply print the number itself
    else {
        echo $i;
    }

    // If the current number is less than 100, print a comma to separate it from the next output
    if ($i < 100) {
        echo ", ";
    }
}

// Print a newline character at the end of the loop to move the cursor to the next line in the console
echo "\n";
?>
