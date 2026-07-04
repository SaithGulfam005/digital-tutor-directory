<?php
define('SITE_NAME', 'Digital Tutor Directory');
define('BASE_URL', '/digital-tutor-directory');

require_once __DIR__ . '/database.php';
require_once __DIR__ . '/auth.php';
require_once __DIR__ . '/mail.php';

function normalize_lesson_duration(?string $value): string
{
    $value = trim((string) ($value ?? ''));
    if ($value === '') {
        return '';
    }

    if (preg_match('/^\d{1,2}:\d{2}$/', $value)) {
        return $value;
    }

    if (is_numeric($value)) {
        return format_lesson_duration((float) $value);
    }

    return '';
}

function format_lesson_duration(float $seconds): string
{
    $seconds = max(0, (int) round($seconds));
    if ($seconds < 60) {
        return '0:' . str_pad((string) $seconds, 2, '0', STR_PAD_LEFT);
    }

    $hours = intdiv($seconds, 3600);
    $minutes = intdiv($seconds % 3600, 60);
    $secs = $seconds % 60;

    if ($hours > 0) {
        return $hours . ':' . str_pad((string) $minutes, 2, '0', STR_PAD_LEFT) . ':' . str_pad((string) $secs, 2, '0', STR_PAD_LEFT);
    }

    return $minutes . ':' . str_pad((string) $secs, 2, '0', STR_PAD_LEFT);
}

function detect_video_duration(string $filePath): string
{
    if (!is_file($filePath)) {
        return '';
    }

    $commands = [
        'ffprobe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . escapeshellarg($filePath),
        'ffprobe.exe -v error -show_entries format=duration -of default=noprint_wrappers=1:nokey=1 ' . escapeshellarg($filePath),
    ];

    foreach ($commands as $command) {
        $output = [];
        $code = 0;
        @exec($command, $output, $code);
        if ($code === 0 && isset($output[0])) {
            $duration = trim((string) $output[0]);
            if (is_numeric($duration)) {
                return format_lesson_duration((float) $duration);
            }
        }
    }

    return '';
}

require_once __DIR__ . '/../includes/data.php';

function url(string $path = ''): string
{
    return rtrim(BASE_URL, '/') . '/' . ltrim($path, '/');
}
function asset(string $path): string
{
    return url('assets/' . ltrim($path, '/'));
}

function media_url(?string $path, string $fallback = 'assets/images/avatars/placeholder.svg'): string
{
    $path = trim((string) $path);
    $fallback = trim($fallback);
    $projectRoot = dirname(__DIR__) . DIRECTORY_SEPARATOR;

    $resolveExistingPath = function (string $candidate) use ($projectRoot): ?string {
        if ($candidate === '') {
            return null;
        }

        if (preg_match('#^https?://#i', $candidate)) {
            return $candidate;
        }

        if (str_starts_with($candidate, BASE_URL . '/')) {
            return $candidate;
        }

        if (str_starts_with($candidate, '/')) {
            $candidate = ltrim($candidate, '/');
        }

        $relativePath = ltrim($candidate, '/');
        $fullPath = $projectRoot . str_replace('/', DIRECTORY_SEPARATOR, $relativePath);

        if ($relativePath !== '' && file_exists($fullPath)) {
            return url($relativePath);
        }

        return null;
    };

    if ($path !== '') {
        $resolvedPath = $resolveExistingPath($path);
        if ($resolvedPath !== null) {
            return $resolvedPath;
        }
    }

    if ($fallback !== '') {
        $resolvedFallback = $resolveExistingPath($fallback);
        if ($resolvedFallback !== null) {
            return $resolvedFallback;
        }
    }

    return url($fallback !== '' ? $fallback : 'assets/images/avatars/placeholder.svg');
}
function upload_error_message(int $code): string
{
    return match ($code) {
        UPLOAD_ERR_INI_SIZE, UPLOAD_ERR_FORM_SIZE => 'Lesson file exceeds the maximum upload size (40 MB).',
        UPLOAD_ERR_PARTIAL => 'Lesson upload was interrupted. Please try again.',
        UPLOAD_ERR_NO_FILE => 'No lesson file was selected.',
        default => 'Lesson upload failed. Please try again.',
    };
}

function video_mime_type(string $path): string
{
    $ext = strtolower(pathinfo(parse_url($path, PHP_URL_PATH) ?: $path, PATHINFO_EXTENSION));
    return match ($ext) {
        'webm' => 'video/webm',
        'ogg' => 'video/ogg',
        'mov' => 'video/quicktime',
        'avi' => 'video/x-msvideo',
        'm4v' => 'video/mp4',
        default => 'video/mp4',
    };
}

