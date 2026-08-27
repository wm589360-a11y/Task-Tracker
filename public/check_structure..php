<?php
echo "<h1>🔍 File Structure Check</h1>";
echo "<hr>";

$basePath = __DIR__ . '/../';

// Check folders
$folders = [
    'src/Controllers' => 'Controllers folder',
    'src/Models' => 'Models folder',
    'src/helpers' => 'Helpers folder',
    'config' => 'Config folder',
    'templates' => 'Templates folder',
];

echo "<h2>📁 Folder Check:</h2>";
foreach ($folders as $folder => $label) {
    $path = $basePath . $folder;
    if (is_dir($path)) {
        echo "✅ $label exists: $path<br>";
    } else {
        echo "❌ $label MISSING: $path<br>";
    }
}

echo "<hr>";

// Check files
$files = [
    'src/Controllers/AuthController.php' => 'AuthController',
    'src/Controllers/TaskController.php' => 'TaskController',
    'src/Models/User.php' => 'User Model',
    'src/Models/Category.php' => 'Category Model',
    'src/Models/Task.php' => 'Task Model',
    'src/helpers/SessionHelper.php' => 'Session Helper',
    'config/database.php' => 'Database Config',
];

echo "<h2>📄 File Check:</h2>";
foreach ($files as $file => $label) {
    $path = $basePath . $file;
    if (file_exists($path)) {
        echo "✅ $label exists: $path<br>";
    } else {
        echo "❌ $label MISSING: $path<br>";
    }
}

echo "<hr>";

// List all directories and files in src
echo "<h2>📂 Contents of src/ folder:</h2>";
$srcPath = $basePath . 'src';
if (is_dir($srcPath)) {
    $items = scandir($srcPath);
    echo "<ul>";
    foreach ($items as $item) {
        if ($item != '.' && $item != '..') {
            $fullPath = $srcPath . '/' . $item;
            if (is_dir($fullPath)) {
                echo "<li>📁 <strong>$item/</strong></li>";
                // List contents of subdirectories
                $subItems = scandir($fullPath);
                echo "<ul>";
                foreach ($subItems as $subItem) {
                    if ($subItem != '.' && $subItem != '..') {
                        echo "<li>📄 $subItem</li>";
                    }
                }
                echo "</ul>";
            } else {
                echo "<li>📄 $item</li>";
            }
        }
    }
    echo "</ul>";
} else {
    echo "❌ src folder not found!";
}
?>