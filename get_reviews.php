<?php
/**
 * GB Laser Soldering — Get Reviews API
 * Method: GET
 * Params: ?limit=8&offset=0&rating=0 (0 = all ratings)
 * Returns: JSON { reviews: [...], total: int, pages: int }
 */

header('Content-Type: application/json');
header('Access-Control-Allow-Origin: *');
header('Cache-Control: public, max-age=60'); // cache 60 seconds

require_once 'db_config.php';

$limit  = min((int)($_GET['limit']  ?? 8), 50);   // max 50 per request
$offset = max((int)($_GET['offset'] ?? 0), 0);
$rating = (int)($_GET['rating'] ?? 0);             // 0 = all, 1-5 = filter by rating

$pdo = getDB();

// Build query with optional rating filter
$ratingSQL = ($rating >= 1 && $rating <= 5) ? 'AND rating = :rating' : '';

$countStmt = $pdo->prepare("SELECT COUNT(*) FROM reviews WHERE is_approved = 1 $ratingSQL");
if ($rating >= 1 && $rating <= 5) $countStmt->bindValue(':rating', $rating, PDO::PARAM_INT);
$countStmt->execute();
$total = (int)$countStmt->fetchColumn();

$stmt = $pdo->prepare("
    SELECT id, name, service, rating, review_text, created_at
    FROM reviews
    WHERE is_approved = 1 $ratingSQL
    ORDER BY created_at DESC
    LIMIT :limit OFFSET :offset
");
$stmt->bindValue(':limit',  $limit,  PDO::PARAM_INT);
$stmt->bindValue(':offset', $offset, PDO::PARAM_INT);
if ($rating >= 1 && $rating <= 5) $stmt->bindValue(':rating', $rating, PDO::PARAM_INT);
$stmt->execute();

$reviews = $stmt->fetchAll();

// Format dates nicely
foreach ($reviews as &$r) {
    $r['date_label'] = date('d M Y', strtotime($r['created_at']));
    unset($r['created_at']);
}

// Average rating
$avgStmt = $pdo->query("SELECT ROUND(AVG(rating),1) as avg, COUNT(*) as cnt FROM reviews WHERE is_approved = 1");
$stats   = $avgStmt->fetch();

echo json_encode([
    'success' => true,
    'reviews' => $reviews,
    'total'   => $total,
    'pages'   => (int)ceil($total / $limit),
    'avg_rating' => (float)($stats['avg'] ?? 0),
    'total_approved' => (int)($stats['cnt'] ?? 0),
]);
