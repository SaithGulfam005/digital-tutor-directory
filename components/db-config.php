<?php
/**
 * Database Configuration — Digital Tutor Directory
 *
 * Default local XAMPP settings:
 *   host : localhost
 *   port : 3306
 *   user : root
 *   pass : empty string
 *
 * You can override these values in the environment for hosted setups.
 */
return [
    'host'    => getenv('DTD_DB_HOST') ?: 'localhost',
    'port'    => getenv('DTD_DB_PORT') ?: '3306',
    'name'    => getenv('DTD_DB_NAME') ?: 'digital_tutor_directory',
    'user'    => getenv('DTD_DB_USER') ?: 'root',
    'pass'    => getenv('DTD_DB_PASS') ?: '',
    'charset' => 'utf8mb4',
];