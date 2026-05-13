-- =====================================================
-- Table: utilisateurs
-- =====================================================
CREATE TABLE IF NOT EXISTS `utilisateurs` (
  `id` INT AUTO_INCREMENT PRIMARY KEY,
  `first_name` VARCHAR(100),
  `last_name` VARCHAR(100),
  `username` VARCHAR(100) UNIQUE,
  `date_of_birth` DATE,
  `phone` VARCHAR(20),
  `city` VARCHAR(100),
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `password` VARCHAR(255) NOT NULL,
  `role` VARCHAR(50),
  `status` VARCHAR(50) DEFAULT 'Actif',
  `created_at` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `last_login` TIMESTAMP NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
