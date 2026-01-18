<?php
/**
 * Careers System Helper Functions
 * Provides CRUD operations and utility functions for the careers system
 */

/**
 * Get database connection
 * @return PDO
 */
function getCareersDB() {
    static $pdo = null;
    
    if ($pdo === null) {
        try {
            $host = 'localhost';
            $dbname = 'joseph_pot_admin';
            $username = 'root';
            $password = '';
            
            $pdo = new PDO(
                "mysql:host=$host;dbname=$dbname;charset=utf8mb4",
                $username,
                $password,
                [
                    PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                    PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC,
                    PDO::ATTR_EMULATE_PREPARES => false
                ]
            );
            
            // Auto-create careers tables if they don't exist
            createCareersTablesIfNotExist($pdo);
        } catch (PDOException $e) {
            error_log("Careers DB Connection Error: " . $e->getMessage());
            throw new Exception("Database connection failed: " . $e->getMessage());
        }
    }
    
    return $pdo;
}

/**
 * Create careers tables if they don't exist
 * @param PDO $pdo
 */
function createCareersTablesIfNotExist($pdo) {
    try {
        // Check if jobs table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'jobs'");
        if ($stmt->rowCount() == 0) {
            // Read and execute schema file
            $schemaFile = __DIR__ . '/../database/careers_schema.sql';
            if (file_exists($schemaFile)) {
                $sql = file_get_contents($schemaFile);
                // Remove comments
                $sql = preg_replace('/--.*$/m', '', $sql);
                // Split by semicolon
                $statements = array_filter(array_map('trim', explode(';', $sql)));
                
                foreach ($statements as $statement) {
                    if (!empty($statement) && strlen($statement) > 10) {
                        try {
                            $pdo->exec($statement);
                        } catch (PDOException $e) {
                            // Ignore "already exists" errors
                            if (strpos($e->getMessage(), 'already exists') === false) {
                                error_log("Error creating careers table: " . $e->getMessage());
                            }
                        }
                    }
                }
            }
        }
    } catch (PDOException $e) {
        error_log("Error checking/creating careers tables: " . $e->getMessage());
    }
}

/**
 * Get jobs with optional filters
 * @param array $filters - Array of filter options (status, department, job_type, etc.)
 * @return array
 */
