<?php
require_once __DIR__ . '/config.php';

$db = Config::GetConnexion();

try {
    // Créer la table post_ratings
    $db->exec("
        CREATE TABLE IF NOT EXISTS `post_ratings` (
            `id`         INT(11) NOT NULL AUTO_INCREMENT,
            `post_id`    INT(11) NOT NULL,
            `user_id`    INT(11) NOT NULL DEFAULT 1,
            `user_ip`    VARCHAR(45),
            `rating`     TINYINT(1) NOT NULL CHECK (`rating` BETWEEN 1 AND 5),
            `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
            `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
            PRIMARY KEY (`id`),
            UNIQUE KEY `unique_rating` (`post_id`, `user_ip`),
            KEY `idx_post` (`post_id`),
            CONSTRAINT `ratings_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
        ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci
    ");

    // S'assurer que la table posts a bien les colonnes rating et reviews_count
    $cols = $db->query("SHOW COLUMNS FROM posts LIKE 'rating'")->fetch();
    if (!$cols) {
        $db->exec("ALTER TABLE posts ADD COLUMN `rating` FLOAT DEFAULT 0 AFTER `views_count`");
    }
    $cols2 = $db->query("SHOW COLUMNS FROM posts LIKE 'reviews_count'")->fetch();
    if (!$cols2) {
        $db->exec("ALTER TABLE posts ADD COLUMN `reviews_count` INT(11) DEFAULT 0 AFTER `rating`");
    }

    echo "<h2 style='font-family:sans-serif;color:green;'>✅ Table <code>post_ratings</code> créée avec succès !</h2>";
    echo "<p style='font-family:sans-serif;'>Vous pouvez maintenant supprimer ce fichier ou retourner au <a href='view/frontoffice.php'>front-office</a>.</p>";

} catch (Exception $e) {
    echo "<h2 style='font-family:sans-serif;color:red;'>❌ Erreur : " . $e->getMessage() . "</h2>";
}
?>
