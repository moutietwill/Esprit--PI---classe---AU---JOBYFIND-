<?php
require_once __DIR__ . '/connexion.php';

try {
    $db = Config::GetConnexion();
    
    // Create categories table
    $db->exec("CREATE TABLE IF NOT EXISTS `categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `name` varchar(100) NOT NULL UNIQUE,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    // Insert Default Categories if empty
    $db->exec("INSERT IGNORE INTO `categories` (`name`) VALUES ('Développement'), ('Design'), ('Marketing'), ('Gestion'), ('Communication');");

    // Ensure posts table has correct schema or add columns if missing
    $columns = $db->query("SHOW COLUMNS FROM posts")->fetchAll(PDO::FETCH_ASSOC);
    $columnNames = array_column($columns, 'Field');

    if (!in_array('category_id', $columnNames)) {
        $db->exec("ALTER TABLE posts ADD COLUMN category_id INT(11) DEFAULT NULL AFTER category");
        $db->exec("ALTER TABLE posts ADD CONSTRAINT fk_user_category FOREIGN KEY (category_id) REFERENCES categories(id) ON DELETE SET NULL");
        
        // map existing category text to category_id
        $db->exec("UPDATE posts p JOIN categories c ON p.category = c.name SET p.category_id = c.id;");
    }

    // Create stories table for the advanced story feature
    $db->exec("CREATE TABLE IF NOT EXISTS `stories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `post_id` int(11) DEFAULT NULL,
        `title` varchar(180) NOT NULL,
        `content` text,
        `cta_label` varchar(80) DEFAULT 'Lire le blog',
        `media_image` varchar(500) DEFAULT NULL,
        `status` varchar(30) DEFAULT 'published',
        `views_count` int unsigned NOT NULL DEFAULT 0,
        `starts_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
        `expires_at` datetime NOT NULL,
        `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
        `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `idx_story_status_dates` (`status`, `starts_at`, `expires_at`),
        KEY `idx_story_post` (`post_id`),
        CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE SET NULL
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");

    echo "Database successfully checked and updated.\n";
} catch (Exception $e) {
    echo "Error updating database: " . $e->getMessage() . "\n";
}
?>