function save_uploaded_course_thumbnail(array $file): string
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        throw new RuntimeException('Please choose a valid thumbnail image.');
    }

    $tmpName = $file['tmp_name'] ?? '';
    if ($tmpName === '' || !is_uploaded_file($tmpName)) {
        throw new RuntimeException('Invalid thumbnail upload. Please try again.');
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size <= 0 || $size > 2 * 1024 * 1024) {
        throw new RuntimeException('Thumbnail image must be smaller than 2 MB.');
    }

    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = finfo_file($finfo, $tmpName);
    finfo_close($finfo);

    $allowed = ['image/jpeg', 'image/png', 'image/webp', 'image/gif'];
    if (!in_array($mime, $allowed, true)) {
        throw new RuntimeException('Only JPG, PNG, WEBP, or GIF thumbnails are allowed.');
    }

    $ext = match ($mime) {
        'image/jpeg' => 'jpg',
        'image/png' => 'png',
        'image/webp' => 'webp',
        'image/gif' => 'gif',
        default => 'jpg',
    };

    $uploadDir = __DIR__ . '/../uploads/courses';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = 'course-thumb_' . uniqid('', true) . '.' . $ext;
    $dest = $uploadDir . '/' . $filename;
    if (!move_uploaded_file($tmpName, $dest)) {
        throw new RuntimeException('Failed to upload thumbnail image.');
    }

    return 'uploads/courses/' . $filename;
}

function save_uploaded_lesson_file(array $file): array
{
    $error = (int) ($file['error'] ?? UPLOAD_ERR_NO_FILE);
    if ($error !== UPLOAD_ERR_OK) {
        if ($error !== UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException(upload_error_message($error));
        }
        return ['path' => null, 'duration' => ''];
    }

    $tmpName = $file['tmp_name'] ?? '';
    $isUploadedFile = $tmpName !== '' && is_uploaded_file($tmpName);
    if ($tmpName === '' || (!$isUploadedFile && PHP_SAPI !== 'cli')) {
        throw new RuntimeException('Invalid lesson upload. Please try again.');
    }

    $size = (int) ($file['size'] ?? 0);
    if ($size < 1024) {
        throw new RuntimeException(
            'The selected file is empty or too small. Please choose a valid lesson file.'
        );
    }

    if ($size > 40 * 1024 * 1024) {
        throw new RuntimeException('Lesson files must be 40 MB or smaller.');
    }

    $originalName = basename($file['name'] ?? '');
    $extension = strtolower(pathinfo($originalName, PATHINFO_EXTENSION));
    $finfo = finfo_open(FILEINFO_MIME_TYPE);
    $mime = $finfo ? (string) finfo_file($finfo, $tmpName) : '';
    if ($finfo) {
        finfo_close($finfo);
    }

    $uploadDir = __DIR__ . '/../uploads/lessons';
    if (!is_dir($uploadDir)) {
        mkdir($uploadDir, 0755, true);
    }

    $filename = $extension !== '' ? uniqid('lesson_', true) . '.' . $extension : uniqid('lesson_', true);
    $dest = $uploadDir . '/' . $filename;
    if ($isUploadedFile) {
        if (!move_uploaded_file($tmpName, $dest)) {
            throw new RuntimeException('Failed to save lesson file. Check that uploads/lessons is writable.');
        }
    } else {
        if (!copy($tmpName, $dest)) {
            throw new RuntimeException('Failed to save lesson file. Check that uploads/lessons is writable.');
        }
    }

    if (filesize($dest) < 1024) {
        @unlink($dest);
        throw new RuntimeException('The uploaded file could not be saved correctly. Please try again.');
    }

    $duration = '';
    if ($mime !== '' && str_starts_with($mime, 'video/')) {
        $duration = detect_video_duration($dest);
    }

    return ['path' => 'uploads/lessons/' . $filename, 'duration' => $duration];
}

function save_uploaded_lesson_video(array $file): ?string
{
    $saved = save_uploaded_lesson_file($file);
    return $saved['path'] ?? null;
}

function normalize_uploaded_files(array $files): array
{
    if ($files === [] || !isset($files['name'])) {
        return [];
    }
    if (!is_array($files['name'])) {
        return [
            'name' => [$files['name']],
            'type' => [$files['type'] ?? ''],
            'tmp_name' => [$files['tmp_name'] ?? ''],
            'error' => [(int) ($files['error'] ?? UPLOAD_ERR_NO_FILE)],
            'size' => [(int) ($files['size'] ?? 0)],
        ];
    }
    return $files;
}

