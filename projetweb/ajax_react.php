<?php
/**
 * AJAX endpoint for emoji reactions (like/love/fire/insightful)
 * Replaces the old simple like system with a rich reaction system
 */
header('Content-Type: application/json; charset=utf-8');
require_once __DIR__ . '/controller/BlogController.php';

$db = Config::GetConnexion();
$userIp = $_SERVER['REMOTE_ADDR'] ?? '127.0.0.1';

// Ensure reactions table exists
try {
    $db->exec("CREATE TABLE IF NOT EXISTS `reactions` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `post_id` int(11) NOT NULL,
        `user_ip` varchar(45) DEFAULT NULL,
        `reaction` varchar(20) NOT NULL DEFAULT 'like',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_reaction` (`post_id`, `user_ip`),
        KEY `idx_post_reaction` (`post_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
} catch (Exception $e) { /* Already exists */ }

if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    echo json_encode(['success' => false, 'message' => 'POST required']);
    exit;
}

$data = json_decode(file_get_contents('php://input'), true);
$postId   = (int)($data['post_id'] ?? 0);
$reaction = preg_replace('/[^a-z]/', '', strtolower($data['reaction'] ?? 'like'));
$valid    = ['like', 'love', 'fire', 'smart'];

if ($postId <= 0 || !in_array($reaction, $valid)) {
    echo json_encode(['success' => false, 'message' => 'Invalid data']);
    exit;
}

try {
    // Check if user already reacted
    $check = $db->prepare("SELECT reaction FROM reactions WHERE post_id = :pid AND user_ip = :ip");
    $check->execute([':pid' => $postId, ':ip' => $userIp]);
    $existing = $check->fetch();

    if ($existing) {
        if ($existing['reaction'] === $reaction) {
            // Toggle off: remove reaction
            $db->prepare("DELETE FROM reactions WHERE post_id = :pid AND user_ip = :ip")
               ->execute([':pid' => $postId, ':ip' => $userIp]);
            $userReaction = null;
        } else {
            // Change reaction
            $db->prepare("UPDATE reactions SET reaction = :r WHERE post_id = :pid AND user_ip = :ip")
               ->execute([':r' => $reaction, ':pid' => $postId, ':ip' => $userIp]);
            $userReaction = $reaction;
        }
    } else {
        // New reaction
        $db->prepare("INSERT INTO reactions (post_id, user_ip, reaction) VALUES (:pid, :ip, :r)")
           ->execute([':pid' => $postId, ':ip' => $userIp, ':r' => $reaction]);
        $userReaction = $reaction;
    }

    // Get counts for all reactions
    $counts = [];
    foreach ($valid as $r) {
        $q = $db->prepare("SELECT COUNT(*) FROM reactions WHERE post_id = :pid AND reaction = :r");
        $q->execute([':pid' => $postId, ':r' => $r]);
        $counts[$r] = (int)$q->fetchColumn();
    }
    $total = array_sum($counts);

    echo json_encode([
        'success'      => true,
        'user_reaction'=> $userReaction,
        'counts'       => $counts,
        'total'        => $total,
    ]);

} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