function getJobs($filters = []) {
    try {
        $pdo = getCareersDB();
        $whereConditions = [];
        $params = [];
        
        // Status filter
        if (!empty($filters['status'])) {
            $whereConditions[] = "status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Department filter
        if (!empty($filters['department'])) {
            $whereConditions[] = "department = :department";
            $params[':department'] = $filters['department'];
        }
        
        // Job type filter
        if (!empty($filters['job_type'])) {
            $whereConditions[] = "job_type = :job_type";
            $params[':job_type'] = $filters['job_type'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $whereConditions[] = "(title LIKE :search OR description LIKE :search OR department LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        // Active jobs only (for public)
        if (isset($filters['active_only']) && $filters['active_only']) {
            $whereConditions[] = "status = 'active'";
            $whereConditions[] = "(application_deadline IS NULL OR application_deadline >= CURDATE())";
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "SELECT * FROM jobs {$whereClause} ORDER BY created_at DESC";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting jobs: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single job by ID
 * @param int $id
 * @return array|false
 */
function getJob($id) {
    try {
        $pdo = getCareersDB();
        $stmt = $pdo->prepare("SELECT * FROM jobs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting job: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new job
 * @param array $data
 * @return array - ['success' => bool, 'id' => int|false, 'message' => string]
 */
function createJob($data) {
    try {
        $pdo = getCareersDB();
        $sql = "INSERT INTO jobs (
            title, department, job_type, location, salary_range, 
            description, requirements, responsibilities, benefits, 
            status, application_deadline, positions_available
        ) VALUES (
            :title, :department, :job_type, :location, :salary_range,
            :description, :requirements, :responsibilities, :benefits,
            :status, :application_deadline, :positions_available
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':title' => $data['title'],
            ':department' => $data['department'],
            ':job_type' => $data['job_type'],
            ':location' => $data['location'],
            ':salary_range' => $data['salary_range'] ?? null,
            ':description' => $data['description'],
            ':requirements' => $data['requirements'],
            ':responsibilities' => $data['responsibilities'],
            ':benefits' => $data['benefits'] ?? null,
            ':status' => $data['status'] ?? 'draft',
            ':application_deadline' => !empty($data['application_deadline']) ? $data['application_deadline'] : null,
            ':positions_available' => $data['positions_available'] ?? 1
        ]);
        
        return [
            'success' => true,
            'id' => $pdo->lastInsertId(),
            'message' => 'Job created successfully'
        ];
    } catch (PDOException $e) {
        error_log("Error creating job: " . $e->getMessage());
        return [
            'success' => false,
            'id' => false,
            'message' => 'Failed to create job: ' . $e->getMessage()
        ];
    }
}

/**
 * Update a job
 * @param int $id
 * @param array $data
 * @return array - ['success' => bool, 'message' => string]
 */
function updateJob($id, $data) {
    try {
        $pdo = getCareersDB();
        $fields = [];
        $params = [':id' => $id];
        
        $allowedFields = [
            'title', 'department', 'job_type', 'location', 'salary_range',
            'description', 'requirements', 'responsibilities', 'benefits',
            'status', 'application_deadline', 'positions_available'
        ];
        
        foreach ($allowedFields as $field) {
            if (isset($data[$field])) {
                $fields[] = "{$field} = :{$field}";
                $params[":{$field}"] = $data[$field];
            }
        }
        
        if (empty($fields)) {
            return ['success' => false, 'message' => 'No fields to update'];
        }
        
        $sql = "UPDATE jobs SET " . implode(', ', $fields) . " WHERE id = :id";
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return [
            'success' => true,
            'message' => 'Job updated successfully'
        ];
    } catch (PDOException $e) {
        error_log("Error updating job: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to update job: ' . $e->getMessage()
        ];
    }
}

/**
 * Delete a job
 * @param int $id
 * @return array - ['success' => bool, 'message' => string]
 */
function deleteJob($id) {
    try {
        $pdo = getCareersDB();
        // Check if job has applications
        $stmt = $pdo->prepare("SELECT COUNT(*) as count FROM applications WHERE job_id = :id");
        $stmt->execute([':id' => $id]);
        $result = $stmt->fetch();
        
        if ($result['count'] > 0) {
            return [
                'success' => false,
                'message' => 'Cannot delete job with existing applications. Please close the job instead.'
            ];
        }
        
        $stmt = $pdo->prepare("DELETE FROM jobs WHERE id = :id");
        $stmt->execute([':id' => $id]);
        
        return [
            'success' => true,
            'message' => 'Job deleted successfully'
        ];
    } catch (PDOException $e) {
        error_log("Error deleting job: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to delete job: ' . $e->getMessage()
        ];
    }
}

/**
 * Get applications with optional filters
 * @param array $filters
 * @return array
 */
function getApplications($filters = []) {
    try {
        $pdo = getCareersDB();
        $whereConditions = [];
        $params = [];
        
        // Job ID filter
        if (!empty($filters['job_id'])) {
            $whereConditions[] = "a.job_id = :job_id";
            $params[':job_id'] = $filters['job_id'];
        }
        
        // Status filter
        if (!empty($filters['status'])) {
            $whereConditions[] = "a.status = :status";
            $params[':status'] = $filters['status'];
        }
        
        // Search filter
        if (!empty($filters['search'])) {
            $whereConditions[] = "(a.applicant_name LIKE :search OR a.applicant_email LIKE :search OR a.applicant_phone LIKE :search OR j.title LIKE :search)";
            $params[':search'] = "%{$filters['search']}%";
        }
        
        // Date range filter
        if (!empty($filters['date_from'])) {
            $whereConditions[] = "DATE(a.applied_date) >= :date_from";
            $params[':date_from'] = $filters['date_from'];
        }
        
        if (!empty($filters['date_to'])) {
            $whereConditions[] = "DATE(a.applied_date) <= :date_to";
            $params[':date_to'] = $filters['date_to'];
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "SELECT a.*, j.title as job_title, j.department 
                FROM applications a 
                LEFT JOIN jobs j ON a.job_id = j.id 
                {$whereClause}
                ORDER BY a.applied_date DESC";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting applications: " . $e->getMessage());
        return [];
    }
}

/**
 * Get a single application by ID
 * @param int $id
 * @return array|false
 */
function getApplication($id) {
    try {
        $pdo = getCareersDB();
        $stmt = $pdo->prepare("SELECT a.*, j.title as job_title, j.department, j.job_type, j.location 
                               FROM applications a 
                               LEFT JOIN jobs j ON a.job_id = j.id 
                               WHERE a.id = :id");
        $stmt->execute([':id' => $id]);
        return $stmt->fetch();
    } catch (PDOException $e) {
        error_log("Error getting application: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a new application
 * @param array $data
 * @param array|null $resumeFile - $_FILES['resume'] array
 * @return array - ['success' => bool, 'id' => int|false, 'message' => string]
 */
function createApplication($data, $resumeFile = null) {
    try {
        $pdo = getCareersDB();
        // Validate job exists and is active
        $job = getJob($data['job_id']);
        if (!$job) {
            return ['success' => false, 'id' => false, 'message' => 'Job not found'];
        }
        
        if ($job['status'] !== 'active') {
            return ['success' => false, 'id' => false, 'message' => 'This job is not currently accepting applications'];
        }
        
        // Check deadline
        if (!empty($job['application_deadline']) && $job['application_deadline'] < date('Y-m-d')) {
            return ['success' => false, 'id' => false, 'message' => 'Application deadline has passed'];
        }
        
        // Handle resume upload
        $resumePath = null;
        if ($resumeFile && $resumeFile['error'] === UPLOAD_ERR_OK) {
            $uploadResult = uploadResume($resumeFile);
            if (!$uploadResult['success']) {
                return $uploadResult;
            }
            $resumePath = $uploadResult['path'];
        }
        
        // Insert application
        $sql = "INSERT INTO applications (
            job_id, applicant_name, applicant_email, applicant_phone,
            cover_letter, resume_path, years_experience, current_position,
            current_company, expected_salary, notice_period, status
        ) VALUES (
            :job_id, :applicant_name, :applicant_email, :applicant_phone,
            :cover_letter, :resume_path, :years_experience, :current_position,
            :current_company, :expected_salary, :notice_period, 'pending'
        )";
        
        $stmt = $pdo->prepare($sql);
        $stmt->execute([
            ':job_id' => $data['job_id'],
            ':applicant_name' => $data['applicant_name'],
            ':applicant_email' => $data['applicant_email'],
            ':applicant_phone' => $data['applicant_phone'],
            ':cover_letter' => $data['cover_letter'] ?? null,
            ':resume_path' => $resumePath,
            ':years_experience' => $data['years_experience'] ?? 0,
            ':current_position' => $data['current_position'] ?? null,
            ':current_company' => $data['current_company'] ?? null,
            ':expected_salary' => $data['expected_salary'] ?? null,
            ':notice_period' => $data['notice_period'] ?? null
        ]);
        
        $applicationId = $pdo->lastInsertId();
        
        // Send notification
        sendApplicationNotification($applicationId);
        
        return [
            'success' => true,
            'id' => $applicationId,
            'message' => 'Application submitted successfully'
        ];
    } catch (PDOException $e) {
        error_log("Error creating application: " . $e->getMessage());
        return [
            'success' => false,
            'id' => false,
            'message' => 'Failed to submit application: ' . $e->getMessage()
        ];
    }
}

/**
 * Update application status
 * @param int $id
 * @param string $status
 * @param string|null $notes
 * @param int|null $changedBy - Admin user ID
 * @return array
 */
function updateApplicationStatus($id, $status, $notes = null, $changedBy = null) {
    try {
        $pdo = getCareersDB();
        // Get current status
        $application = getApplication($id);
        if (!$application) {
            return ['success' => false, 'message' => 'Application not found'];
        }
        
        $oldStatus = $application['status'];
        
        // Validate status
        $validStatuses = ['pending', 'reviewed', 'shortlisted', 'interview', 'rejected', 'hired'];
        if (!in_array($status, $validStatuses)) {
            return ['success' => false, 'message' => 'Invalid status'];
        }
        
        // Update application
        $stmt = $pdo->prepare("UPDATE applications SET status = :status WHERE id = :id");
        $stmt->execute([':status' => $status, ':id' => $id]);
        
        // Record status change in history
        $stmt = $pdo->prepare("INSERT INTO application_status_history 
                              (application_id, old_status, new_status, changed_by, notes) 
                              VALUES (:application_id, :old_status, :new_status, :changed_by, :notes)");
        $stmt->execute([
            ':application_id' => $id,
            ':old_status' => $oldStatus,
            ':new_status' => $status,
            ':changed_by' => $changedBy,
            ':notes' => $notes
        ]);
        
        // Create notification if status changed
        if ($oldStatus !== $status) {
            createNotification(
                'application_status_change',
                "Application Status Changed",
                "Application #{$id} status changed from {$oldStatus} to {$status}",
                $changedBy
            );
        }
        
        return [
            'success' => true,
            'message' => 'Application status updated successfully'
        ];
    } catch (PDOException $e) {
        error_log("Error updating application status: " . $e->getMessage());
        return [
            'success' => false,
            'message' => 'Failed to update status: ' . $e->getMessage()
        ];
    }
}

/**
 * Get application status history
 * @param int $applicationId
 * @return array
 */
function getApplicationHistory($applicationId) {
    try {
        $pdo = getCareersDB();
        $stmt = $pdo->prepare("SELECT h.*, u.username as changed_by_name 
                              FROM application_status_history h 
                              LEFT JOIN admin_users u ON h.changed_by = u.id 
                              WHERE h.application_id = :id 
                              ORDER BY h.change_date DESC");
        $stmt->execute([':id' => $applicationId]);
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting application history: " . $e->getMessage());
        return [];
    }
}

/**
 * Get career dashboard statistics
 * @return array
 */
function getCareerStats() {
    try {
        $pdo = getCareersDB();
        $stats = [];
        
        // Total jobs
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total_jobs,
            SUM(CASE WHEN status = 'active' THEN 1 ELSE 0 END) as active_jobs,
            SUM(CASE WHEN status = 'inactive' THEN 1 ELSE 0 END) as inactive_jobs,
            SUM(CASE WHEN status = 'closed' THEN 1 ELSE 0 END) as closed_jobs
            FROM jobs");
        $stats['jobs'] = $stmt->fetch();
        
        // Total applications
        $stmt = $pdo->query("SELECT 
            COUNT(*) as total_applications,
            SUM(CASE WHEN status = 'pending' THEN 1 ELSE 0 END) as pending_applications,
            SUM(CASE WHEN status = 'reviewed' THEN 1 ELSE 0 END) as reviewed_applications,
            SUM(CASE WHEN status = 'shortlisted' THEN 1 ELSE 0 END) as shortlisted_applications,
            SUM(CASE WHEN status = 'interview' THEN 1 ELSE 0 END) as interview_applications,
            SUM(CASE WHEN status = 'rejected' THEN 1 ELSE 0 END) as rejected_applications,
            SUM(CASE WHEN status = 'hired' THEN 1 ELSE 0 END) as hired_applications
            FROM applications");
        $stats['applications'] = $stmt->fetch();
        
        // Recent applications (last 7 days)
        $stmt = $pdo->query("SELECT COUNT(*) as recent_applications 
                            FROM applications 
                            WHERE applied_date >= DATE_SUB(NOW(), INTERVAL 7 DAY)");
        $stats['recent_applications'] = $stmt->fetch()['recent_applications'];
        
        // Upcoming deadlines (next 7 days)
        $stmt = $pdo->query("SELECT COUNT(*) as upcoming_deadlines 
                            FROM jobs 
                            WHERE application_deadline BETWEEN CURDATE() AND DATE_ADD(CURDATE(), INTERVAL 7 DAY)
                            AND status = 'active'");
        $stats['upcoming_deadlines'] = $stmt->fetch()['upcoming_deadlines'];
        
        return $stats;
    } catch (PDOException $e) {
        error_log("Error getting career stats: " . $e->getMessage());
        return [];
    }
}

/**
 * Upload resume file
 * @param array $file - $_FILES['resume'] array
 * @return array
 */
function uploadResume($file) {
    // Validate file
    $allowedTypes = ['application/pdf', 'application/msword', 'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
    $allowedExtensions = ['pdf', 'doc', 'docx'];
    $maxSize = 5 * 1024 * 1024; // 5MB
    
    // Check file error
    if ($file['error'] !== UPLOAD_ERR_OK) {
        return ['success' => false, 'message' => 'File upload error'];
    }
    
    // Check file size
    if ($file['size'] > $maxSize) {
        return ['success' => false, 'message' => 'File size exceeds 5MB limit'];
    }
    
    // Check file type
    $fileExtension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));
    $fileMimeType = mime_content_type($file['tmp_name']);
    
    if (!in_array($fileExtension, $allowedExtensions) || !in_array($fileMimeType, $allowedTypes)) {
        return ['success' => false, 'message' => 'Invalid file type. Only PDF, DOC, and DOCX files are allowed'];
    }
    
    // Create upload directory
    $uploadDir = __DIR__ . '/../uploads/resumes/';
    if (!file_exists($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }
    
    // Generate unique filename
    $filename = 'resume_' . time() . '_' . uniqid() . '.' . $fileExtension;
    $filepath = $uploadDir . $filename;
    
    // Move uploaded file
    if (!move_uploaded_file($file['tmp_name'], $filepath)) {
        return ['success' => false, 'message' => 'Failed to upload file'];
    }
    
    return [
        'success' => true,
        'path' => 'uploads/resumes/' . $filename,
        'message' => 'Resume uploaded successfully'
    ];
}

/**
 * Send application notification to admins
 * @param int $applicationId
 * @return bool
 */
function sendApplicationNotification($applicationId) {
    try {
        $pdo = getCareersDB();
        $application = getApplication($applicationId);
        if (!$application) {
            return false;
        }
        
        // Get all admin users - try admin_users first, then admins table
        $admins = [];
        try {
            // Check if admin_users table exists and has records
            $stmt = $pdo->query("SELECT id FROM admin_users LIMIT 1");
            $stmt->fetch();
            // If we get here, table exists, get all admins
            $stmt = $pdo->query("SELECT id FROM admin_users");
            $admins = $stmt->fetchAll();
        } catch (PDOException $e) {
            // admin_users doesn't exist, try admins table
            try {
                $stmt = $pdo->query("SELECT id FROM admins WHERE is_active = 1");
                $admins = $stmt->fetchAll();
            } catch (PDOException $e2) {
                // If admins table also doesn't work, create notification without recipient_id (for all admins)
                error_log("Could not find admin_users or admins table: " . $e2->getMessage());
            }
        }
        
        // Create notification for each admin, or one general notification if no admins found
        if (!empty($admins)) {
            foreach ($admins as $admin) {
                createNotification(
                    'new_application',
                    "New Job Application",
                    "New application received for {$application['job_title']} from {$application['applicant_name']}",
                    $admin['id']
                );
            }
        } else {
            // Create a general notification without specific recipient (will show to all admins)
            createNotification(
                'new_application',
                "New Job Application",
                "New application received for {$application['job_title']} from {$application['applicant_name']}",
                null
            );
        }
        
        return true;
    } catch (PDOException $e) {
        error_log("Error sending notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Create a notification
 * @param string $type
 * @param string $title
 * @param string $message
 * @param int|null $recipientId
 * @return bool
 */
function createNotification($type, $title, $message, $recipientId = null) {
    try {
        $pdo = getCareersDB();
        
        // Ensure career_notifications table exists
        $stmt = $pdo->query("SHOW TABLES LIKE 'career_notifications'");
        if ($stmt->rowCount() == 0) {
            // Create the table
            $createTableSql = "CREATE TABLE IF NOT EXISTS `career_notifications` (
                `id` INT(11) NOT NULL AUTO_INCREMENT,
                `type` ENUM('new_application', 'application_status_change', 'job_deadline', 'interview_scheduled') NOT NULL,
                `title` VARCHAR(255) NOT NULL,
                `message` TEXT NOT NULL,
                `recipient_id` INT(11) DEFAULT NULL,
                `is_read` TINYINT(1) NOT NULL DEFAULT 0,
                `created_at` TIMESTAMP NOT NULL DEFAULT CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                INDEX `idx_recipient` (`recipient_id`),
                INDEX `idx_is_read` (`is_read`),
                INDEX `idx_type` (`type`),
                INDEX `idx_created_at` (`created_at`)
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci";
            $pdo->exec($createTableSql);
        }
        
        $stmt = $pdo->prepare("INSERT INTO career_notifications (type, title, message, recipient_id) 
                              VALUES (:type, :title, :message, :recipient_id)");
        $stmt->execute([
            ':type' => $type,
            ':title' => $title,
            ':message' => $message,
            ':recipient_id' => $recipientId
        ]);
        return true;
    } catch (PDOException $e) {
        error_log("Error creating notification: " . $e->getMessage());
        return false;
    }
}

/**
 * Get notifications for a user
 * @param int $userId
 * @param bool $unreadOnly
 * @param int $limit
 * @return array
 */
function getNotifications($userId = null, $unreadOnly = false, $limit = 50) {
    try {
        $pdo = getCareersDB();
        $whereConditions = [];
        $params = [];
        
        // Always get notifications for the user AND general notifications (recipient_id IS NULL)
        // This ensures all admins see notifications created for "all admins"
        if ($userId !== null) {
            $whereConditions[] = "(recipient_id = :user_id OR recipient_id IS NULL)";
            $params[':user_id'] = $userId;
        } else {
            // If no user ID, get all general notifications (recipient_id IS NULL)
            $whereConditions[] = "recipient_id IS NULL";
        }
        
        if ($unreadOnly) {
            $whereConditions[] = "is_read = 0";
        }
        
        $whereClause = !empty($whereConditions) ? 'WHERE ' . implode(' AND ', $whereConditions) : '';
        
        $sql = "SELECT * FROM career_notifications 
                {$whereClause} 
                ORDER BY created_at DESC 
                LIMIT :limit";
        
        $stmt = $pdo->prepare($sql);
        $stmt->bindValue(':limit', $limit, PDO::PARAM_INT);
        $stmt->execute($params);
        
        return $stmt->fetchAll();
    } catch (PDOException $e) {
        error_log("Error getting notifications: " . $e->getMessage());
        // If table doesn't exist, return empty array
        if (strpos($e->getMessage(), "doesn't exist") !== false) {
            return [];
        }
        throw $e;
    }
}

/**
 * Mark notification as read
 * @param int $notificationId
 * @return bool
 */
function markNotificationRead($notificationId) {
    try {
        $pdo = getCareersDB();
        $stmt = $pdo->prepare("UPDATE career_notifications SET is_read = 1 WHERE id = :id");
        $stmt->execute([':id' => $notificationId]);
        return true;
    } catch (PDOException $e) {
        error_log("Error marking notification as read: " . $e->getMessage());
        return false;
    }
}

/**
 * Rate limiting check for application submissions
 * @param string $email
 * @return array - ['allowed' => bool, 'message' => string]
 */
function checkApplicationRateLimit($email) {
    try {
        $pdo = getCareersDB();
        // Check applications in last 24 hours
        $stmt = $pdo->prepare("SELECT COUNT(*) as count 
                              FROM applications 
                              WHERE applicant_email = :email 
                              AND applied_date >= DATE_SUB(NOW(), INTERVAL 24 HOUR)");
        $stmt->execute([':email' => $email]);
        $result = $stmt->fetch();
        
        $maxApplications = 3; // Max 3 applications per 24 hours
        
        if ($result['count'] >= $maxApplications) {
            return [
                'allowed' => false,
                'message' => 'You have reached the maximum number of applications allowed per day. Please try again tomorrow.'
            ];
        }
        
        return ['allowed' => true, 'message' => ''];
    } catch (PDOException $e) {
        error_log("Error checking rate limit: " . $e->getMessage());
        // Allow on error to not block legitimate users
        return ['allowed' => true, 'message' => ''];
    }
}

/**
 * Get resume file path securely (for admin download)
 * @param int $applicationId
 * @return array - ['success' => bool, 'path' => string, 'filename' => string]
 */
function getResumePath($applicationId) {
    try {
        $pdo = getCareersDB();
        $stmt = $pdo->prepare("SELECT resume_path, applicant_name FROM applications WHERE id = :id");
        $stmt->execute([':id' => $applicationId]);
        $application = $stmt->fetch();
        
        if (!$application || empty($application['resume_path'])) {
            return ['success' => false, 'message' => 'Resume not found'];
        }
        
        $resumePath = $application['resume_path'];
        $fullPath = __DIR__ . '/../' . $resumePath;
        
        // Security check - ensure file exists and is within uploads/resumes directory
        if (!file_exists($fullPath)) {
            return ['success' => false, 'message' => 'Resume file not found on server'];
        }
        
        // Ensure path is within uploads/resumes directory (prevent directory traversal)
        $realPath = realpath($fullPath);
        $basePath = realpath(__DIR__ . '/../uploads/resumes');
        
        if (strpos($realPath, $basePath) !== 0) {
            return ['success' => false, 'message' => 'Invalid resume path'];
        }
        
        // Generate safe filename
        $extension = pathinfo($resumePath, PATHINFO_EXTENSION);
        $safeName = preg_replace('/[^a-zA-Z0-9_-]/', '_', $application['applicant_name']) . '_resume.' . $extension;
        
        return [
            'success' => true,
            'path' => $fullPath,
            'filename' => $safeName,
            'mime_type' => mime_content_type($fullPath)
        ];
    } catch (PDOException $e) {
        error_log("Error getting resume path: " . $e->getMessage());
        return ['success' => false, 'message' => 'Database error'];
    }
}

/**
 * Get list of unique departments from jobs
 * @return array
 */
function getDepartments() {
    try {
        $pdo = getCareersDB();
        $stmt = $pdo->query("SELECT DISTINCT department FROM jobs WHERE department IS NOT NULL AND department != '' ORDER BY department");
        $departments = $stmt->fetchAll(PDO::FETCH_COLUMN);
        return $departments;
    } catch (PDOException $e) {
        error_log("Error getting departments: " . $e->getMessage());
        return [];
    }
}

/**
 * Validate and sanitize input data
 * @param array $data
 * @param array $rules - ['field' => ['required' => bool, 'type' => 'string|int|email', 'max_length' => int]]
 * @return array - ['valid' => bool, 'errors' => array, 'sanitized' => array]
 */
function validateApplicationData($data, $rules = []) {
    $errors = [];
    $sanitized = [];
    
    foreach ($rules as $field => $rule) {
        $value = $data[$field] ?? null;
        
        // Check required
        if (!empty($rule['required']) && (empty($value) && $value !== '0')) {
            $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' is required';
            continue;
        }
        
        // Skip validation if field is empty and not required
        if (empty($value) && empty($rule['required'])) {
            $sanitized[$field] = null;
            continue;
        }
        
        // Type validation
        if (!empty($rule['type'])) {
            switch ($rule['type']) {
                case 'email':
                    if (!filter_var($value, FILTER_VALIDATE_EMAIL)) {
                        $errors[$field] = 'Invalid email address';
                    } else {
                        $sanitized[$field] = filter_var($value, FILTER_SANITIZE_EMAIL);
                    }
                    break;
                    
                case 'int':
                    $sanitized[$field] = intval($value);
                    break;
                    
                case 'string':
                default:
                    $sanitized[$field] = trim(strip_tags($value));
                    if (!empty($rule['max_length']) && strlen($sanitized[$field]) > $rule['max_length']) {
                        $errors[$field] = ucfirst(str_replace('_', ' ', $field)) . ' exceeds maximum length';
                    }
                    break;
            }
        } else {
            $sanitized[$field] = trim(strip_tags($value));
        }
    }
    
    return [
        'valid' => empty($errors),
        'errors' => $errors,
        'sanitized' => $sanitized
    ];
}
