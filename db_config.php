<?php
/**
 * GB Laser Soldering — Database Configuration
 * 
 * INSTRUCTIONS: Fill in your Hostinger MySQL database credentials below.
 * In Hostinger: go to Hosting → your site → Databases → MySQL Databases
 * Create a database and user there, then paste the credentials here.
 */

define('DB_HOST',     'localhost');       // Usually 'localhost' on Hostinger
define('DB_NAME',     'your_db_name');   // Your Hostinger MySQL database name
define('DB_USER',     'your_db_user');   // Your Hostinger MySQL username
define('DB_PASS',     'your_db_pass');   // Your Hostinger MySQL password
define('DB_CHARSET',  'utf8mb4');

function getDB(): PDO {
    static $pdo = null;
    if ($pdo === null) {
        $dsn = 'mysql:host=' . DB_HOST . ';dbname=' . DB_NAME . ';charset=' . DB_CHARSET;
        $options = [
            PDO::ATTR_ERRMODE            => PDO::ERRMODE_EXCEPTION,
            PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
            PDO::ATTR_EMULATE_PREPARES   => false,
        ];
        try {
            $pdo = new PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (PDOException $e) {
            http_response_code(500);
            die(json_encode(['success' => false, 'error' => 'Database connection failed.']));
        }
    }
    return $pdo;
}
