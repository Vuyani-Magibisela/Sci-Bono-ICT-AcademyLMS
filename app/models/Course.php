<?php
namespace App\Models;

use Core\Database;

class Course
{
    private $db;
    private $table = 'courses';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Get all active courses
     *
     * @return array
     */
    public function getAllActiveCourses()
    {
        $query = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY created_at DESC";
        return $this->db->query($query)->fetchAll();
    }

    /**
     * Get featured courses
     *
     * @param int $limit Number of courses to return
     * @return array
     */
    public function getFeaturedCourses($limit = 4)
    {
        $query = "SELECT * FROM {$this->table} WHERE status = 'active' AND featured = 1 ORDER BY created_at DESC LIMIT :limit";
        return $this->db->query($query, ['limit' => $limit])->fetchAll();
    }

    /**
     * Find course by ID
     *
     * @param int $id Course ID
     * @return array|false
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->fetch();
    }

    /**
     * Get full course data including modules and lessons count
     *
     * @param int $id Course ID
     * @return array|false
     */
    public function getFullCourseData($id)
    {
        $query = "
            SELECT c.*, 
                   COUNT(DISTINCT m.id) as total_modules,
                   COUNT(DISTINCT l.id) as total_lessons
            FROM {$this->table} c
            LEFT JOIN modules m ON m.course_id = c.id
            LEFT JOIN lessons l ON l.module_id = m.id
            WHERE c.id = :id
            GROUP BY c.id
        ";
        
        return $this->db->query($query, ['id' => $id])->fetch();
    }

    /**
     * Get courses by category
     *
     * @param int $categoryId Category ID
     * @return array
     */
    public function getCoursesByCategory($categoryId)
    {
        $query = "SELECT * FROM {$this->table} WHERE category_id = :category_id AND status = 'active' ORDER BY created_at DESC";
        return $this->db->query($query, ['category_id' => $categoryId])->fetchAll();
    }

    /**
     * Get courses for a specific user
     * 
     * @param int $userId User ID
     * @return array
     */
    public function getUserCourses($userId)
    {
        $query = "
            SELECT c.*, cp.progress, cp.last_accessed_at
            FROM {$this->table} c
            JOIN course_progress cp ON c.id = cp.course_id
            WHERE cp.user_id = :user_id
            ORDER BY cp.last_accessed_at DESC
        ";
        
        return $this->db->query($query, ['user_id' => $userId])->fetchAll();
    }

    /**
     * Search courses by title or description
     *
     * @param string $query Search query
     * @return array
     */
    public function searchCourses($query)
    {
        $searchTerm = "%{$query}%";
        
        $sql = "
            SELECT * FROM {$this->table} 
            WHERE status = 'active' AND (
                title LIKE :search 
                OR description LIKE :search
                OR keywords LIKE :search
            )
            ORDER BY created_at DESC
        ";
        
        return $this->db->query($sql, ['search' => $searchTerm])->fetchAll();
    }

    /**
     * Create a new course
     *
     * @param array $data Course data
     * @return int|string|false Last insert ID or false on failure
     */
    public function create($data)
    {
        // Set default values if not provided
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');
        $data['status'] = $data['status'] ?? 'draft';
        
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
     * Update a course
     *
     * @param int $id Course ID
     * @param array $data Course data
     * @return bool Success or failure
     */
    public function update($id, $data)
    {
        // Always update the updated_at timestamp
        $data['updated_at'] = date('Y-m-d H:i:s');
        
        // Build SET clause
        $setClause = [];
        foreach ($data as $field => $value) {
            $setClause[] = "{$field} = :{$field}";
        }
        
        $query = "
            UPDATE {$this->table} 
            SET " . implode(', ', $setClause) . "
            WHERE id = :id
        ";
        
        // Add ID to data array
        $data['id'] = $id;
        
        return $this->db->query($query, $data)->rowCount() > 0;
    }

    /**
     * Delete a course
     *
     * @param int $id Course ID
     * @return bool Success or failure
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->rowCount() > 0;
    }

    /**
     * Get total number of active courses
     *
     * @return int
     */
    public function getTotalCourses()
    {
        $query = "SELECT COUNT(*) as total FROM {$this->table} WHERE status = 'active'";
        $result = $this->db->query($query)->fetch();
        return $result['total'] ?? 0;
    }

    /**
     * Get recent courses
     *
     * @param int $limit Number of courses to return
     * @return array
     */
    public function getRecentCourses($limit = 5)
    {
        $query = "SELECT * FROM {$this->table} WHERE status = 'active' ORDER BY created_at DESC LIMIT :limit";
        return $this->db->query($query, ['limit' => $limit])->fetchAll();
    }

    /**
     * Get related courses
     *
     * @param int $courseId Current course ID
     * @param int $categoryId Category ID
     * @param int $limit Number of courses to return
     * @return array
     */
    public function getRelatedCourses($courseId, $categoryId, $limit = 3)
    {
        $query = "
            SELECT * FROM {$this->table} 
            WHERE status = 'active' 
            AND id != :course_id
            AND category_id = :category_id
            ORDER BY created_at DESC
            LIMIT :limit
        ";
        
        return $this->db->query($query, [
            'course_id' => $courseId,
            'category_id' => $categoryId,
            'limit' => $limit
        ])->fetchAll();
    }
}