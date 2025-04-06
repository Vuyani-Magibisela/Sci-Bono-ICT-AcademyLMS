<?php
namespace App\Models;

use Core\Database;

class CourseProgress
{
    private $db;
    private $table = 'course_progress';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find course progress by user and course IDs
     *
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return array|false
     */
    public function findByUserAndCourse($userId, $courseId)
    {
        $query = "SELECT * FROM {$this->table} WHERE user_id = :user_id AND course_id = :course_id";
        return $this->db->query($query, ['user_id' => $userId, 'course_id' => $courseId])->fetch();
    }

    /**
     * Create new course progress record
     *
     * @param array $data Progress data
     * @return int|string|false The last insert ID or false on failure
     */
    public function create($data)
    {
        // Build query
        $fields = array_keys($data);
        $placeholders = array_map(function($field) {
            return ":{$field}";
        }, $fields);
        
        $query = "
            INSERT INTO {$this->table} 
            (" . implode(', ', $fields) . ") 
            VALUES 
            (" . implode(', ', $placeholders) . ")
        ";
        
        if ($this->db->query($query, $data)) {
            return $this->db->lastInsertId();
        }
        
        return false;
    }

    /**
     * Update course progress
     *
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @param array $data Data to update
     * @return bool Success or failure
     */
    public function update($userId, $courseId, $data)
    {
        $setClause = array_map(function($field) {
            return "{$field} = :{$field}";
        }, array_keys($data));
        
        $query = "
            UPDATE {$this->table} 
            SET " . implode(', ', $setClause) . "
            WHERE user_id = :user_id AND course_id = :course_id
        ";
        
        // Add IDs to data array
        $data['user_id'] = $userId;
        $data['course_id'] = $courseId;
        
        return $this->db->query($query, $data)->rowCount() > 0;
    }

    /**
     * Check if user is enrolled in a course
     *
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool
     */
    public function isUserEnrolled($userId, $courseId)
    {
        $query = "
            SELECT COUNT(*) as count 
            FROM {$this->table} 
            WHERE user_id = :user_id 
            AND course_id = :course_id
        ";
        
        $result = $this->db->query($query, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
        
        return ($result && $result['count'] > 0);
    }

    /**
     * Update last accessed timestamp
     *
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool Success or failure
     */
    public function updateLastAccessed($userId, $courseId)
    {
        $query = "
            UPDATE {$this->table}
            SET last_accessed_at = :now, updated_at = :now
            WHERE user_id = :user_id AND course_id = :course_id
        ";
        
        return $this->db->query($query, [
            'now' => date('Y-m-d H:i:s'),
            'user_id' => $userId,
            'course_id' => $courseId
        ])->rowCount() > 0;
    }
}