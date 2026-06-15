<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/database.php';

function map_course_row(array $row): array
{
    return [
        'id' => (int) $row['id'],
        'title' => $row['title'],
        'teacher' => $row['teacher_name'] ?? '',
        'teacher_id' => (int) ($row['teacher_id'] ?? 0),
        'price' => (float) $row['price'],
        'rating' => (float) $row['rating'],
        'category' => $row['category_name'] ?? '',
        'category_id' => (int) ($row['category_id'] ?? 0),
        'thumb' => $row['thumb'] ?: 'assets/images/avatars/placeholder.svg',
        'students' => (int) ($row['student_count'] ?? 0),
        'desc' => $row['description'] ?? '',
        'status' => $row['status'] ?? 'published',
        'submitted' => isset($row['created_at']) ? date('Y-m-d', strtotime($row['created_at'])) : '',
        'enrollments' => (int) ($row['student_count'] ?? 0),
    ];
}

function courses_base_sql(string $where = '1=1'): string
{
    return "SELECT c.*, u.name AS teacher_name, cat.name AS category_name,
            (SELECT COUNT(*) FROM enrollments e WHERE e.course_id = c.id) AS student_count
            FROM courses c
            JOIN users u ON u.id = c.teacher_id
            JOIN categories cat ON cat.id = c.category_id
            WHERE $where";
}

function getCourses(bool $publishedOnly = true): array
{
    if (!db_available()) {
        return fallbackCourses();
    }
    $where = $publishedOnly ? "c.status = 'published'" : '1=1';
    $stmt = db()->query(courses_base_sql($where) . ' ORDER BY c.created_at DESC');
    return array_map('map_course_row', $stmt->fetchAll());
}

function getCourseById(int $id): ?array
{
    if (!db_available()) {
        foreach (fallbackCourses() as $c) {
            if ($c['id'] === $id) {
                return $c;
            }
        }
        return null;
    }
    $stmt = db()->prepare(courses_base_sql('c.id = ?'));
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ? map_course_row($row) : null;
}

function getTeachers(bool $verifiedOnly = true): array
{
    if (!db_available()) {
        return fallbackTeachers();
    }
    $where = $verifiedOnly
        ? "u.role = 'teacher' AND tp.verification_status = 'verified' AND u.status = 'active'"
        : "u.role = 'teacher'";
    $sql = "SELECT u.id, u.name, u.email, u.bio, u.avatar AS photo, tp.qualification, tp.experience,
            tp.subject, tp.rating,
            (SELECT COUNT(DISTINCT e.student_id) FROM enrollments e
             JOIN courses c ON c.id = e.course_id WHERE c.teacher_id = u.id) AS students
            FROM users u
            LEFT JOIN teacher_profiles tp ON tp.user_id = u.id
            WHERE $where
            ORDER BY tp.rating DESC";
    $stmt = db()->query($sql);
    return array_map(static function ($row) {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'email' => $row['email'] ?? '',
            'qualification' => $row['qualification'] ?? '',
            'experience' => $row['experience'] ?? '',
            'rating' => (float) ($row['rating'] ?? 0),
            'subject' => $row['subject'] ?? '',
            'photo' => $row['photo'] ?? '',
            'students' => (int) ($row['students'] ?? 0),
            'bio' => $row['bio'] ?? '',
        ];
    }, $stmt->fetchAll());
}

function getTeacherById(int $id): ?array
{
    foreach (getTeachers(false) as $t) {
        if ($t['id'] === $id) {
            return $t;
        }
    }
    return null;
}

function getCategories(): array
{
    if (!db_available()) {
        return ['Development', 'Design', 'Business', 'Marketing', 'Data Science'];
    }
    return db()->query('SELECT id, name, slug FROM categories ORDER BY name')->fetchAll();
}

function getCategoriesWithCourses(): array
{
    if (!db_available()) {
        return ['Development', 'Design', 'Business', 'Marketing', 'Data Science'];
    }
    $stmt = db()->query("
        SELECT DISTINCT cat.id, cat.name, cat.slug 
        FROM categories cat
        INNER JOIN courses c ON c.category_id = cat.id
        WHERE c.status = 'published'
        ORDER BY cat.name
    ");
    $categories = $stmt->fetchAll();
    if (empty($categories)) {
        return db()->query('SELECT id, name, slug FROM categories ORDER BY name LIMIT 8')->fetchAll();
    }
    return $categories;
}

function getStudents(): array
{
    if (!db_available()) {
        return fallbackStudents();
    }
    $sql = "SELECT u.*, (SELECT COUNT(*) FROM enrollments e WHERE e.student_id = u.id) AS courses
            FROM users u WHERE u.role = 'student' ORDER BY u.created_at DESC";
    $stmt = db()->query($sql);
    return array_map(static function ($row) {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? '',
            'joined' => date('Y-m-d', strtotime($row['created_at'])),
            'status' => $row['status'],
            'courses' => (int) $row['courses'],
        ];
    }, $stmt->fetchAll());
}

