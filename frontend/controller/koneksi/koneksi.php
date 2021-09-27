<?php
// Require Composer's autoloader.
require '../../../vendor/autoload.php';

// Using Medoo namespace.
use Medoo\Medoo;

$koneksi = new Medoo([
    'type' => 'mysql',
    'host' => 'localhost',
    'database' => 'absensi',
    'username' => 'root',
    'password' => 'root'
]);
?>