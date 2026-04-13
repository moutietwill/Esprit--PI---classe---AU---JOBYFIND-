-- ===================================================================
-- JobyFind Database Setup
-- Import this file into phpMyAdmin to create the database and tables
-- ===================================================================

CREATE DATABASE IF NOT EXISTS `projet_web` CHARACTER SET utf8mb4 COLLATE utf8mb4_unicode_ci;
USE `projet_web`;

-- ===================================================================
-- Table: posts (formations/publications)
-- ===================================================================
CREATE TABLE IF NOT EXISTS `posts` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `title` varchar(255) NOT NULL,
  `excerpt` text,
  `content` longtext,
  `category` varchar(100),
  `instructor` varchar(255),
  `price` decimal(10,2) DEFAULT '0.00',
  `rating` float DEFAULT '0',
  `reviews_count` int(11) DEFAULT '0',
  `students_count` int(11) DEFAULT '0',
  `duration_hours` float,
  `cover_image` varchar(500),
  `status` varchar(50) DEFAULT 'published',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  KEY `idx_category` (`category`),
  KEY `idx_status` (`status`),
  FULLTEXT KEY `ft_search` (`title`,`excerpt`,`content`)
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
-- Table: users (utilisateurs)
-- ===================================================================
CREATE TABLE IF NOT EXISTS `users` (
  `id` int(11) NOT NULL AUTO_INCREMENT,
  `username` varchar(100) NOT NULL,
  `email` varchar(255) NOT NULL,
  `password` varchar(255) NOT NULL,
  `is_admin` tinyint(1) DEFAULT '0',
  `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
  PRIMARY KEY (`id`),
  UNIQUE KEY `username` (`username`),
  UNIQUE KEY `email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- ===================================================================
-- Insert Sample Data
-- ===================================================================
INSERT INTO `posts` (`title`, `excerpt`, `content`, `category`, `instructor`, `price`, `rating`, `reviews_count`, `students_count`, `duration_hours`, `status`) VALUES
('Apprendre React.js', 'Maîtrisez les fondamentaux de React et créez des applications interactives.', 'Cours complet sur React.js - Apprenez React depuis les bases jusqu\'aux concepts avancés. Vous découvrirez les hooks, le state management et bien plus.', 'Développement', 'John Doe', '29.99', '4.8', '120', '3200', '8.5', 'published'),
('Développement Mobile avec Flutter', 'Créez des applications mobiles magnifiques et performantes.', 'Formation complète Flutter - Développez pour iOS et Android avec un seul codebase. Apprenez les meilleures pratiques du développement mobile.', 'Mobile App', 'Jane Smith', '39.99', '4.9', '85', '2100', '12', 'published'),
('UX/UI Design Complet', 'Apprenez à concevoir des interfaces utilisateur magnifiques et intuitives.', 'Cours complet de design UX/UI - Principes, outils et pratiques modernes. De la conception à la prototypage.', 'Design', 'Mike Johnson', '24.99', '4.7', '200', '5000', '10', 'published'),
('Marketing Digital 2024', 'Stratégies modernes de marketing digital pour maximiser votre présence en ligne.', 'Les dernières stratégies et outils de marketing digital pour 2024. SEO, publicités, réseaux sociaux et bien plus.', 'Marketing', 'Sarah Connor', '19.99', '4.6', '150', '4500', '6', 'published'),
('Gestion de Projet Agile', 'Maîtrisez les méthodologies Agile et Scrum pour une gestion de projet efficace.', 'Certification Scrum Master - Apprenez à gérer des projets avec Agile. Sprints, retrospectives et planification.', 'Gestion', 'Tom Wilson', '34.99', '4.8', '100', '2800', '8', 'published'),
('Python pour les Débutants', 'Apprenez Python pas à pas, du zéro à la maîtrise.', 'Langage puissant et facile à apprendre. Variables, boucles, fonctions et programmation orientée objet.', 'Développement', 'Alice Brown', '21.99', '4.6', '180', '4200', '9', 'published');