function getPendingVerifications(): array
{
    if (!db_available()) {
        return fallbackPendingVerifications();
    }
    $sql = "SELECT u.id, u.name, u.email, tp.qualification, tp.cnic, tp.created_at AS submitted
            FROM teacher_profiles tp
            JOIN users u ON u.id = tp.user_id
            WHERE tp.verification_status = 'pending'
            ORDER BY tp.created_at DESC";
    $stmt = db()->query($sql);
    return array_map(static function ($row) {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'qualification' => $row['qualification'],
            'cnic' => $row['cnic'],
            'submitted' => date('Y-m-d', strtotime($row['submitted'])),
            'status' => 'pending',
        ];
    }, $stmt->fetchAll());
}

function getAdminCourses(): array
{
    if (!db_available()) {
        return fallbackAdminCourses();
    }
    $stmt = db()->query(courses_base_sql('1=1') . ' ORDER BY c.created_at DESC');
    return array_map(static function ($row) {
        $c = map_course_row($row);
        $c['status'] = $row['status'] === 'published' ? 'approved' : ($row['status'] === 'pending' ? 'pending' : $row['status']);
        return $c;
    }, $stmt->fetchAll());
}

function getPayments(): array
{
    if (!db_available()) {
        return fallbackPayments();
    }
    $sql = "SELECT p.*, u.name AS student_name, c.title AS course_title
            FROM payments p
            JOIN users u ON u.id = p.student_id
            JOIN courses c ON c.id = p.course_id
            ORDER BY p.created_at DESC";
    $stmt = db()->query($sql);
    return array_map(static function ($row) {
        return [
            'id' => $row['reference'],
            'student' => $row['student_name'],
            'course' => $row['course_title'],
            'amount' => (float) $row['amount'],
            'method' => $row['method'],
            'date' => date('Y-m-d H:i', strtotime($row['created_at'])),
            'status' => $row['status'],
            'payment_id' => (int) $row['id'],
        ];
    }, $stmt->fetchAll());
}

function getAdminStats(): array
{
    if (!db_available()) {
        return fallbackAdminStats();
    }
    $pdo = db();
    $totalUsers = (int) $pdo->query("SELECT COUNT(*) FROM users")->fetchColumn();
    $students = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='student'")->fetchColumn();
    $teachers = (int) $pdo->query("SELECT COUNT(*) FROM users WHERE role='teacher' AND status='active'")->fetchColumn();
    $pendingVerifications = (int) $pdo->query("SELECT COUNT(*) FROM teacher_profiles WHERE verification_status='pending'")->fetchColumn();
    $totalCourses = (int) $pdo->query("SELECT COUNT(*) FROM courses")->fetchColumn();
    $pendingCourses = (int) $pdo->query("SELECT COUNT(*) FROM courses WHERE status='pending'")->fetchColumn();
    $revenueMonth = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed' AND MONTH(created_at)=MONTH(CURRENT_DATE()) AND YEAR(created_at)=YEAR(CURRENT_DATE())")->fetchColumn();
    $revenueTotal = (float) $pdo->query("SELECT COALESCE(SUM(amount),0) FROM payments WHERE status='completed'")->fetchColumn();
    $enrollmentsMonth = (int) $pdo->query("SELECT COUNT(*) FROM enrollments WHERE MONTH(enrolled_at)=MONTH(CURRENT_DATE()) AND YEAR(enrolled_at)=YEAR(CURRENT_DATE())")->fetchColumn();
    $activeStudents = (int) $pdo->query("SELECT COUNT(DISTINCT student_id) FROM enrollments WHERE status='active'")->fetchColumn();

    return [
        'total_users' => $totalUsers,
        'students' => $students,
        'teachers' => $teachers,
        'pending_verifications' => $pendingVerifications,
        'total_courses' => $totalCourses,
        'pending_courses' => $pendingCourses,
        'revenue_month' => $revenueMonth,
        'revenue_total' => $revenueTotal,
        'enrollments_month' => $enrollmentsMonth,
        'active_students' => $activeStudents,
    ];
}

function getUserById(int $id): ?array
{
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ?');
    $stmt->execute([$id]);
    $row = $stmt->fetch();
    return $row ?: null;
}

