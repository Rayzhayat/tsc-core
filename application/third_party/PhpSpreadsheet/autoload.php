<?php
/**
 * PhpSpreadsheet Autoloader
 */

// Define base path
define('PHPSPREADSHEET_ROOT', __DIR__);

// Register autoloader
spl_autoload_register(function ($class) {
    $prefix = 'PhpOffice\\PhpSpreadsheet\\';
    $base_dir = PHPSPREADSHEET_ROOT . '/src/PhpSpreadsheet/';

    $len = strlen($prefix);
    if (strncmp($prefix, $class, $len) !== 0) {
        return;
    }

    $relative_class = substr($class, $len);
    $file = $base_dir . str_replace('\\', '/', $relative_class) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

// Load dependencies if vendor/autoload exists
if (file_exists(PHPSPREADSHEET_ROOT . '/vendor/autoload.php')) {
    require PHPSPREADSHEET_ROOT . '/vendor/autoload.php';
}