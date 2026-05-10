-- ===================================================================
-- Blog Tables Schema - Integration with gestion_evenements
-- Add these tables to the gestion_evenements database
-- ===================================================================

-- ===================================================================
-- Table: categories (catégories de blog)
-- ===================================================================
CREATE TABLE IF NOT EXISTS `categories` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `name` varchar(100) NOT NULL UNIQUE,
  `description` text,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_name` (`name`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- Table: posts (formations/publications)
-- ===================================================================
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `excerpt` text,
  `content` longtext,
  `category_id` int(11),
  `category` varchar(100),
  `instructor` varchar(255),
  `price` decimal(10,2) DEFAULT '0.00',
  `rating` float DEFAULT '0',
  `reviews_count` int(11) DEFAULT '0',
  `students_count` int(11) DEFAULT '0',
  `views_count` int(11) DEFAULT '0',
  `duration_hours` float,
  `cover_image` varchar(500),
  `status` varchar(50) DEFAULT 'published',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_category_id` (`category_id`),
  KEY `idx_status` (`status`),
  FULLTEXT KEY `ft_search` (`title`,`excerpt`,`content`),
  CONSTRAINT `posts_ibfk_1` FOREIGN KEY (`category_id`) REFERENCES `categories` (`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- Table: comments (commentaires)
-- ===================================================================
CREATE TABLE IF NOT EXISTS `comments` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11),
  `user_name` varchar(100) DEFAULT 'Anonymous',
  `content` text NOT NULL,
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_post` (`post_id`),
  CONSTRAINT `comments_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- Table: likes (j'aime)
-- ===================================================================
CREATE TABLE IF NOT EXISTS `likes` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `post_id` int(11) NOT NULL,
  `user_id` int(11),
  `user_ip` varchar(45),
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `unique_like` (`post_id`,`user_ip`),
  KEY `idx_post` (`post_id`),
  CONSTRAINT `likes_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `posts` (`id`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- Table: stories (metier avance)
-- ===================================================================
CREATE TABLE IF NOT EXISTS `stories` (
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
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- Insert Sample Categories
-- ===================================================================
INSERT IGNORE INTO `categories` (`id`, `name`, `description`) VALUES
(1, 'Teknoloji', 'Articles sur les technologies web'),
(2, 'Affaires', 'Articles sur les affaires et entrepreneuriat'),
(3, 'Design', 'Articles sur le design et l\'UI/UX'),
(4, 'Marketing', 'Articles sur le marketing digital'),
(5, 'Lifestyle', 'Articles sur le lifestyle');
