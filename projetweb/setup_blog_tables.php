<?php
require_once __DIR__ . '/connexion.php';
$db = Config::GetConnexion();

header('Content-Type: text/plain; charset=utf-8');

$steps = [];

try {
    // 1. posts table
    $db->exec("CREATE TABLE IF NOT EXISTS `posts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `title` varchar(255) NOT NULL,
        `excerpt` text,
        `content` longtext,
        `category_id` int(11) DEFAULT NULL,
        `category` varchar(100) DEFAULT NULL,
        `instructor` varchar(255) DEFAULT NULL,
        `price` decimal(10,2) DEFAULT '0.00',
        `rating` float DEFAULT '0',
        `reviews_count` int(11) DEFAULT '0',
        `students_count` int(11) DEFAULT '0',
        `views_count` int(11) DEFAULT '0',
        `duration_hours` float DEFAULT NULL,
        `cover_image` varchar(500) DEFAULT NULL,
        `status` varchar(50) DEFAULT 'published',
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $steps[] = "✅ Table `posts` OK";

    // Ensure columns exist in posts
    $existingCols = $db->query("SHOW COLUMNS FROM posts")->fetchAll(PDO::FETCH_COLUMN);
    foreach (['rating'=>'FLOAT DEFAULT 0', 'reviews_count'=>'INT DEFAULT 0', 'views_count'=>'INT DEFAULT 0', 'category_id'=>'INT DEFAULT NULL', 'cover_image'=>'VARCHAR(500) DEFAULT NULL'] as $col => $def) {
        if (!in_array($col, $existingCols)) {
            $db->exec("ALTER TABLE posts ADD COLUMN `$col` $def");
            $steps[] = "✅ Added column `$col` to posts";
        }
    }

    // 2. comments table
    $db->exec("CREATE TABLE IF NOT EXISTS `comments` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `post_id` int(11) NOT NULL,
        `user_id` int(11) DEFAULT NULL,
        `user_name` varchar(100) DEFAULT 'Anonymous',
        `content` text NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_post` (`post_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $steps[] = "✅ Table `comments` OK";

    // 3. likes table
    $db->exec("CREATE TABLE IF NOT EXISTS `likes` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `post_id` int(11) NOT NULL,
        `user_id` int(11) DEFAULT 1,
        `user_ip` varchar(45) DEFAULT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_like` (`post_id`, `user_ip`),
        KEY `idx_post_like` (`post_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $steps[] = "✅ Table `likes` OK";

    // 4. post_ratings table
    $db->exec("CREATE TABLE IF NOT EXISTS `post_ratings` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `post_id` int(11) NOT NULL,
        `user_id` int(11) DEFAULT 1,
        `user_ip` varchar(45) DEFAULT NULL,
        `rating` tinyint(1) NOT NULL,
        `created_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp NOT NULL DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        UNIQUE KEY `unique_rating` (`post_id`, `user_ip`),
        KEY `idx_post_rating` (`post_id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    $steps[] = "✅ Table `post_ratings` OK";

    // 5. reactions table (new!)
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
    $steps[] = "✅ Table `reactions` OK";

    echo implode("\n", $steps);
    echo "\n\n🎉 All tables are ready!";

} catch (Exception $e) {
    echo "❌ Error: " . $e->getMessage();
}
?>
