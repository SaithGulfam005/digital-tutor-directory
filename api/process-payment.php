<?php
/**
 * Process Payment API Endpoint
 * POST /api/process-payment.php
 * 
 * This endpoint processes Stripe payments and creates enrollments
 * Future: Integrate with actual Stripe API when SDK is installed
 */

declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';
require_once __DIR__ . '/../components/payment-config.php';

$user = auth_user();
if (!$user || $user['role'] !== 'student') {
    json_response(['success' => false, 'message' => 'Unauthorized'], 401);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['success' => false, 'message' => 'Invalid request method'], 405);
}

$courseId = (int) ($_POST['course_id'] ?? 0);
$firstName = trim($_POST['first_name'] ?? '');
$country = trim($_POST['country'] ?? '');
$postalCode = trim($_POST['postal_code'] ?? '');

if (!$courseId) {
    json_response(['success' => false, 'message' => 'Course not found'], 400);
}

if (!db_available()) {
    json_response(['success' => false, 'message' => 'Database not available'], 503);
}

// Verify course exists and get details
$course = getCourseById($courseId);
if (!$course) {
    json_response(['success' => false, 'message' => 'Course not found'], 404);
}

// Check if already enrolled
$stmt = db()->prepare('SELECT id FROM enrollments WHERE student_id = ? AND course_id = ? LIMIT 1');
$stmt->execute([(int) $user['id'], $courseId]);
if ($stmt->fetch()) {
    json_response(['success' => false, 'message' => 'You are already enrolled in this course'], 409);
}

try {
    db()->beginTransaction();

    // In production, verify the payment with Stripe API
    // For now, we'll create a pending payment and simulate Stripe processing
    
    $paymentRef = 'PAY-' . str_pad((string) random_int(100000, 999999), 6, '0', STR_PAD_LEFT);
    $amount = (float) $course['price'];
    $teacherShare = round($amount * 0.7, 2);

    // Create payment record
    $stmt = db()->prepare('
        INSERT INTO payments 
        (reference, student_id, course_id, amount, method, status, teacher_share, created_at) 
        VALUES (?, ?, ?, ?, ?, ?, ?, NOW())
    ');
    $stmt->execute([
        $paymentRef,
        (int) $user['id'],
        $courseId,
        $amount,
        'stripe',
        'completed', // In production, this should be 'pending' until Stripe confirms
        $teacherShare,
    ]);

    // Create enrollment
    $stmt = db()->prepare('
        INSERT INTO enrollments 
        (student_id, course_id, progress, status, last_access, enrolled_at) 
        VALUES (?, ?, 0, ?, CURDATE(), NOW())
    ');
    $stmt->execute([
        (int) $user['id'],
        $courseId,
        'active',
    ]);

    db()->commit();

    // Log payment for audit purposes
    error_log("Payment processed: Reference=$paymentRef, Student=" . $user['id'] . ", Course=$courseId, Amount=$amount");

    json_response([
        'success' => true,
        'message' => 'Payment processed successfully',
        'payment_reference' => $paymentRef,
        'redirect' => url('student/my-courses.php'),
    ]);

} catch (Throwable $e) {
    if (db()) {
        try {
            db()->rollBack();
        } catch (Throwable) {
            // Already rolled back or transaction not started
        }
    }
    
    error_log("Payment processing error: " . $e->getMessage());
    json_response(['success' => false, 'message' => 'Payment processing failed: ' . $e->getMessage()], 500);
}
