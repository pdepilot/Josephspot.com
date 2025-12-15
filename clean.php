<?php
// cleanup.php - Run once to delete test files

$files_to_delete = [
    'test-save.php',
    'test-form.php',
    'test-contact.php',
    'test-simple.php',
    'test-db.php',
    'debug-save.php',
    'insert-test.php',
    'check-db.php',
    'check-messages.php',
    'save-contact-simple.php',
    'cleanup.php' // This file will delete itself
];

echo "<h2>Cleaning up test files...</h2>";

foreach ($files_to_delete as $file) {
    if (file_exists($file)) {
        if (unlink($file)) {
            echo "<p style='color: green;'>Deleted: $file</p>";
        } else {
            echo "<p style='color: red;'>Failed to delete: $file</p>";
        }
    } else {
        echo "<p>File not found: $file</p>";
    }
}

echo "<h3>Cleanup complete!</h3>";
echo "<p>Essential files kept:</p>";
echo "<ul>
    <li>contact.php</li>
    <li>save-contact.php</li>
    <li>admin-login.php</li>
    <li>admin-contact-messages.php</li>
    <li>admin-logout.php</li>
</ul>";
?>