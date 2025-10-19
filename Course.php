<?php
require_once "Database.php";

class Course {
    private $db;

    public function __construct() {
        $this->db = new Database();
    }


    public function createCourse($title, $description, $category, $price, $imageFile) {
        // Handle file upload
        $targetDir = "uploads/courses/";
        if (!file_exists($targetDir)) {
            mkdir($targetDir, 0777, true);
        }
        $imageName = time() . '_' . basename($imageFile["name"]);
        $targetFile = $targetDir . $imageName;

        if (move_uploaded_file($imageFile["tmp_name"], $targetFile)) {
            // Updated SQL to include the price column
            $stmt = $this->db->conn->prepare(
                "INSERT INTO courses (title, description, category, price, image) VALUES (?, ?, ?, ?, ?)"
            );
            // Execute with all five parameters
            if ($stmt->execute([$title, $description, $category, $price, $targetFile])) {
                return ["success" => true, "message" => "Course created successfully"];
            }
        }
        return ["success" => false, "message" => "Failed to create course or upload image"];
    }
    /**
     * Fetches all courses from the database.
     */
    public function getAllCourses() {
        $stmt = $this->db->conn->prepare("SELECT * FROM courses ORDER BY id DESC");
        $stmt->execute();
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Fetches a single course by its ID.
     */
    public function getCourseById($id) {
        $stmt = $this->db->conn->prepare("SELECT * FROM courses WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    /**
     * Adds a new chapter to a specific course.
     * MODIFIED: This method now accepts and saves the sort order.
     */
    public function addChapter($course_id, $title, $sort_order) {
        // Updated SQL query to include the sort_order column
        $stmt = $this->db->conn->prepare(
            "INSERT INTO chapters (course_id, title, sort_order) VALUES (?, ?, ?)"
        );
        // Execute with all three parameters
        if ($stmt->execute([$course_id, $title, $sort_order])) {
            return ["success" => true, "message" => "Chapter added successfully"];
        }
        return ["success" => false, "message" => "Failed to add chapter"];
    }

    /**
     * Gets all chapters for a specific course, ordered by sort_order.
     */
    public function getChaptersByCourseId($course_id) {
        $stmt = $this->db->conn->prepare("SELECT * FROM chapters WHERE course_id = ? ORDER BY sort_order ASC, id ASC");
        $stmt->execute([$course_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Adds content (video or PDF) to a chapter.
     */
    public function addContent($chapter_id, $title, $contentType, $contentData) {
        $contentPath = "";
        if ($contentType === 'youtube') {
            $contentPath = $contentData; // Directly use the URL
        } elseif ($contentType === 'pdf') {
            // Handle PDF upload
            $targetDir = "uploads/pdfs/";
            if (!file_exists($targetDir)) {
                mkdir($targetDir, 0777, true);
            }
            $fileName = time() . '_' . basename($contentData["name"]);
            $targetFile = $targetDir . $fileName;

            if (!move_uploaded_file($contentData["tmp_name"], $targetFile)) {
                return ["success" => false, "message" => "Failed to upload PDF"];
            }
            $contentPath = $targetFile;
        }

        $stmt = $this->db->conn->prepare(
            "INSERT INTO content (chapter_id, title, content_type, content_path) VALUES (?, ?, ?, ?)"
        );
        if ($stmt->execute([$chapter_id, $title, $contentType, $contentPath])) {
            return ["success" => true, "message" => "Content added successfully"];
        }
        return ["success" => false, "message" => "Failed to add content"];
    }

    /**
     * Gets all content for a specific chapter.
     */
    public function getContentByChapterId($chapter_id) {
        $stmt = $this->db->conn->prepare("SELECT * FROM content WHERE chapter_id = ? ORDER BY id ASC");
        $stmt->execute([$chapter_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }
    /**
     * Updates an existing course's details.
     * Handles optional new image upload.
     */
    public function updateCourse($id, $title, $description, $category, $price, $imageFile) {
        $imagePath = $this->getCourseById($id)['image']; // Get existing image path

        // Check if a new image was uploaded
        if (isset($imageFile) && $imageFile['error'] == 0) {
            $targetDir = "uploads/courses/";
            // Optionally delete the old image to save space
            if (file_exists($imagePath)) {
                unlink($imagePath);
            }
            // Upload the new image
            $imageName = time() . '_' . basename($imageFile["name"]);
            $imagePath = $targetDir . $imageName;
            if (!move_uploaded_file($imageFile["tmp_name"], $imagePath)) {
                return ["success" => false, "message" => "Failed to upload new image."];
            }
        }

        $stmt = $this->db->conn->prepare(
            "UPDATE courses SET title = ?, description = ?, category = ?, price = ?, image = ? WHERE id = ?"
        );
        
        if ($stmt->execute([$title, $description, $category, $price, $imagePath, $id])) {
            return ["success" => true, "message" => "Course updated successfully"];
        }
        return ["success" => false, "message" => "Failed to update course"];
    }
    /**
     * Enrolls a user in multiple courses.
     */
    public function enrollUser($user_id, $course_ids, $payment_id) {
        $this->db->conn->beginTransaction();
        try {
            $stmt = $this->db->conn->prepare(
                "INSERT INTO enrollments (user_id, course_id, payment_id) VALUES (?, ?, ?)"
            );
            foreach ($course_ids as $course_id) {
                $stmt->execute([$user_id, $course_id, $payment_id]);
            }
            $this->db->conn->commit();
            return ["success" => true];
        } catch (Exception $e) {
            $this->db->conn->rollBack();
            return ["success" => false, "message" => "Enrollment failed: " . $e->getMessage()];
        }
    }

    /**
     * Fetches all courses a user is enrolled in.
     */
    public function getUserCourses($user_id) {
        $stmt = $this->db->conn->prepare(
            "SELECT c.* FROM courses c JOIN enrollments e ON c.id = e.course_id WHERE e.user_id = ?"
        );
        $stmt->execute([$user_id]);
        return $stmt->fetchAll(PDO::FETCH_ASSOC);
    }

    /**
     * Checks if a user is already enrolled in a specific course.
     */
    public function isUserEnrolled($user_id, $course_id) {
        $stmt = $this->db->conn->prepare(
            "SELECT id FROM enrollments WHERE user_id = ? AND course_id = ?"
        );
        $stmt->execute([$user_id, $course_id]);
        return $stmt->rowCount() > 0;
    }
    // --- Methods for Chapters ---
    public function getChapterById($id) {
        $stmt = $this->db->conn->prepare("SELECT * FROM chapters WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public function updateChapter($id, $title, $sort_order) {
        $stmt = $this->db->conn->prepare("UPDATE chapters SET title = ?, sort_order = ? WHERE id = ?");
        if ($stmt->execute([$title, $sort_order, $id])) {
            return ["success" => true, "message" => "Chapter updated successfully"];
        }
        return ["success" => false, "message" => "Failed to update chapter"];
    }
    
    public function deleteChapter($id) {
        $stmt = $this->db->conn->prepare("DELETE FROM chapters WHERE id = ?");
        if ($stmt->execute([$id])) {
            return ["success" => true, "message" => "Chapter deleted successfully"];
        }
        return ["success" => false, "message" => "Failed to delete chapter"];
    }

    // --- Methods for Content ---
    public function getContentById($id) {
        $stmt = $this->db->conn->prepare("SELECT * FROM content WHERE id = ?");
        $stmt->execute([$id]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
    
    public function deleteContent($id) {
        // Before deleting from DB, delete the associated file if it's a PDF
        $content = $this->getContentById($id);
        if ($content && $content['content_type'] === 'pdf' && file_exists($content['content_path'])) {
            unlink($content['content_path']);
        }

        $stmt = $this->db->conn->prepare("DELETE FROM content WHERE id = ?");
        if ($stmt->execute([$id])) {
            return ["success" => true, "message" => "Content deleted successfully"];
        }
        return ["success" => false, "message" => "Failed to delete content"];
    }
    /**
     * Marks a content item as completed for a user.
     * Uses INSERT IGNORE to prevent errors if the record already exists.
     */
    public function markContentAsCompleted($user_id, $content_id) {
        $stmt = $this->db->conn->prepare(
            "INSERT IGNORE INTO user_progress (user_id, content_id) VALUES (?, ?)"
        );
        if ($stmt->execute([$user_id, $content_id])) {
            return ["success" => true];
        }
        return ["success" => false, "message" => "Could not mark as complete."];
    }

    /**
     * Calculates a user's progress for a specific course.
     * Returns the percentage of completed lessons.
     */
    public function getCourseProgress($user_id, $course_id) {
        // First, count the total number of content items in the course
        $stmt_total = $this->db->conn->prepare(
            "SELECT COUNT(*) FROM content c JOIN chapters ch ON c.chapter_id = ch.id WHERE ch.course_id = ?"
        );
        $stmt_total->execute([$course_id]);
        $total_count = $stmt_total->fetchColumn();

        if ($total_count == 0) {
            return 100; // If a course has no content, it's considered complete.
        }

        // Next, count how many of those items the user has completed
        $stmt_completed = $this->db->conn->prepare(
            "SELECT COUNT(*) FROM user_progress up
             JOIN content c ON up.content_id = c.id
             JOIN chapters ch ON c.chapter_id = ch.id
             WHERE up.user_id = ? AND ch.course_id = ?"
        );
        $stmt_completed->execute([$user_id, $course_id]);
        $completed_count = $stmt_completed->fetchColumn();
        
        // Calculate and return the percentage
        return ($completed_count / $total_count) * 100;
    }
    /**
     * Generates or retrieves a unique certificate token for an enrollment.
     * If a token doesn't exist, it creates one.
     */
    public function generateCertificateToken($user_id, $course_id) {
        $stmt_check = $this->db->conn->prepare(
            "SELECT certificate_token FROM enrollments WHERE user_id = ? AND course_id = ?"
        );
        $stmt_check->execute([$user_id, $course_id]);
        $existing_token = $stmt_check->fetchColumn();

        if ($existing_token) {
            return $existing_token; // Return the token if it already exists
        }

        // Generate a new, unique token (UUID version 4)
        $token = sprintf('%04x%04x-%04x-%04x-%04x-%04x%04x%04x',
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff),
            mt_rand(0, 0x0fff) | 0x4000, mt_rand(0, 0x3fff) | 0x8000,
            mt_rand(0, 0xffff), mt_rand(0, 0xffff), mt_rand(0, 0xffff)
        );

        $stmt_update = $this->db->conn->prepare(
            "UPDATE enrollments SET certificate_token = ? WHERE user_id = ? AND course_id = ?"
        );
        
        if ($stmt_update->execute([$token, $user_id, $course_id])) {
            return $token;
        }
        return null; // Return null on failure
    }

    /**
     * Fetches certificate details (user, course) based on a certificate token.
     * This is used for the public-facing certificate page and requires no login.
     */
    public function getEnrollmentByToken($token) {
        $stmt = $this->db->conn->prepare(
            "SELECT u.name AS user_name, c.title AS course_title, e.enrollment_date
             FROM enrollments e
             JOIN users u ON e.user_id = u.id
             JOIN courses c ON e.course_id = c.id
             WHERE e.certificate_token = ?"
        );
        $stmt->execute([$token]);
        return $stmt->fetch(PDO::FETCH_ASSOC);
    }
}

?>