function getCurrentStudent(): array
{
    $user = auth_user();
    if (!$user || !db_available()) {
        return fallbackCurrentStudent();
    }
    $stmt = db()->prepare('SELECT * FROM users WHERE id = ? LIMIT 1');
    $stmt->execute([(int) $user['id']]);
    $row = $stmt->fetch();
    if (!$row) {
        return fallbackCurrentStudent();
    }
    return [
        'id' => (int) $row['id'],
        'name' => $row['name'],
        'email' => $row['email'],
        'phone' => $row['phone'] ?? '',
        'joined' => date('Y-m-d', strtotime($row['created_at'] ?? 'now')),
        'bio' => $row['bio'] ?? '',
        'avatar' => $row['avatar'] ?? '',
    ];
}

function getStudentEnrollments(?int $studentId = null): array
{
    $studentId = $studentId ?? auth_id();
    if (!$studentId || !db_available()) {
        return fallbackStudentEnrollments();
    }
    $sql = "SELECT e.*, c.id AS course_id, c.title, c.price, c.rating, c.thumb, c.description,
            u.name AS teacher_name, cat.name AS category_name
            FROM enrollments e
            JOIN courses c ON c.id = e.course_id
            JOIN users u ON u.id = c.teacher_id
            JOIN categories cat ON cat.id = c.category_id
            WHERE e.student_id = ?
            ORDER BY e.last_access DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute([$studentId]);
    return array_map(static function ($row) {
        return [
            'id' => (int) $row['course_id'],
            'course_id' => (int) $row['course_id'],
            'title' => $row['title'],
            'teacher' => $row['teacher_name'],
            'price' => (float) $row['price'],
            'rating' => (float) $row['rating'],
            'category' => $row['category_name'],
            'thumb' => $row['thumb'] ?: 'assets/images/avatars/placeholder.svg',
            'desc' => $row['description'],
            'progress' => (int) $row['progress'],
            'status' => $row['status'],
            'last_access' => $row['last_access'] ? date('Y-m-d', strtotime($row['last_access'])) : '',
        ];
    }, $stmt->fetchAll());
}

function getStudentPurchases(?int $studentId = null): array
{
    $studentId = $studentId ?? auth_id();
    if (!$studentId || !db_available()) {
        return fallbackStudentPurchases();
    }
    $sql = "SELECT p.reference, p.amount, p.method, p.status, p.created_at, c.title AS course_title
            FROM payments p JOIN courses c ON c.id = p.course_id
            WHERE p.student_id = ? ORDER BY p.created_at DESC";
    $stmt = db()->prepare($sql);
    $stmt->execute([$studentId]);
    return array_map(static function ($row) {
        return [
            'id' => $row['reference'],
            'course' => $row['course_title'],
            'amount' => (float) $row['amount'],
            'method' => $row['method'],
            'date' => date('Y-m-d', strtotime($row['created_at'])),
            'status' => $row['status'],
        ];
    }, $stmt->fetchAll());
}

