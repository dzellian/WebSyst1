<?php
$directories = [
    'assets/uploads/profiles',
    'assets/uploads/signatures'
];

echo "<h2>Upload Directory Check</h2>";

foreach ($directories as $dir) {
    echo "<h3>$dir</h3>";
    
    if (is_dir($dir)) {
        echo "✓ Directory exists<br>";
        echo "Writable: " . (is_writable($dir) ? "✓ Yes" : "✗ No") . "<br>";
        echo "Permissions: " . substr(sprintf('%o', fileperms($dir)), -4) . "<br>";
        
        // List files
        $files = scandir($dir);
        echo "Files: " . count($files) . "<br>";
        if (count($files) > 2) {
            echo "<ul>";
            foreach ($files as $file) {
                if ($file != '.' && $file != '..') {
                    $filepath = $dir . '/' . $file;
                    echo "<li>$file - " . filesize($filepath) . " bytes";
                    echo " - <a href='$filepath' target='_blank'>View</a></li>";
                }
            }
            echo "</ul>";
        }
    } else {
        echo "✗ Directory does not exist<br>";
        echo "Attempting to create...<br>";
        if (mkdir($dir, 0777, true)) {
            echo "✓ Directory created successfully<br>";
        } else {
            echo "✗ Failed to create directory<br>";
        }
    }
    echo "<hr>";
}
?>