-- =====================================================
-- Quiz Management Tables
-- =====================================================

USE `gestion_evenements`;

-- Table: quizz
CREATE TABLE IF NOT EXISTS `quizz` (
  `id_quiz` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `domaine` VARCHAR(100) NOT NULL,
  `niveau` VARCHAR(50) NOT NULL,
  `dateCreation` DATE NOT NULL,
  `id_createur` INT,
  FOREIGN KEY (`id_createur`) REFERENCES `utilisateurs`(`id`) ON DELETE SET NULL
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: question
CREATE TABLE IF NOT EXISTS `question` (
  `id_question` INT AUTO_INCREMENT PRIMARY KEY,
  `id_quiz` INT NOT NULL,
  `enonce` TEXT NOT NULL,
  `type` VARCHAR(50) NOT NULL,
  `points` INT DEFAULT 1,
  `dateCreation` DATE NOT NULL,
  FOREIGN KEY (`id_quiz`) REFERENCES `quizz`(`id_quiz`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: reponse
CREATE TABLE IF NOT EXISTS `reponse` (
  `id_reponse` INT AUTO_INCREMENT PRIMARY KEY,
  `id_question` INT NOT NULL,
  `texte` TEXT NOT NULL,
  `est_correcte` TINYINT(1) DEFAULT 0,
  `justification` TEXT,
  `dateCreation` DATE NOT NULL,
  FOREIGN KEY (`id_question`) REFERENCES `question`(`id_question`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- Table: participation_quizz
CREATE TABLE IF NOT EXISTS `participation_quizz` (
  `id_participation` INT AUTO_INCREMENT PRIMARY KEY,
  `id_user` INT NOT NULL,
  `id_quiz` INT NOT NULL,
  `score` FLOAT DEFAULT 0,
  `date_participation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  FOREIGN KEY (`id_user`) REFERENCES `utilisateurs`(`id`) ON DELETE CASCADE,
  FOREIGN KEY (`id_quiz`) REFERENCES `quizz`(`id_quiz`) ON DELETE CASCADE
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;
