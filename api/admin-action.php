<?php
declare(strict_types=1);

require_once __DIR__ . '/../components/config.php';

header('Content-Type: application/json');

$user = auth_user();
if (!$user || ($user['role'] ?? '') !== 'admin') {
    json_response(['ok' => false, 'message' => 'Unauthorized'], 403);
}

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    json_response(['ok' => false, 'message' => 'Invalid request'], 405);
}

$action = $_POST['action'] ?? '';
$id = (int) ($_POST['id'] ?? 0);

try {
    switch ($action) {
        case 'approve_teacher':
            admin_verify_teacher($id, true);
            json_response(['ok' => true, 'message' => 'Teacher approved.']);
        case 'reject_teacher':
            admin_verify_teacher($id, false);
            json_response(['ok' => true, 'message' => 'Teacher rejected.']);
        case 'approve_course':
            admin_update_course_status($id, 'published');
            json_response(['ok' => true, 'message' => 'Course approved.']);
        case 'reject_course':
            admin_update_course_status($id, 'rejected');
            json_response(['ok' => true, 'message' => 'Course rejected.']);
        case 'activate_user':
            admin_update_user_status($id, 'active');
            json_response(['ok' => true, 'message' => 'User activated.']);
        case 'deactivate_user':
            admin_update_user_status($id, 'inactive');
            json_response(['ok' => true, 'message' => 'User deactivated.']);
        case 'confirm_payment':
            admin_update_payment_status($id, 'completed');
            json_response(['ok' => true, 'message' => 'Payment confirmed.']);
        case 'refund_payment':
            admin_update_payment_status($id, 'refunded');
            json_response(['ok' => true, 'message' => 'Payment refunded.']);
        default:
            json_response(['ok' => false, 'message' => 'Unknown action'], 400);
    }
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => $e->getMessage()], 500);
}
