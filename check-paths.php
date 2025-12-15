<?php
// check-paths.php
echo "<h2>Checking File Paths</h2>";

$files_to_check = [
    'gallery.php' => __DIR__ . '/gallery.php',
    'admin-gallery.php' => __DIR__ . '/admin/admin-gallery.php',
    'diagnose.php' => __DIR__ . '/diagnose.php',
    'db-connection.php' => __DIR__ . '/admin/db-connection.php',
];

echo "<table border='1' cellpadding='10'>";
echo "<tr><th>File</th><th>Path</th><th>Exists</th></tr>";

foreach ($files_to_check as $name => $path) {
    $exists = file_exists($path) ? '✅ Yes' : '❌ No';
    echo "<tr>";
    echo "<td>$name</td>";
    echo "<td>$path</td>";
    echo "<td>$exists</td>";
    echo "</tr>";
}

echo "</table>";

// Check if we can access via URL
echo "<h3>Test URLs:</h3>";
echo "<ul>";
echo "<li><a href='gallery.php' target='_blank'>gallery.php</a></li>";
echo "<li><a href='admin/admin-gallery.php' target='_blank'>admin/admin-gallery.php</a></li>";
echo "<li><a href='diagnose.php' target='_blank'>diagnose.php</a></li>";
echo "</ul>";
?>