function is_local_video_path(string $path): bool
{
    $path = trim($path);
    return $path !== '' && !preg_match('#^https?://#i', $path);
}

function lesson_playback_url(int $courseId, array $lesson): string
{
    $url = trim($lesson['content_url'] ?? '');
    if ($url === '') {
        return '';
    }
    if (preg_match('#(?:youtube\.com|youtu\.be|vimeo\.com)#i', $url)) {
        return $url;
    }
    if (preg_match('#^https?://#i', $url)) {
        return $url;
    }

    $extension = strtolower(pathinfo(parse_url($url, PHP_URL_PATH) ?: $url, PATHINFO_EXTENSION));
    $downloadableExtensions = ['pdf', 'doc', 'docx', 'ppt', 'pptx', 'txt', 'zip', 'rar', 'csv', 'xlsx', 'xls', 'jpg', 'jpeg', 'png', 'gif', 'webp', 'svg'];
    if (in_array($extension, $downloadableExtensions, true)) {
        return url($url);
    }

    return url('api/lesson-video.php?course=' . $courseId . '&lesson=' . (int) ($lesson['id'] ?? 0));
}

function stream_video_file(string $absPath): never
{
    if (!is_readable($absPath)) {
        http_response_code(404);
        exit('Video not found');
    }

    $size = filesize($absPath);
    if ($size === false || $size < 1) {
        http_response_code(404);
        exit('Video file is empty');
    }

    while (ob_get_level() > 0) {
        ob_end_clean();
    }

    $mime = video_mime_type($absPath);
    $start = 0;
    $end = $size - 1;
    $length = $size;

    if (isset($_SERVER['HTTP_RANGE']) && preg_match('/bytes=(\d*)-(\d*)/', (string) $_SERVER['HTTP_RANGE'], $matches)) {
        if ($matches[1] !== '') {
            $start = (int) $matches[1];
        }
        if ($matches[2] !== '') {
            $end = (int) $matches[2];
        }
        if ($end >= $size) {
            $end = $size - 1;
        }
        if ($start > $end) {
            http_response_code(416);
            header('Content-Range: bytes */' . $size);
            exit;
        }
        $length = $end - $start + 1;
        http_response_code(206);
        header('Content-Range: bytes ' . $start . '-' . $end . '/' . $size);
    }

    header('Content-Type: ' . $mime);
    header('Accept-Ranges: bytes');
    header('Content-Length: ' . $length);
    header('Cache-Control: private, max-age=3600');

    $handle = fopen($absPath, 'rb');
    if ($handle === false) {
        http_response_code(500);
        exit;
    }

    fseek($handle, $start);
    $remaining = $length;
    while ($remaining > 0 && !feof($handle)) {
        $chunk = fread($handle, min(8192, $remaining));
        if ($chunk === false) {
            break;
        }
        echo $chunk;
        $remaining -= strlen($chunk);
    }
    fclose($handle);
    exit;
}

function parse_course_lessons(array $post, array $files): array
{
    $files = normalize_uploaded_files($files);
    $titles = array_map('trim', (array) ($post['lessons'] ?? []));
    $durations = array_map('trim', (array) ($post['lesson_durations'] ?? []));
    $urls = array_map('trim', (array) ($post['lesson_urls'] ?? []));
    $fileCount = is_array($files['name'] ?? null) ? count($files['name']) : 0;
    $count = max(count($titles), count($durations), count($urls), $fileCount);

    $lessons = [];
    for ($i = 0; $i < $count; $i++) {
        $title = $titles[$i] ?? '';
        if ($title === '') {
            continue;
        }

        $duration = normalize_lesson_duration($durations[$i] ?? '');
        $contentUrl = $urls[$i] ?? '';
        $fileError = isset($files['error'][$i]) ? (int) $files['error'][$i] : UPLOAD_ERR_NO_FILE;

        if ($fileError === UPLOAD_ERR_OK) {
            $uploaded = save_uploaded_lesson_file([
                'name' => $files['name'][$i] ?? '',
                'type' => $files['type'][$i] ?? '',
                'tmp_name' => $files['tmp_name'][$i] ?? '',
                'error' => $fileError,
                'size' => $files['size'][$i] ?? 0,
            ]);
            if (!empty($uploaded['path'])) {
                $contentUrl = $uploaded['path'];
                if ($uploaded['duration'] !== '') {
                    $duration = $uploaded['duration'];
                }
            }
        } elseif ($fileError !== UPLOAD_ERR_NO_FILE) {
            throw new RuntimeException(upload_error_message($fileError) . ' (lesson: ' . $title . ')');
        }

        $lessons[] = [
            'title' => $title,
            'duration' => $duration,
            'content_url' => $contentUrl ?: null,
        ];
    }

    return $lessons;
}
function platform_fee_percentage(): float
{
    return 0.10;
}

