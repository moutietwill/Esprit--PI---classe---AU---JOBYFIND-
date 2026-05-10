<?php
/**
 * Script d'initialisation du blog
 * Exécutez ce fichier une seule fois pour créer les tables du blog
 */

require_once __DIR__ . '/config/Database.php';

try {
    $db = Database::getInstance()->getConnection();
    
    echo "🚀 Initialisation du blog...\n\n";
    
    // Créer la table des posts
    echo "✓ Création de la table blog_posts...";
    $db->exec("CREATE TABLE IF NOT EXISTS `blog_posts` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `titre` varchar(255) NOT NULL,
        `slug` varchar(255),
        `contenu` longtext NOT NULL,
        `resume` text,
        `auteur_id` int(11) NOT NULL,
        `categorie` varchar(100),
        `image_couverture` varchar(255),
        `statut` varchar(20) DEFAULT 'brouillon',
        `vues` int(11) DEFAULT 0,
        `date_creation` timestamp DEFAULT CURRENT_TIMESTAMP,
        `date_modification` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
        `date_publication` datetime,
        PRIMARY KEY (`id`),
        KEY `auteur_id` (`auteur_id`),
        KEY `statut` (`statut`),
        FULLTEXT KEY `recherche` (`titre`, `contenu`, `resume`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo " ✅\n";
    
    // Créer la table des commentaires
    echo "✓ Création de la table blog_commentaires...";
    $db->exec("CREATE TABLE IF NOT EXISTS `blog_commentaires` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `post_id` int(11) NOT NULL,
        `auteur_id` int(11),
        `nom` varchar(100),
        `email` varchar(100),
        `contenu` text NOT NULL,
        `approuve` tinyint(1) DEFAULT 0,
        `date_creation` timestamp DEFAULT CURRENT_TIMESTAMP,
        PRIMARY KEY (`id`),
        KEY `post_id` (`post_id`),
        CONSTRAINT `blog_commentaires_post` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE CASCADE
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo " ✅\n";
    
    // Créer la table des catégories
    echo "✓ Création de la table blog_categories...";
    $db->exec("CREATE TABLE IF NOT EXISTS `blog_categories` (
        `id` int(11) NOT NULL AUTO_INCREMENT,
        `nom` varchar(100) NOT NULL UNIQUE,
        `slug` varchar(100),
        `description` text,
        `icone` varchar(50),
        PRIMARY KEY (`id`)
    ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci");
    echo " ✅\n";
    
    // Insérer les catégories par défaut
    echo "✓ Insertion des catégories par défaut...";
    $check = $db->query("SELECT COUNT(*) FROM blog_categories");
    if ($check->fetchColumn() == 0) {
        $db->exec("INSERT INTO blog_categories (nom, slug, description, icone) VALUES 
            ('Tutoriels', 'tutoriels', 'Guides et tutoriels pratiques', 'fa-book'),
            ('Actualités', 'actualites', 'Les dernières nouvelles et mises à jour', 'fa-newspaper'),
            ('Conseils', 'conseils', 'Conseils et bonnes pratiques', 'fa-lightbulb')");
    }
    echo " ✅\n";
    
    // Créer le répertoire d'uploads
    echo "✓ Création du répertoire d'uploads...";
    if (!is_dir(__DIR__ . '/public/uploads/blog')) {
        @mkdir(__DIR__ . '/public/uploads/blog', 0777, true);
    }
    echo " ✅\n";
    
    echo "\n✨ Blog initialisé avec succès!\n";
    echo "\n📚 Prochaines étapes:\n";
    echo "1. Accédez à /admin/blog pour créer vos premiers posts\n";
    echo "2. Accédez à /blog pour voir le blog en ligne\n";
    echo "3. Ajouter le lien du blog au menu principal\n";
    
} catch (Exception $e) {
    echo "❌ Erreur: " . $e->getMessage() . "\n";
    die(1);
}
?>