function getCourseLessons(int $courseId, ?int $studentId = null): array
{
    if (!db_available()) {
        return fallbackCourseLessons($courseId);
    }
    $stmt = db()->prepare('SELECT * FROM lessons WHERE course_id = ? ORDER BY sort_order');
    $stmt->execute([$courseId]);
    $lessons = $stmt->fetchAll();

    $completedIds = [];
    if ($studentId) {
        $stmt = db()->prepare('SELECT lp.lesson_id FROM lesson_progress lp
            JOIN enrollments e ON e.id = lp.enrollment_id
            WHERE e.student_id = ? AND e.course_id = ?');
        $stmt->execute([$studentId, $courseId]);
        $completedIds = array_column($stmt->fetchAll(), 'lesson_id');
    }

    return array_map(static function ($row) use ($completedIds) {
        return [
            'id' => (int) $row['id'],
            'title' => $row['title'],
            'duration' => $row['duration'],
            'content_url' => $row['content_url'] ?? '',
            'completed' => in_array($row['id'], $completedIds, true),
        ];
    }, $lessons);
}

function getCurrentTeacher(): array
{
    $user = auth_user();
    if (!$user || $user['role'] !== 'teacher' || !db_available()) {
        return fallbackCurrentTeacher();
    }
    $teachers = getTeachers(false);
    foreach ($teachers as $t) {
        if ($t['id'] === (int) $user['id']) {
            return $t;
        }
    }
    return fallbackCurrentTeacher();
}

function getTeacherCourses(?int $teacherId = null): array
{
    $teacherId = $teacherId ?? auth_id();
    if (!$teacherId || !db_available()) {
        return fallbackTeacherCourses();
    }
    $stmt = db()->prepare(courses_base_sql('c.teacher_id = ?') . ' ORDER BY c.created_at DESC');
    $stmt->execute([$teacherId]);
    return array_map(static function ($row) {
        $c = map_course_row($row);
        $status = $row['status'] === 'published' ? 'published' : ($row['status'] === 'draft' ? 'draft' : 'pending');
        $c['status'] = $status;
        $c['revenue'] = round($c['students'] * $c['price'] * 0.7, 2);
        return $c;
    }, $stmt->fetchAll());
}

function getTeacherEarnings(?int $teacherId = null): array
{
    $teacherId = $teacherId ?? auth_id();
    if (!$teacherId || !db_available()) {
        return fallbackTeacherEarnings();
    }
    $pdo = db();
    $stmt = $pdo->prepare("SELECT COALESCE(SUM(teacher_share),0) FROM payments p JOIN courses c ON c.id=p.course_id WHERE c.teacher_id=? AND p.status='completed' AND MONTH(p.created_at)=MONTH(CURRENT_DATE())");
    $stmt->execute([$teacherId]);
    $thisMonth = (float) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(teacher_share),0) FROM payments p JOIN courses c ON c.id=p.course_id WHERE c.teacher_id=? AND p.status='completed' AND MONTH(p.created_at)=MONTH(CURRENT_DATE() - INTERVAL 1 MONTH)");
    $stmt->execute([$teacherId]);
    $lastMonth = (float) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT COALESCE(SUM(teacher_share),0) FROM payments p JOIN courses c ON c.id=p.course_id WHERE c.teacher_id=? AND p.status='completed'");
    $stmt->execute([$teacherId]);
    $total = (float) $stmt->fetchColumn();

    $stmt = $pdo->prepare("SELECT p.created_at AS date, c.title AS course, u.name AS student, p.teacher_share AS amount
        FROM payments p JOIN courses c ON c.id=p.course_id JOIN users u ON u.id=p.student_id
        WHERE c.teacher_id=? AND p.status='completed' ORDER BY p.created_at DESC LIMIT 10");
    $stmt->execute([$teacherId]);
    $transactions = array_map(static fn($r) => [
        'date' => date('Y-m-d', strtotime($r['date'])),
        'course' => $r['course'],
        'student' => $r['student'],
        'amount' => (float) $r['amount'],
    ], $stmt->fetchAll());

    return [
        'balance' => round($total * 0.85, 2),
        'this_month' => $thisMonth,
        'last_month' => $lastMonth,
        'total' => $total,
        'pending_payout' => round($thisMonth * 0.4, 2),
        'history' => [],
        'transactions' => $transactions,
    ];
}

function getTeacherVerification(?int $teacherId = null): array
{
    $teacherId = $teacherId ?? auth_id();
    if (!$teacherId || !db_available()) {
        return fallbackTeacherVerification();
    }
    $stmt = db()->prepare('SELECT tp.*, u.name FROM teacher_profiles tp JOIN users u ON u.id=tp.user_id WHERE tp.user_id=?');
    $stmt->execute([$teacherId]);
    $row = $stmt->fetch();
    if (!$row) {
        return fallbackTeacherVerification();
    }
    $docStmt = db()->prepare('SELECT original_name, file_path FROM teacher_documents WHERE teacher_profile_id=?');
    $docStmt->execute([$row['id']]);
    $docs = array_map(static fn($d) => $d['original_name'], $docStmt->fetchAll());

    return [
        'status' => $row['verification_status'] === 'verified' ? 'verified' : $row['verification_status'],
        'verified_at' => $row['verified_at'] ?? '',
        'qualification' => $row['qualification'] ?? '',
        'cnic' => $row['cnic'] ?? '',
        'documents' => $docs,
    ];
}

function getStudentStats(?int $studentId = null): array
{
    $enrollments = getStudentEnrollments($studentId);
    $active = count(array_filter($enrollments, fn($e) => $e['status'] === 'active'));
    $completed = count(array_filter($enrollments, fn($e) => $e['status'] === 'completed'));
    return [
        'total' => count($enrollments),
        'active' => $active,
        'completed' => $completed,
        'hours' => min(99, count($enrollments) * 12),
    ];
}

function getTeacherStats(?int $teacherId = null): array
{
    $courses = getTeacherCourses($teacherId);
    $published = array_filter($courses, fn($c) => ($c['status'] ?? '') === 'published');
    $students = array_sum(array_column($published, 'students'));
    $earnings = getTeacherEarnings($teacherId);
    $teacher = getCurrentTeacher();
    return [
        'courses' => count($published),
        'students' => $students,
        'rating' => $teacher['rating'] ?? 0,
        'revenue_month' => $earnings['this_month'],
    ];
}

function getAdminUsersList(): array
{
    if (!db_available()) {
        $users = fallbackStudents();
        foreach (fallbackTeachers() as $teacher) {
            $users[] = [
                'id' => $teacher['id'],
                'name' => $teacher['name'],
                'email' => $teacher['email'],
                'phone' => '',
                'joined' => '2024-08-15',
                'status' => 'active',
                'courses' => 2,
                'role' => 'teacher',
            ];
        }
        return $users;
    }
    $sql = "SELECT u.id, u.name, u.email, u.phone, u.role, u.status, u.created_at,
            CASE WHEN u.role = 'teacher'
              THEN (SELECT COUNT(*) FROM courses c WHERE c.teacher_id = u.id)
              ELSE (SELECT COUNT(*) FROM enrollments e WHERE e.student_id = u.id)
            END AS courses
            FROM users u
            WHERE u.role IN ('student', 'teacher')
            ORDER BY u.created_at DESC";
    $stmt = db()->query($sql);
    return array_map(static function ($row) {
        return [
            'id' => (int) $row['id'],
            'name' => $row['name'],
            'email' => $row['email'],
            'phone' => $row['phone'] ?? '',
            'joined' => date('Y-m-d', strtotime($row['created_at'])),
            'status' => $row['status'],
            'courses' => (int) $row['courses'],
            'role' => $row['role'],
        ];
    }, $stmt->fetchAll());
}

function enrollStudent(int $studentId, int $courseId, string $method = 'Card'): array
{
    $course = getCourseById($courseId);
    if (!$course || (($course['status'] ?? '') !== 'published' && ($course['status'] ?? '') !== 'approved')) {
        if (!db_available()) {
            throw new RuntimeException('Course not available.');
        }
        $stmt = db()->prepare("SELECT status FROM courses WHERE id=?");
        $stmt->execute([$courseId]);
        if ($stmt->fetchColumn() !== 'published') {
            throw new RuntimeException('Course not available.');
        }
    }

    $pdo = db();
    $check = $pdo->prepare('SELECT id FROM enrollments WHERE student_id=? AND course_id=?');
    $check->execute([$studentId, $courseId]);
    if ($check->fetch()) {
        throw new RuntimeException('Already enrolled in this course.');
    }

    $pdo->beginTransaction();
    try {
        $ref = 'PAY-' . str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
        $amount = (float) $course['price'];
        $share = round($amount * 0.7, 2);

        $pay = $pdo->prepare('INSERT INTO payments (reference, student_id, course_id, amount, method, status, teacher_share) VALUES (?,?,?,?,?,?,?)');
        $pay->execute([$ref, $studentId, $courseId, $amount, $method, 'completed', $share]);

        $en = $pdo->prepare('INSERT INTO enrollments (student_id, course_id, progress, status, last_access) VALUES (?,?,0,?,CURDATE())');
        $en->execute([$studentId, $courseId, 'active']);

        $pdo->commit();
        return ['reference' => $ref, 'course_id' => $courseId];
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function createCourse(int $teacherId, array $data): int
{
    $catStmt = db()->prepare('SELECT id FROM categories WHERE name = ? OR slug = ? LIMIT 1');
    $catStmt->execute([$data['category'], slugify($data['category'])]);
    $categoryId = (int) ($catStmt->fetchColumn() ?: 1);

    $slug = slugify($data['title']);
    $stmt = db()->prepare('INSERT INTO courses (teacher_id, category_id, title, slug, description, price, status) VALUES (?,?,?,?,?,?,?)');
    $stmt->execute([
        $teacherId,
        $categoryId,
        $data['title'],
        $slug,
        $data['description'],
        (float) $data['price'],
        $data['status'] ?? 'pending',
    ]);
    $courseId = (int) db()->lastInsertId();

    if (!empty($data['lessons'])) {
        $lessonStmt = db()->prepare('INSERT INTO lessons (course_id, title, duration, sort_order, content_url) VALUES (?,?,?,?,?)');
        foreach ($data['lessons'] as $i => $lesson) {
            $title = trim($lesson['title'] ?? '');
            if ($title === '') {
                continue;
            }
            $duration = trim($lesson['duration'] ?? '10:00');
            if (!preg_match('/^\d{1,2}:\d{2}$/', $duration)) {
                $duration = '10:00';
            }
            $contentUrl = trim($lesson['content_url'] ?? '');
            $lessonStmt->execute([$courseId, $title, $duration, $i + 1, $contentUrl ?: null]);
        }
    }
    return $courseId;
}

function updateCourse(int $courseId, int $teacherId, array $data): void
{
    $course = getCourseById($courseId);
    if (!$course) {
        throw new RuntimeException('Course not found.');
    }
    if ((int) $course['teacher_id'] !== $teacherId) {
        throw new RuntimeException('Unauthorized course update.');
    }

    $category = $data['category'] ?? $course['category'];
    $catStmt = db()->prepare('SELECT id FROM categories WHERE name = ? OR slug = ? LIMIT 1');
    $catStmt->execute([$category, slugify($category)]);
    $categoryId = (int) ($catStmt->fetchColumn() ?: 1);

    $title = $data['title'] ?? $course['title'];
    $slug = slugify($title);
    $description = $data['description'] ?? $course['desc'];
    $price = isset($data['price']) ? (float) $data['price'] : $course['price'];
    $status = $data['status'] ?? $course['status'];

    db()->prepare('UPDATE courses SET category_id = ?, title = ?, slug = ?, description = ?, price = ?, status = ? WHERE id = ? AND teacher_id = ?')
        ->execute([$categoryId, $title, $slug, $description, $price, $status, $courseId, $teacherId]);

    if (array_key_exists('lessons', $data)) {
        db()->prepare('DELETE FROM lessons WHERE course_id = ?')->execute([$courseId]);
        $lessonStmt = db()->prepare('INSERT INTO lessons (course_id, title, duration, sort_order, content_url) VALUES (?,?,?,?,?)');
        foreach (($data['lessons'] ?? []) as $i => $lesson) {
            $title = trim($lesson['title'] ?? '');
            if ($title === '') {
                continue;
            }
            $duration = trim($lesson['duration'] ?? '10:00');
            if (!preg_match('/^\d{1,2}:\d{2}$/', $duration)) {
                $duration = '10:00';
            }
            $contentUrl = trim($lesson['content_url'] ?? '');
            $lessonStmt->execute([$courseId, $title, $duration, $i + 1, $contentUrl ?: null]);
        }
    }
}

function deleteCourse(int $courseId, int $teacherId): void
{
    $stmt = db()->prepare('DELETE FROM courses WHERE id = ? AND teacher_id = ?');
    $stmt->execute([$courseId, $teacherId]);
    if ($stmt->rowCount() === 0) {
        throw new RuntimeException('Course delete failed or course not found.');
    }
}

function saveContactMessage(array $data): void
{
    if (!db_available()) {
        return;
    }
    $stmt = db()->prepare('INSERT INTO contact_messages (name, email, subject, message) VALUES (?,?,?,?)');
    $stmt->execute([$data['name'], $data['email'], $data['subject'], $data['message']]);
}

function updateUserProfile(int $userId, array $data): void
{
    $stmt = db()->prepare('UPDATE users SET name=?, email=?, phone=?, bio=? WHERE id=?');
    $stmt->execute([$data['name'], $data['email'], $data['phone'] ?? null, $data['bio'] ?? null, $userId]);

    if (!empty($data['avatar'])) {
        db()->prepare('UPDATE users SET avatar=? WHERE id=?')->execute([$data['avatar'], $userId]);
    }

    if (!empty($data['password'])) {
        $hash = password_hash($data['password'], PASSWORD_DEFAULT);
        db()->prepare('UPDATE users SET password_hash=? WHERE id=?')->execute([$hash, $userId]);
    }

    if (auth_id() === $userId) {
        $fresh = getUserById($userId);
        if ($fresh) {
            auth_login($fresh);
        }
    }
}

function studentIsEnrolled(int $studentId, int $courseId): bool
{
    if (!db_available()) {
        return false;
    }
    $stmt = db()->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ? LIMIT 1');
    $stmt->execute([$studentId, $courseId]);
    return (bool) $stmt->fetch();
}

function getEnrollmentProgress(int $studentId, int $courseId): int
{
    if (!db_available()) {
        return 0;
    }
    $stmt = db()->prepare('SELECT progress FROM enrollments WHERE student_id = ? AND course_id = ? LIMIT 1');
    $stmt->execute([$studentId, $courseId]);
    return (int) ($stmt->fetchColumn() ?: 0);
}

function processCoursePayment(int $studentId, int $courseId, string $method, array $billing = []): array
{
    $course = getCourseById($courseId);
    if (!$course) {
        throw new RuntimeException('Course not found.');
    }

    if (!db_available()) {
        throw new RuntimeException('Database not available.');
    }

    $pdo = db();
    $check = $pdo->prepare('SELECT id FROM enrollments WHERE student_id=? AND course_id=?');
    $check->execute([$studentId, $courseId]);
    if ($check->fetch()) {
        throw new RuntimeException('You are already enrolled in this course.');
    }

    $pendingCheck = $pdo->prepare("SELECT id FROM payments WHERE student_id=? AND course_id=? AND status='pending' LIMIT 1");
    $pendingCheck->execute([$studentId, $courseId]);
    if ($pendingCheck->fetch()) {
        throw new RuntimeException('A payment for this course is already pending admin approval.');
    }

    $amount = (float) $course['price'];
    $teacherShare = round($amount * 0.7, 2);
    $paymentRef = next_payment_reference();
    $instantMethods = ['card', 'jazzcash', 'easypaisa'];
    $methodKey = strtolower($method);
    $status = in_array($methodKey, $instantMethods, true) ? 'completed' : 'pending';

    $pdo->beginTransaction();
    try {
        $pay = $pdo->prepare('INSERT INTO payments (reference, student_id, course_id, amount, method, status, teacher_share, created_at) VALUES (?,?,?,?,?,?,?,NOW())');
        $pay->execute([$paymentRef, $studentId, $courseId, $amount, $method, $status, $teacherShare]);

        if ($status === 'completed') {
            $en = $pdo->prepare('INSERT INTO enrollments (student_id, course_id, progress, status, last_access) VALUES (?,?,0,?,CURDATE())');
            $en->execute([$studentId, $courseId, 'active']);
        }

        $pdo->commit();

        return [
            'reference' => $paymentRef,
            'status' => $status,
            'amount' => $amount,
            'method' => $method,
        ];
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function admin_confirm_payment(int $paymentId): void
{
    $pdo = db();
    $stmt = $pdo->prepare('SELECT * FROM payments WHERE id = ? LIMIT 1');
    $stmt->execute([$paymentId]);
    $payment = $stmt->fetch();
    if (!$payment) {
        throw new RuntimeException('Payment not found.');
    }
    if ($payment['status'] === 'completed') {
        return;
    }

    $pdo->beginTransaction();
    try {
        $pdo->prepare("UPDATE payments SET status='completed' WHERE id=?")->execute([$paymentId]);

        $check = $pdo->prepare('SELECT id FROM enrollments WHERE student_id=? AND course_id=? LIMIT 1');
        $check->execute([(int) $payment['student_id'], (int) $payment['course_id']]);
        if (!$check->fetch()) {
            $en = $pdo->prepare('INSERT INTO enrollments (student_id, course_id, progress, status, last_access) VALUES (?,?,0,?,CURDATE())');
            $en->execute([(int) $payment['student_id'], (int) $payment['course_id'], 'active']);
        }

        $pdo->commit();
    } catch (Throwable $e) {
        $pdo->rollBack();
        throw $e;
    }
}

function admin_update_user_status(int $userId, string $status): void
{
    db()->prepare('UPDATE users SET status=? WHERE id=?')->execute([$status, $userId]);
}

function admin_verify_teacher(int $userId, bool $approve): void
{
    $status = $approve ? 'verified' : 'rejected';
    $userStatus = $approve ? 'active' : 'inactive';
    db()->prepare('UPDATE teacher_profiles SET verification_status=?, verified_at=? WHERE user_id=?')
        ->execute([$status, $approve ? date('Y-m-d') : null, $userId]);
    db()->prepare('UPDATE users SET status=? WHERE id=?')->execute([$userStatus, $userId]);
}

function admin_update_course_status(int $courseId, string $status): void
{
    $map = ['approved' => 'published', 'published' => 'published', 'pending' => 'pending', 'rejected' => 'rejected'];
    $dbStatus = $map[$status] ?? $status;
    db()->prepare('UPDATE courses SET status=? WHERE id=?')->execute([$dbStatus, $courseId]);
}

function admin_delete_course(int $courseId): void
{
    db()->prepare('DELETE FROM courses WHERE id=?')->execute([$courseId]);
}

function admin_update_payment_status(int $paymentId, string $status): void
{
    db()->prepare('UPDATE payments SET status=? WHERE id=?')->execute([$status, $paymentId]);
}

function next_payment_reference(): string
{
    return 'PAY-' . str_pad((string) random_int(10000, 99999), 5, '0', STR_PAD_LEFT);
}

// --- Static fallbacks (when DB not installed) ---

function fallbackCourses(): array
{
    return [
        ['id' => 1, 'title' => 'Complete Web Development', 'teacher' => 'Dr. Sarah Khan', 'price' => 49.99, 'rating' => 4.8, 'category' => 'Development', 'thumb' => 'assets/images/avatars/placeholder.svg', 'students' => 1240, 'desc' => 'Master HTML, CSS, JavaScript, and modern frameworks.'],
        ['id' => 2, 'title' => 'UI/UX Design Masterclass', 'teacher' => 'Ahmed Hassan', 'price' => 39.99, 'rating' => 4.7, 'category' => 'Design', 'thumb' => 'assets/images/avatars/placeholder.svg', 'students' => 890, 'desc' => 'Learn user research, wireframing, and prototyping.'],
        ['id' => 3, 'title' => 'Data Science with Python', 'teacher' => 'Maria Lopez', 'price' => 59.99, 'rating' => 4.9, 'category' => 'Data Science', 'thumb' => 'assets/images/avatars/placeholder.svg', 'students' => 2100, 'desc' => 'Python, pandas, ML basics, and visualization.'],
        ['id' => 4, 'title' => 'Digital Marketing Fundamentals', 'teacher' => 'James Wilson', 'price' => 29.99, 'rating' => 4.6, 'category' => 'Marketing', 'thumb' => 'assets/images/avatars/placeholder.svg', 'students' => 650, 'desc' => 'SEO, social media, and campaign strategy.'],
        ['id' => 5, 'title' => 'Mobile App Development', 'teacher' => 'Dr. Sarah Khan', 'price' => 54.99, 'rating' => 4.8, 'category' => 'Development', 'thumb' => 'assets/images/avatars/placeholder.svg', 'students' => 980, 'desc' => 'Build cross-platform apps with modern tools.'],
        ['id' => 6, 'title' => 'Business Analytics', 'teacher' => 'Maria Lopez', 'price' => 44.99, 'rating' => 4.5, 'category' => 'Business', 'thumb' => 'assets/images/avatars/placeholder.svg', 'students' => 430, 'desc' => 'Data-driven decision making for business.'],
    ];
}

function fallbackTeachers(): array
{
    return [
        ['id' => 1, 'name' => 'Dr. Sarah Khan', 'email' => 'sarah.khan@digitaltutor.com', 'qualification' => 'PhD Computer Science', 'experience' => '12 years', 'rating' => 4.9, 'subject' => 'Development', 'photo' => 'assets/images/avatars/placeholder.svg', 'students' => 3200, 'bio' => 'Senior developer and educator.'],
        ['id' => 2, 'name' => 'Ahmed Hassan', 'email' => 'ahmed@email.com', 'qualification' => 'MSc Design', 'experience' => '8 years', 'rating' => 4.7, 'subject' => 'Design', 'photo' => 'assets/images/avatars/placeholder.svg', 'students' => 1800, 'bio' => 'UI/UX designer.'],
        ['id' => 3, 'name' => 'Maria Lopez', 'email' => 'maria@email.com', 'qualification' => 'PhD Statistics', 'experience' => '10 years', 'rating' => 4.9, 'subject' => 'Data Science', 'photo' => 'assets/images/avatars/placeholder.svg', 'students' => 4100, 'bio' => 'Data scientist.'],
        ['id' => 4, 'name' => 'James Wilson', 'email' => 'james@email.com', 'qualification' => 'MBA Marketing', 'experience' => '7 years', 'rating' => 4.6, 'subject' => 'Marketing', 'photo' => 'assets/images/avatars/placeholder.svg', 'students' => 950, 'bio' => 'Marketing strategist.'],
    ];
}

function fallbackStudents(): array
{
    return [
        ['id' => 101, 'name' => 'Ali Raza', 'email' => 'ali.raza@email.com', 'phone' => '+92 300 1112233', 'joined' => '2025-01-12', 'status' => 'active', 'courses' => 4, 'role' => 'student'],
    ];
}

function fallbackPendingVerifications(): array { return []; }

function fallbackAdminCourses(): array
{
    $courses = [];
    foreach (fallbackCourses() as $course) {
        $courses[] = array_merge($course, ['status' => 'approved', 'submitted' => '2025-03-10', 'enrollments' => $course['students']]);
    }
    return $courses;
}

function fallbackPayments(): array { return []; }

function fallbackAdminStats(): array
{
    return [
        'total_users' => 0, 'students' => 0, 'teachers' => 0, 'pending_verifications' => 0,
        'total_courses' => 0, 'pending_courses' => 0, 'revenue_month' => 0, 'revenue_total' => 0,
        'enrollments_month' => 0, 'active_students' => 0,
    ];
}

function fallbackCurrentStudent(): array
{
    return [
        'id' => 101, 'name' => 'Ali Raza', 'email' => 'ali.raza@email.com', 'phone' => '+92 300 1112233',
        'joined' => '2025-01-12', 'bio' => '', 'avatar' => 'assets/images/avatars/placeholder.svg',
    ];
}

function fallbackStudentEnrollments(): array { return []; }
function fallbackStudentPurchases(): array { return []; }

function fallbackCourseLessons(int $courseId): array
{
    return [['id' => 1, 'title' => 'Getting Started', 'duration' => '10:00', 'completed' => false]];
}

function fallbackCurrentTeacher(): array { return fallbackTeachers()[0]; }
function fallbackTeacherCourses(): array { return []; }

function fallbackTeacherEarnings(): array
{
    return ['balance' => 0, 'this_month' => 0, 'last_month' => 0, 'total' => 0, 'pending_payout' => 0, 'history' => [], 'transactions' => []];
}

function fallbackTeacherVerification(): array
{
    return ['status' => 'verified', 'verified_at' => '', 'qualification' => '', 'cnic' => '', 'documents' => []];
}
