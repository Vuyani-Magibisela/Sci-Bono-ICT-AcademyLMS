<?php
// public/index.php

// Define root path - very important this is correct
define('ROOT_PATH', dirname(__DIR__));

// Load bootstrap file
require_once ROOT_PATH . '/app/bootstrap.php';

// Initialize the application
$app = new \Core\Application();

// Run the application
$app->run();