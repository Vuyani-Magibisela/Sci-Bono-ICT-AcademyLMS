<?php
namespace App\Models;

use Core\Database;

class Module
{
    private $db;
    private $table = 'modules';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find module by ID
     *
     * @param int $id Module ID
     * @return array|false
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->fetch();
    }

    /**
     * Get all modules for a course
     *
     * @param int $courseId Course ID
     * @return array
     */
    public function getModulesByCourseId($courseId)
    {
        $query = "
            SELECT m.*, 
                   (SELECT COUNT(*) FROM lessons WHERE module_id = m.id) as lesson_count
            FROM {$this->table} m
            WHERE m.course_id = :course_id
            ORDER BY m.order_index ASC
        ";
        
        return $this->db->query($query, ['course_id' => $courseId])->fetchAll();
    }

    /**
     * Get module with full details including lessons
     *
     * @param int $id Module ID
     * @return array|false
     */
    public function getModuleWithLessons($id)
    {
        // First get the module
        $module = $this->findById($id);
        
        if (!$module) {
            return false;
        }
        
        // Get all lessons for this module
        $lessonModel = new Lesson();
        $lessons = $lessonModel->getLessonsByModuleId($id);
        
        // Add lessons to the module data
        $module['lessons'] = $lessons;
        
        return $module;
    }

    /**
     * Create a new module
     *
     * @param array $data Module data
     * @return int|false Last insert ID or false on failure
     */
    public function create($data)
    {
        // Set default values if not provided
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');
        
        // Get the highest order_index for this course and add 1
        if (!isset($data['order_index'])) {
            $query = "
                SELECT MAX(order_index) as max_order
                FROM {$this->table}
                WHERE course_id = :course_id
            ";
            
            $result = $this->db->query($query, ['course_id' => $data['course_id']])->fetch();
            $data['order_index'] = ($result['max_order'] ?? 0) + 1;
        }
        
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
     * Update a module
     *
     * @param int $id Module ID
     * @param array $data Module data
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
     * Delete a module
     *
     * @param int $id Module ID
     * @return bool Success or failure
     */
    public function delete($id)
    {
        // This should be wrapped in a transaction with deleting associated lessons
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->rowCount() > 0;
    }

    /**
     * Reorder modules
     *
     * @param array $moduleIds Ordered array of module IDs
     * @return bool Success or failure
     */
    public function reorder($moduleIds)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($moduleIds as $index => $moduleId) {
                $query = "
                    UPDATE {$this->table}
                    SET order_index = :order_index
                    WHERE id = :id
                ";
                
                $this->db->query($query, [
                    'order_index' => $index + 1,
                    'id' => $moduleId
                ]);
            }
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get next module in a course
     *
     * @param int $currentModuleId Current module ID
     * @param int $courseId Course ID
     * @return array|false Next module or false if none
     */
    public function getNextModule($currentModuleId, $courseId)
    {
        // Get the current module's order index
        $currentModule = $this->findById($currentModuleId);
        
        if (!$currentModule) {
            return false;
        }
        
        $query = "
            SELECT * FROM {$this->table}
            WHERE course_id = :course_id
            AND order_index > :current_order
            ORDER BY order_index ASC
            LIMIT 1
        ";
        
        return $this->db->query($query, [
            'course_id' => $courseId,
            'current_order' => $currentModule['order_index']
        ])->fetch();
    }

    /**
     * Get previous module in a course
     *
     * @param int $currentModuleId Current module ID
     * @param int $courseId Course ID
     * @return array|false Previous module or false if none
     */
    public function getPreviousModule($currentModuleId, $courseId)
    {
        // Get the current module's order index
        $currentModule = $this->findById($currentModuleId);
        
        if (!$currentModule) {
            return false;
        }
        
        $query = "
            SELECT * FROM {$this->table}
            WHERE course_id = :course_id
            AND order_index < :current_order
            ORDER BY order_index DESC
            LIMIT 1
        ";
        
        return $this->db->query($query, [
            'course_id' => $courseId,
            'current_order' => $currentModule['order_index']
        ])->fetch();
    }

    /**
     * Get module progress for a user
     *
     * @param int $moduleId Module ID
     * @param int $userId User ID
     * @return float Percentage of completion
     */
    public function getModuleProgressForUser($moduleId, $userId)
    {
        $query = "
            SELECT 
                COUNT(l.id) as total_lessons,
                COUNT(lp.id) as completed_lessons
            FROM {$this->table} m
            JOIN lessons l ON l.module_id = m.id
            LEFT JOIN lesson_progress lp ON lp.lesson_id = l.id AND lp.user_id = :user_id
            WHERE m.id = :module_id
            GROUP BY m.id
        ";
        
        $result = $this->db->query($query, [
            'module_id' => $moduleId,
            'user_id' => $userId
        ])->fetch();
        
        if (!$result || $result['total_lessons'] == 0) {
            return 0;
        }
        
        return ($result['completed_lessons'] / $result['total_lessons']) * 100;
    }
}