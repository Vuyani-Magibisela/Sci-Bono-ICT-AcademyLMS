<?php
/**
 * Database Configuration
 * 
 * This file handles loading database configuration and establishing the connection.
 */

// Load the main configuration file if not already included
if (!defined('DB_HOST')) {
    require_once __DIR__ . '/config.php';
}

/**
 * Get a new database connection instance
 * 
 * @return Core\Database The database instance
 */
function db() {
    return \Core\Database::getInstance();
}

/**
 * Execute a database query with parameters
 * 
 * Helper function for more convenient database querying
 * 
 * @param string $sql The SQL query
 * @param array $params The parameters for the query
 * @return \Core\Database The database instance for method chaining
 */
function db_query($sql, $params = []) {
    return db()->query($sql, $params);
}

/**
 * Fetch a single record from the database
 * 
 * @param string $sql The SQL query
 * @param array $params The parameters for the query
 * @return array|false The record or false if not found
 */
function db_fetch($sql, $params = []) {
    return db()->query($sql, $params)->fetch();
}

/**
 * Fetch all records from the database
 * 
 * @param string $sql The SQL query
 * @param array $params The parameters for the query
 * @return array The records
 */
function db_fetch_all($sql, $params = []) {
    return db()->query($sql, $params)->fetchAll();
}

/**
 * Insert a record into the database
 * 
 * @param string $table The table name
 * @param array $data The data to insert
 * @return int|string|false The last insert ID or false on failure
 */
function db_insert($table, $data) {
    $fields = array_keys($data);
    $placeholders = array_map(function($field) {
        return ":$field";
    }, $fields);
    
    $sql = "INSERT INTO $table (" . implode(', ', $fields) . ") VALUES (" . implode(', ', $placeholders) . ")";
    
    try {
        $db = db();
        $db->query($sql, $data);
        return $db->lastInsertId(); // This returns a string
    } catch (\PDOException $e) {
        return false;
    }
}

/**
 * Update a record in the database
 * 
 * @param string $table The table name
 * @param array $data The data to update
 * @param string $where The WHERE clause
 * @param array $params Additional parameters for the WHERE clause
 * @return bool True on success, false on failure
 */
function db_update($table, $data, $where, $params = []) {
    $sets = array_map(function($field) {
        return "$field = :$field";
    }, array_keys($data));
    
    $sql = "UPDATE $table SET " . implode(', ', $sets) . " WHERE $where";
    
    // Merge data and params
    $params = array_merge($data, $params);
    
    try {
        return db()->query($sql, $params)->rowCount() > 0;
    } catch (\PDOException $e) {
        return false;
    }
}

/**
 * Delete a record from the database
 * 
 * @param string $table The table name
 * @param string $where The WHERE clause
 * @param array $params Parameters for the WHERE clause
 * @return bool True on success, false on failure
 */
function db_delete($table, $where, $params = []) {
    $sql = "DELETE FROM $table WHERE $where";
    
    try {
        return db()->query($sql, $params)->rowCount() > 0;
    } catch (\PDOException $e) {
        return false;
    }
}