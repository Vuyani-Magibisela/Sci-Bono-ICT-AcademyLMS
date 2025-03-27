<?php
namespace App\Models;

use Core\Database;

class Lesson
{
    private $db;
    private $table = 'lessons';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find lesson by ID
     *
     * @param int $id Lesson ID
     * @return array|false
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->fetch();
    }

    /**
     * Get all lessons for a module
     *
     * @param int $moduleId Module ID
     * @return array
     */
    public function getLessonsByModuleId($moduleId)
    {
        $query = "
            SELECT * FROM {$this->table}
            WHERE module_id = :module_id
            ORDER BY order_index ASC
        ";
        
        return $this->db->query($query, ['module_id' => $moduleId])->fetchAll();
    }

    /**
     * Get lesson with detailed information
     *
     * @param int $id Lesson ID
     * @return array|false
     */
    public function getLessonWithDetails($id)
    {
        $query = "
            SELECT l.*, m.title as module_title, c.title as course_title, c.id as course_id
            FROM {$this->table} l
            JOIN modules m ON l.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE l.id = :id
        ";
        
        return $this->db->query($query, ['id' => $id])->fetch();
    }

    /**
     * Get lesson with resources
     *
     * @param int $id Lesson ID
     * @return array|false
     */
    public function getLessonWithResources($id)
    {
        // First get the lesson details
        $lesson = $this->getLessonWithDetails($id);
        
        if (!$lesson) {
            return false;
        }
        
        // Get resources for this lesson
        $query = "
            SELECT * FROM lesson_resources
            WHERE lesson_id = :lesson_id
            ORDER BY order_index ASC
        ";
        
        $resources = $this->db->query($query, ['lesson_id' => $id])->fetchAll();
        
        // Add resources to the lesson data
        $lesson['resources'] = $resources;
        
        return $lesson;
    }

    /**
     * Get lesson with quiz
     *
     * @param int $id Lesson ID
     * @return array|false
     */
    public function getLessonWithQuiz($id)
    {
        // First get the lesson details
        $lesson = $this->getLessonWithDetails($id);
        
        if (!$lesson) {
            return false;
        }
        
        // Get quiz for this lesson
        $query = "
            SELECT q.*, 
                   (SELECT COUNT(*) FROM quiz_questions WHERE quiz_id = q.id) as question_count
            FROM quizzes q
            WHERE q.lesson_id = :lesson_id
        ";
        
        $quiz = $this->db->query($query, ['lesson_id' => $id])->fetch();
        
        if ($quiz) {
            // Get questions for this quiz
            $query = "
                SELECT * FROM quiz_questions
                WHERE quiz_id = :quiz_id
                ORDER BY order_index ASC
            ";
            
            $questions = $this->db->query($query, ['quiz_id' => $quiz['id']])->fetchAll();
            
            // For each question, get its options
            foreach ($questions as &$question) {
                $query = "
                    SELECT * FROM quiz_question_options
                    WHERE question_id = :question_id
                    ORDER BY order_index ASC
                ";
                
                $options = $this->db->query($query, ['question_id' => $question['id']])->fetchAll();
                $question['options'] = $options;
            }
            
            $quiz['questions'] = $questions;
        }
        
        // Add quiz to the lesson data
        $lesson['quiz'] = $quiz ?: null;
        
        return $lesson;
    }

    /**
     * Create a new lesson
     *
     * @param array $data Lesson data
     * @return int|false Last insert ID or false on failure
     */
    public function create($data)
    {
        // Set default values if not provided
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');
        
        // Get the highest order_index for this module and add 1
        if (!isset($data['order_index'])) {
            $query = "
                SELECT MAX(order_index) as max_order
                FROM {$this->table}
                WHERE module_id = :module_id
            ";
            
            $result = $this->db->query($query, ['module_id' => $data['module_id']])->fetch();
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
     * Update a lesson
     *
     * @param int $id Lesson ID
     * @param array $data Lesson data
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
     * Delete a lesson
     *
     * @param int $id Lesson ID
     * @return bool Success or failure
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->rowCount() > 0;
    }

    /**
     * Get next lesson in a module
     *
     * @param int $currentLessonId Current lesson ID
     * @param int $moduleId Module ID
     * @return array|false Next lesson or false if none
     */
    public function getNextLesson($currentLessonId, $moduleId)
    {
        // Get the current lesson's order index
        $currentLesson = $this->findById($currentLessonId);
        
        if (!$currentLesson) {
            return false;
        }
        
        $query = "
            SELECT * FROM {$this->table}
            WHERE module_id = :module_id
            AND order_index > :current_order
            ORDER BY order_index ASC
            LIMIT 1
        ";
        
        return $this->db->query($query, [
            'module_id' => $moduleId,
            'current_order' => $currentLesson['order_index']
        ])->fetch();
    }

    /**
     * Get previous lesson in a module
     *
     * @param int $currentLessonId Current lesson ID
     * @param int $moduleId Module ID
     * @return array|false Previous lesson or false if none
     */
    public function getPreviousLesson($currentLessonId, $moduleId)
    {
        // Get the current lesson's order index
        $currentLesson = $this->findById($currentLessonId);
        
        if (!$currentLesson) {
            return false;
        }
        
        $query = "
            SELECT * FROM {$this->table}
            WHERE module_id = :module_id
            AND order_index < :current_order
            ORDER BY order_index DESC
            LIMIT 1
        ";
        
        return $this->db->query($query, [
            'module_id' => $moduleId,
            'current_order' => $currentLesson['order_index']
        ])->fetch();
    }

    /**
     * Reorder lessons
     *
     * @param array $lessonIds Ordered array of lesson IDs
     * @return bool Success or failure
     */
    public function reorder($lessonIds)
    {
        $this->db->beginTransaction();
        
        try {
            foreach ($lessonIds as $index => $lessonId) {
                $query = "
                    UPDATE {$this->table}
                    SET order_index = :order_index
                    WHERE id = :id
                ";
                
                $this->db->query($query, [
                    'order_index' => $index + 1,
                    'id' => $lessonId
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
     * Get course ID by lesson ID
     *
     * @param int $lessonId Lesson ID
     * @return int|false Course ID or false if not found
     */
    public function getCourseIdByLessonId($lessonId)
    {
        $query = "
            SELECT c.id
            FROM {$this->table} l
            JOIN modules m ON l.module_id = m.id
            JOIN courses c ON m.course_id = c.id
            WHERE l.id = :lesson_id
        ";
        
        $result = $this->db->query($query, ['lesson_id' => $lessonId])->fetch();
        
        return $result ? $result['id'] : false;
    }

    /**
     * Count lessons by course ID
     *
     * @param int $courseId Course ID
     * @return int
     */
    public function countLessonsByCourseId($courseId)
    {
        $query = "
            SELECT COUNT(*) as count
            FROM {$this->table} l
            JOIN modules m ON l.module_id = m.id
            WHERE m.course_id = :course_id
        ";
        
        $result = $this->db->query($query, ['course_id' => $courseId])->fetch();
        
        return $result ? (int)$result['count'] : 0;
    }

    /**
     * Get first lesson in a course
     *
     * @param int $courseId Course ID
     * @return array|false First lesson or false if none
     */
    public function getFirstLessonInCourse($courseId)
    {
        $query = "
            SELECT l.*
            FROM {$this->table} l
            JOIN modules m ON l.module_id = m.id
            WHERE m.course_id = :course_id
            ORDER BY m.order_index ASC, l.order_index ASC
            LIMIT 1
        ";
        
        return $this->db->query($query, ['course_id' => $courseId])->fetch();
    }
}