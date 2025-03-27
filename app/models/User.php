<?php
namespace App\Models;

use Core\Database;

class User
{
    private $db;
    private $table = 'users';

    public function __construct()
    {
        $this->db = Database::getInstance();
    }

    /**
     * Find user by ID
     *
     * @param int $id User ID
     * @return array|false
     */
    public function findById($id)
    {
        $query = "SELECT * FROM {$this->table} WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->fetch();
    }

    /**
     * Find user by email
     *
     * @param string $email User email
     * @return array|false
     */
    public function findByEmail($email)
    {
        $query = "SELECT * FROM {$this->table} WHERE email = :email";
        return $this->db->query($query, ['email' => $email])->fetch();
    }

    /**
     * Create a new user
     *
     * @param array $data User data
     * @return int|false Last insert ID or false on failure
     */
    public function create($data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
        // Set default values
        $data['created_at'] = $data['created_at'] ?? date('Y-m-d H:i:s');
        $data['updated_at'] = $data['updated_at'] ?? date('Y-m-d H:i:s');
        $data['role'] = $data['role'] ?? 'student';
        $data['status'] = $data['status'] ?? 'active';
        
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
     * Update a user
     *
     * @param int $id User ID
     * @param array $data User data
     * @return bool Success or failure
     */
    public function update($id, $data)
    {
        // Hash password if provided
        if (isset($data['password'])) {
            $data['password'] = password_hash($data['password'], PASSWORD_DEFAULT);
        }
        
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
     * Delete a user
     *
     * @param int $id User ID
     * @return bool Success or failure
     */
    public function delete($id)
    {
        $query = "DELETE FROM {$this->table} WHERE id = :id";
        return $this->db->query($query, ['id' => $id])->rowCount() > 0;
    }

    /**
     * Verify user password
     *
     * @param string $email User email
     * @param string $password Password to verify
     * @return array|false User data if verified, false otherwise
     */
    public function verifyPassword($email, $password)
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        if (password_verify($password, $user['password'])) {
            return $user;
        }
        
        return false;
    }

    /**
     * Create password reset token
     *
     * @param string $email User email
     * @return string|false Reset token or false on failure
     */
    public function createPasswordResetToken($email)
    {
        $user = $this->findByEmail($email);
        
        if (!$user) {
            return false;
        }
        
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+1 hour'));
        
        $query = "
            INSERT INTO password_resets (user_id, token, expires_at, created_at)
            VALUES (:user_id, :token, :expires_at, :created_at)
        ";
        
        $result = $this->db->query($query, [
            'user_id' => $user['id'],
            'token' => $token,
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $result ? $token : false;
    }

    /**
     * Validate password reset token
     *
     * @param string $token Reset token
     * @return int|false User ID if valid, false otherwise
     */
    public function validateResetToken($token)
    {
        $query = "
            SELECT user_id 
            FROM password_resets 
            WHERE token = :token 
            AND expires_at > :now 
            AND used = 0
            ORDER BY created_at DESC
            LIMIT 1
        ";
        
        $result = $this->db->query($query, [
            'token' => $token,
            'now' => date('Y-m-d H:i:s')
        ])->fetch();
        
        return $result ? $result['user_id'] : false;
    }

    /**
     * Reset password using token
     *
     * @param string $token Reset token
     * @param string $newPassword New password
     * @return bool Success or failure
     */
    public function resetPassword($token, $newPassword)
    {
        $userId = $this->validateResetToken($token);
        
        if (!$userId) {
            return false;
        }
        
        $this->db->beginTransaction();
        
        try {
            // Update password
            $result = $this->update($userId, [
                'password' => $newPassword
            ]);
            
            if (!$result) {
                throw new \Exception('Failed to update password');
            }
            
            // Mark token as used
            $query = "
                UPDATE password_resets 
                SET used = 1, used_at = :used_at 
                WHERE token = :token
            ";
            
            $this->db->query($query, [
                'used_at' => date('Y-m-d H:i:s'),
                'token' => $token
            ]);
            
            $this->db->commit();
            return true;
        } catch (\Exception $e) {
            $this->db->rollBack();
            return false;
        }
    }

    /**
     * Get user profile with additional details
     *
     * @param int $userId User ID
     * @return array|false
     */
    public function getUserProfile($userId)
    {
        $query = "
            SELECT u.*, 
                   COUNT(DISTINCT cp.course_id) as enrolled_courses,
                   COUNT(DISTINCT lp.lesson_id) as completed_lessons
            FROM {$this->table} u
            LEFT JOIN course_progress cp ON u.id = cp.user_id
            LEFT JOIN lesson_progress lp ON u.id = lp.user_id
            WHERE u.id = :user_id
            GROUP BY u.id
        ";
        
        return $this->db->query($query, ['user_id' => $userId])->fetch();
    }

    /**
     * Update user last active timestamp
     *
     * @param int $userId User ID
     * @return bool Success or failure
     */
    public function updateLastActive($userId)
    {
        $query = "
            UPDATE {$this->table}
            SET last_active_at = :last_active_at
            WHERE id = :id
        ";
        
        return $this->db->query($query, [
            'last_active_at' => date('Y-m-d H:i:s'),
            'id' => $userId
        ])->rowCount() > 0;
    }

    /**
     * Find user by remember token
     *
     * @param string $token Remember token
     * @return array|false User data or false if not found
     */
    public function findByRememberToken($token)
    {
        $query = "
            SELECT u.*
            FROM {$this->table} u
            JOIN user_tokens ut ON u.id = ut.user_id
            WHERE ut.token = :token
            AND ut.type = 'remember'
            AND ut.expires_at > :now
        ";
        
        return $this->db->query($query, [
            'token' => $token,
            'now' => date('Y-m-d H:i:s')
        ])->fetch();
    }

    /**
     * Create remember token
     *
     * @param int $userId User ID
     * @return string|false Token or false on failure
     */
    public function createRememberToken($userId)
    {
        // Generate token
        $token = bin2hex(random_bytes(32));
        $expires = date('Y-m-d H:i:s', strtotime('+30 days'));
        
        $query = "
            INSERT INTO user_tokens (user_id, token, type, expires_at, created_at)
            VALUES (:user_id, :token, 'remember', :expires_at, :created_at)
        ";
        
        $result = $this->db->query($query, [
            'user_id' => $userId,
            'token' => $token,
            'expires_at' => $expires,
            'created_at' => date('Y-m-d H:i:s')
        ]);
        
        return $result ? $token : false;
    }

    /**
     * Delete remember token
     *
     * @param string $token Remember token
     * @return bool Success or failure
     */
    public function deleteRememberToken($token)
    {
        $query = "DELETE FROM user_tokens WHERE token = :token AND type = 'remember'";
        return $this->db->query($query, ['token' => $token])->rowCount() > 0;
    }

    /**
     * Get user statistics
     *
     * @param int $userId User ID
     * @return array
     */
    public function getUserStats($userId)
    {
        $query = "
            SELECT 
                COUNT(DISTINCT cp.course_id) as enrolled_courses,
                COUNT(DISTINCT lp.lesson_id) as completed_lessons,
                COUNT(DISTINCT q.id) as completed_quizzes,
                AVG(q.score) as average_quiz_score,
                MAX(lp.completed_at) as last_activity
            FROM {$this->table} u
            LEFT JOIN course_progress cp ON u.id = cp.user_id
            LEFT JOIN lesson_progress lp ON u.id = lp.user_id
            LEFT JOIN quiz_results q ON u.id = q.user_id
            WHERE u.id = :user_id
            GROUP BY u.id
        ";
        
        return $this->db->query($query, ['user_id' => $userId])->fetch();
    }

    /**
     * Check if user is enrolled in a course
     *
     * @param int $userId User ID
     * @param int $courseId Course ID
     * @return bool
     */
    public function isEnrolledInCourse($userId, $courseId)
    {
        $query = "
            SELECT COUNT(*) as count 
            FROM course_progress 
            WHERE user_id = :user_id 
            AND course_id = :course_id
        ";
        
        $result = $this->db->query($query, [
            'user_id' => $userId,
            'course_id' => $courseId
        ])->fetch();
        
        return $result && $result['count'] > 0;
    }

    /**
     * Get all users (with pagination)
     *
     * @param int $page Page number
     * @param int $perPage Items per page
     * @param string $search Search term
     * @return array
     */
    public function getAllUsers($page = 1, $perPage = 20, $search = '')
    {
        $offset = ($page - 1) * $perPage;
        
        $params = [
            'limit' => $perPage,
            'offset' => $offset
        ];
        
        $searchClause = '';
        if (!empty($search)) {
            $searchClause = "
                WHERE name LIKE :search 
                OR email LIKE :search
            ";
            $params['search'] = "%{$search}%";
        }
        
        $query = "
            SELECT * FROM {$this->table}
            {$searchClause}
            ORDER BY created_at DESC
            LIMIT :limit OFFSET :offset
        ";
        
        $users = $this->db->query($query, $params)->fetchAll();
        
        // Get total count for pagination
        $countQuery = "SELECT COUNT(*) as total FROM {$this->table} {$searchClause}";
        $count = $this->db->query($countQuery, $search ? ['search' => "%{$search}%"] : [])->fetch();
        
        return [
            'users' => $users,
            'total' => $count ? $count['total'] : 0,
            'pages' => ceil(($count ? $count['total'] : 0) / $perPage),
            'current_page' => $page
        ];
    }

    /**
     * Generate username from email or name
     *
     * @param string $email User email
     * @param string $name User name
     * @return string
     */
    public function generateUsername($email, $name = '')
    {
        // Try to use name first
        if (!empty($name)) {
            $username = strtolower(preg_replace('/[^a-zA-Z0-9]/', '', $name));
        } else {
            // Use email without domain
            $username = strtolower(explode('@', $email)[0]);
        }
        
        // Check if username exists
        $query = "SELECT COUNT(*) as count FROM {$this->table} WHERE username = :username";
        $result = $this->db->query($query, ['username' => $username])->fetch();
        
        if ($result && $result['count'] > 0) {
            // Username exists, add a random number
            $username .= rand(100, 999);
        }
        
        return $username;
    }
}