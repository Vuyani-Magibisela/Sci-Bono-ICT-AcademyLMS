<?php
namespace Core;

/**
 * Database Class
 * 
 * A singleton PDO database connection class.
 * Provides secure methods for querying the database with prepared statements.
 */
class Database
{
    /**
     * @var Database|null The singleton instance
     */
    private static $instance = null;
    
    /**
     * @var \PDO The PDO connection
     */
    private $pdo;
    
    /**
     * @var \PDOStatement|null The last prepared statement
     */
    private $statement;
    
    /**
     * Private constructor to prevent direct instantiation
     * Establishes the database connection
     */
    private function __construct()
    {
        try {
            $dsn = "mysql:host=" . DB_HOST . ";dbname=" . DB_NAME . ";charset=" . DB_CHARSET;
            
            $options = [
                \PDO::ATTR_ERRMODE => \PDO::ERRMODE_EXCEPTION,
                \PDO::ATTR_DEFAULT_FETCH_MODE => \PDO::FETCH_ASSOC,
                \PDO::ATTR_EMULATE_PREPARES => false,
                \PDO::ATTR_PERSISTENT => true // Use persistent connections for better performance
            ];
            
            $this->pdo = new \PDO($dsn, DB_USER, DB_PASS, $options);
        } catch (\PDOException $e) {
            // Log the error
            $this->logError($e->getMessage());
            
            // Display a user-friendly message
            die('Database connection error. Please try again later.');
        }
    }
    
    /**
     * Get the singleton instance
     * 
     * @return Database The database instance
     */
    public static function getInstance()
    {
        if (self::$instance === null) {
            self::$instance = new self();
        }
        
        return self::$instance;
    }
    
    /**
     * Prepare and execute a query
     * 
     * @param string $sql The SQL query to execute
     * @param array $params The parameters to bind to the query
     * @return Database The database instance for method chaining
     */
    public function query($sql, $params = [])
    {
        try {
            $this->statement = $this->pdo->prepare($sql);
            $this->statement->execute($params);
            return $this;
        } catch (\PDOException $e) {
            $this->logError($e->getMessage(), $sql, $params);
            throw $e;
        }
    }
    
    /**
     * Fetch a single row from the result set
     * 
     * @return array|false The row or false if no row is found
     */
    public function fetch()
    {
        return $this->statement->fetch();
    }
    
    /**
     * Fetch all rows from the result set
     * 
     * @return array The result set rows
     */
    public function fetchAll()
    {
        return $this->statement->fetchAll();
    }
    
    /**
     * Get the number of affected rows
     * 
     * @return int The number of rows affected by the last DELETE, INSERT, or UPDATE statement
     */
    public function rowCount()
    {
        return $this->statement->rowCount();
    }
    
    /**
     * Get the last inserted ID
     * 
     * @return string The last inserted ID (as a string)
     */
    public function lastInsertId()
    {
        return $this->pdo->lastInsertId();
    }
    
    /**
     * Begin a transaction
     * 
     * @return bool True on success or false on failure
     */
    public function beginTransaction()
    {
        return $this->pdo->beginTransaction();
    }
    
    /**
     * Commit a transaction
     * 
     * @return bool True on success or false on failure
     */
    public function commit()
    {
        return $this->pdo->commit();
    }
    
    /**
     * Roll back a transaction
     * 
     * @return bool True on success or false on failure
     */
    public function rollBack()
    {
        return $this->pdo->rollBack();
    }
    
    /**
     * Check if a transaction is active
     * 
     * @return bool True if a transaction is active, false otherwise
     */
    public function inTransaction()
    {
        return $this->pdo->inTransaction();
    }
    
    /**
     * Execute a raw SQL query directly (use with caution)
     * Only for queries that don't need parameter binding
     * 
     * @param string $sql The raw SQL query
     * @return bool True on success or false on failure
     */
    public function exec($sql)
    {
        try {
            return $this->pdo->exec($sql);
        } catch (\PDOException $e) {
            $this->logError($e->getMessage(), $sql);
            throw $e;
        }
    }
    
    /**
     * Log a database error
     * 
     * @param string $message The error message
     * @param string $sql The SQL query that caused the error
     * @param array $params The parameters used in the query
     * @return void
     */
    private function logError($message, $sql = '', $params = [])
    {
        $logPath = ROOT_PATH . '/logs';
        
        // Create logs directory if it doesn't exist
        if (!is_dir($logPath)) {
            mkdir($logPath, 0755, true);
        }
        
        $logFile = $logPath . '/db_errors.log';
        $date = date('Y-m-d H:i:s');
        
        // Format params for logging
        $paramStr = '';
        if (!empty($params)) {
            $paramStr = "Parameters: " . print_r($params, true);
        }
        
        // Build log message
        $log = "[{$date}] Error: {$message}" . PHP_EOL;
        if ($sql) {
            $log .= "SQL: {$sql}" . PHP_EOL;
        }
        if ($paramStr) {
            $log .= $paramStr . PHP_EOL;
        }
        $log .= "--------------------" . PHP_EOL;
        
        // Append to log file
        file_put_contents($logFile, $log, FILE_APPEND);
    }
    
    /**
     * Prevent cloning of the instance
     */
    private function __clone() {}
    
    /**
     * Prevent unserializing of the instance
     */
    public function __wakeup()
    {
        throw new \Exception("Cannot unserialize a singleton.");
    }
}