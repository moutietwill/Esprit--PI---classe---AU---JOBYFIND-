-- =====================================================
-- Tables pour le système de Blog
-- Ajout au schéma existant: gestion_evenements
-- =====================================================

-- =====================================================
-- Table: categories (Catégories de blog)
-- =====================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `name` VARCHAR(100) NOT NULL UNIQUE,
  `slug` VARCHAR(120),
  `description` TEXT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: posts (Articles de blog)
-- =====================================================
CREATE TABLE IF NOT EXISTS `posts` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `title` VARCHAR(255) NOT NULL,
  `content` LONGTEXT NOT NULL,
  `category_id` INT,
  `category` VARCHAR(100),
  `cover_image` VARCHAR(255),
  `excerpt` VARCHAR(500),
  `status` VARCHAR(50) DEFAULT 'draft' COMMENT 'draft, published, archived',
  `views` INT DEFAULT 0,
  `likes` INT DEFAULT 0,
  `author_id` INT,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`category_id`) REFERENCES `categories`(`id`) ON DELETE SET NULL,
  INDEX `idx_status` (`status`),
  INDEX `idx_category` (`category_id`),
  INDEX `idx_created` (`created_at`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: comments (Commentaires)
-- =====================================================
CREATE TABLE IF NOT EXISTS `comments` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT NOT NULL,
  `author_name` VARCHAR(100) NOT NULL,
  `author_email` VARCHAR(255) NOT NULL,
  `content` TEXT NOT NULL,
  `status` VARCHAR(50) DEFAULT 'pending' COMMENT 'pending, approved, spam',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `updated_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  INDEX `idx_post` (`post_id`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: post_ratings (Évaluations des posts)
-- =====================================================
CREATE TABLE IF NOT EXISTS `post_ratings` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT NOT NULL,
  `user_ip` VARCHAR(45),
  `rating` INT COMMENT '1-5 stars',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_rating` (`post_id`, `user_ip`),
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  INDEX `idx_post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: post_likes (J'aime)
-- =====================================================
CREATE TABLE IF NOT EXISTS `post_likes` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `post_id` INT NOT NULL,
  `user_ip` VARCHAR(45),
  `liked` BOOLEAN DEFAULT 1,
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  UNIQUE KEY `unique_like` (`post_id`, `user_ip`),
  FOREIGN KEY (`post_id`) REFERENCES `posts`(`id`) ON DELETE CASCADE,
  INDEX `idx_post` (`post_id`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Données d'exemples pour Catégories
-- =====================================================
INSERT IGNORE INTO `categories` (`id`, `name`, `slug`, `description`) VALUES
(1, 'Technologie', 'technologie', 'Articles sur les dernières technologies'),
(2, 'Formation', 'formation', 'Guides et tutoriels de formation'),
(3, 'Emploi', 'emploi', 'Conseils et actualités emploi'),
(4, 'Événements', 'evenements', 'Couverture et annonces d\'événements'),
(5, 'Général', 'general', 'Articles généraux');

-- =====================================================
-- Données d'exemples pour Posts
-- =====================================================
INSERT IGNORE INTO `posts` (`id`, `title`, `content`, `category_id`, `category`, `cover_image`, `excerpt`, `status`, `views`, `likes`, `created_at`) VALUES
(1, 'Introduction à React.js', 'React est une bibliothèque JavaScript pour construire des interfaces utilisateur avec des composants réutilisables...', 1, 'Technologie', 'public/assets/images/blog/react.jpg', 'Découvrez les bases de React.js pour le développement web moderne', 'published', 156, 23, NOW()),
(2, 'Les Meilleures Pratiques en PHP', 'Dans cet article, nous explorons les meilleures pratiques pour écrire du code PHP de qualité...', 1, 'Technologie', 'public/assets/images/blog/php.jpg', 'Apprenez à écrire du PHP professionnel et maintenable', 'published', 89, 12, NOW()),
(3, 'Débuter avec Node.js', 'Node.js est un environnement d\'exécution JavaScript côté serveur qui permet de développer des applications web scalables...', 2, 'Formation', 'public/assets/images/blog/nodejs.jpg', 'Un guide complet pour débuter avec Node.js', 'published', 210, 45, NOW());
