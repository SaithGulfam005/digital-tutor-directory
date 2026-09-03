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
            break;
        case 'reject_teacher':
            admin_verify_teacher($id, false);
            json_response(['ok' => true, 'message' => 'Teacher rejected.']);
            break;
        case 'approve_course':
            admin_update_course_status($id, 'published');
            json_response(['ok' => true, 'message' => 'Course approved.']);
            break;
        case 'reject_course':
            admin_update_course_status($id, 'rejected');
            json_response(['ok' => true, 'message' => 'Course rejected.']);
            break;
        case 'delete_course':
            admin_delete_course($id);
            json_response(['ok' => true, 'message' => 'Course deleted.']);
            break;
        case 'activate_user':
            admin_update_user_status($id, 'active');
            json_response(['ok' => true, 'message' => 'User activated.']);
            break;
        case 'deactivate_user':
            admin_update_user_status($id, 'inactive');
            json_response(['ok' => true, 'message' => 'User deactivated.']);
            break;
        case 'confirm_payment':
            admin_confirm_payment($id);
            json_response(['ok' => true, 'message' => 'Payment confirmed and student enrolled.']);
            break;
        case 'reject_payment':
            admin_reject_payment($id, trim((string) ($_POST['reason'] ?? '')));
            json_response(['ok' => true, 'message' => 'Payment rejected and the student has been notified.']);
            break;
        case 'refund_payment':
            admin_update_payment_status($id, 'refunded');
            json_response(['ok' => true, 'message' => 'Payment refunded.']);
            break;
        case 'approve_payout_request':
            if (!update_payout_request($id, ['status' => 'approved', 'processed_at' => date('Y-m-d H:i:s')])) {
                throw new RuntimeException('Payout request not found.');
            }
            json_response(['ok' => true, 'message' => 'Payout request approved. Admin will process it within 24 hours.']);
            break;
        case 'reject_payout_request':
            if (!update_payout_request($id, ['status' => 'rejected', 'processed_at' => date('Y-m-d H:i:s')])) {
                throw new RuntimeException('Payout request not found.');
            }
            json_response(['ok' => true, 'message' => 'Payout request rejected.']);
            break;
        default:
            json_response(['ok' => false, 'message' => 'Unknown action'], 400);
            break;
    }
} catch (Throwable $e) {
    json_response(['ok' => false, 'message' => $e->getMessage()], 500);
}