function calculate_platform_fee(float $amount): float
{
    return round($amount * platform_fee_percentage(), 2);
}

function calculate_teacher_share(float $amount): float
{
    return round($amount * (1 - platform_fee_percentage()), 2);
}

function payout_requests_storage_path(): string
{
    return __DIR__ . '/../uploads/payout-requests.json';
}

function load_payout_requests(): array
{
    $path = payout_requests_storage_path();
    if (!is_file($path)) {
        return [];
    }

    $data = json_decode((string) file_get_contents($path), true);
    return is_array($data) ? $data : [];
}

function save_payout_requests(array $requests): void
{
    $path = payout_requests_storage_path();
    $dir = dirname($path);
    if (!is_dir($dir)) {
        mkdir($dir, 0755, true);
    }
    file_put_contents($path, json_encode($requests, JSON_PRETTY_PRINT | JSON_UNESCAPED_SLASHES));
}

function create_payout_request(array $request): array
{
    $requests = load_payout_requests();
    $requestId = count($requests) + 1;
    $entry = [
        'id' => $requestId,
        'status' => 'pending',
        'created_at' => date('Y-m-d H:i:s'),
        'updated_at' => date('Y-m-d H:i:s'),
    ];
    $entry = array_merge($entry, $request);
    $requests[] = $entry;
    save_payout_requests($requests);
    return $entry;
}

function get_teacher_payout_requests(?int $teacherId = null): array
{
    $requests = load_payout_requests();
    if ($teacherId === null) {
        return $requests;
    }

    $filtered = array_values(array_filter($requests, static fn($request) => (int) ($request['teacher_id'] ?? 0) === $teacherId));
    usort($filtered, static fn($a, $b) => strtotime((string) ($b['created_at'] ?? '')) <=> strtotime((string) ($a['created_at'] ?? '')));
    return $filtered;
}

function get_all_payout_requests(): array
{
    $requests = load_payout_requests();
    usort($requests, static fn($a, $b) => strtotime((string) ($b['created_at'] ?? '')) <=> strtotime((string) ($a['created_at'] ?? '')));
    return $requests;
}

function update_payout_request(int $requestId, array $changes): bool
{
    $requests = load_payout_requests();
    foreach ($requests as &$request) {
        if ((int) ($request['id'] ?? 0) === $requestId) {
            $request = array_merge($request, $changes, ['updated_at' => date('Y-m-d H:i:s')]);
            save_payout_requests($requests);
            return true;
        }
    }
    return false;
}

function renderStars(float $rating, int $max = 5): string
{
    $html = '';
    for ($i = 1; $i <= $max; $i++) {
        if ($rating >= $i) {
            $html .= '<i class="bi bi-star-fill text-warning"></i>';
        } elseif ($rating >= $i - 0.5) {
            $html .= '<i class="bi bi-star-half text-warning"></i>';
        } else {
            $html .= '<i class="bi bi-star text-warning"></i>';
        }
    }
    return $html;
}
function isActive(string $path): string
{
    return str_contains($_SERVER['PHP_SELF'] ?? '', $path) ? 'active' : '';
}

// Aliases used across pages (DB-backed with mock fallback in data.php)
function mockCourses(): array { return getCourses(true); }
function mockTeachers(): array { return getTeachers(true); }
function mockStudents(): array { return getStudents(); }
function mockPendingVerifications(): array { return getPendingVerifications(); }
function mockAdminCourses(): array { return getAdminCourses(); }
function mockPayments(): array { return getPayments(); }
function mockAdminStats(): array { return getAdminStats(); }
function mockCurrentStudent(): array { return getCurrentStudent(); }
function mockStudentEnrollments(): array { return getStudentEnrollments(); }
function mockStudentPurchases(): array { return getStudentPurchases(); }
function mockStudentBookings(): array { return getStudentBookings(); }
function mockCourseLessons(int $courseId): array { return getCourseLessons($courseId, auth_id()); }
function mockCurrentTeacher(): array { return getCurrentTeacher(); }
function mockTeacherCourses(): array { return getTeacherCourses(); }
function mockTeacherEarnings(): array { return getTeacherEarnings(); }
function mockTeacherVerification(): array { return getTeacherVerification(); }
function mockStudentStats(): array { return getStudentStats(); }
function mockTeacherStats(): array { return getTeacherStats(); }
