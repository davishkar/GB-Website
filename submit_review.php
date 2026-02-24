<?php
/**
 * GB Laser Soldering — Submit Review Endpoint
 * Method: POST
 * Returns: JSON { success: bool, message: string }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Access-Control-Allow-Methods: POST');

// Only allow POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'message' => 'Method not allowed.']);
    exit;
}

require_once 'db_config.php';

// ---- Rate limiting: max 3 submissions per IP per hour ----
$ip = $_SERVER['HTTP_X_FORWARDED_FOR'] ?? $_SERVER['REMOTE_ADDR'] ?? '0.0.0.0';
$ip = trim(explode(',', $ip)[0]);

$pdo = getDB();
$stmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE ip_address = ? AND created_at > NOW() - INTERVAL 1 HOUR");
$stmt->execute([$ip]);
if ((int)$stmt->fetchColumn() >= 3) {
    http_response_code(429);
    echo json_encode(['success' => false, 'message' => 'Too many submissions. Please try again later.']);
    exit;
}

// ---- Collect & sanitize input ----
$name        = trim(strip_tags($_POST['name']        ?? ''));
$service     = trim(strip_tags($_POST['service']     ?? 'General'));
$rating      = (int)($_POST['rating']                ?? 5);
$review_text = trim(strip_tags($_POST['review_text'] ?? ''));

// ---- Validation ----
$errors = [];
if (mb_strlen($name) < 2 || mb_strlen($name) > 100) {
    $errors[] = 'Name must be between 2 and 100 characters.';
}
if ($rating < 1 || $rating > 5) {
    $errors[] = 'Rating must be between 1 and 5.';
}
if (mb_strlen($review_text) < 20) {
    $errors[] = 'Review must be at least 20 characters long.';
}
if (mb_strlen($review_text) > 1000) {
    $errors[] = 'Review cannot exceed 1000 characters.';
}

// Allowed services
$allowed_services = [
    'Laser Gold Soldering',
    'Laser Silver Soldering',
    'Precision Stone Setting',
    'Laser Jewelry Repairs',
    'NG Gold Testing',
    'General',
];
if (!in_array($service, $allowed_services, true)) {
    $service = 'General';
}

if (!empty($errors)) {
    http_response_code(422);
    echo json_encode(['success' => false, 'message' => implode(' ', $errors)]);
    exit;
}

// ---- Insert (is_approved = 0: review awaits admin approval) ----
try {
    $stmt = $pdo->prepare("
        INSERT INTO reviews (name, service, rating, review_text, is_approved, ip_address)
        VALUES (:name, :service, :rating, :review_text, 0, :ip)
    ");
    $stmt->execute([
        ':name'        => $name,
        ':service'     => $service,
        ':rating'      => $rating,
        ':review_text' => $review_text,
        ':ip'          => $ip,
    ]);

    echo json_encode([
        'success' => true,
        'message' => 'Thank you for your review! It will appear after approval.',
    ]);
} catch (PDOException $e) {
    http_response_code(500);
    echo json_encode(['success' => false, 'message' => 'Could not save review. Please try again.']);
}
