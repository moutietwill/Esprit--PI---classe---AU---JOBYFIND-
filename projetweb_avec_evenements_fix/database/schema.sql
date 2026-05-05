-- =====================================================
-- Base de données: gestion_evenements
-- Créé: 2026-04-12
-- Description: Gestion des événements et inscriptions
-- =====================================================

-- =====================================================
-- Table: Evenement
-- =====================================================
CREATE TABLE IF NOT EXISTS `evenement` (
  `idEvenement` INT AUTO_INCREMENT PRIMARY KEY,
  `titre` VARCHAR(255) NOT NULL,
  `description` TEXT,
  `date` DATE NOT NULL,
  `heure` TIME,
  `lieu` VARCHAR(255) NOT NULL,
  `categorie` VARCHAR(50) COMMENT 'tech, emploi, culture, formation',
  `organisateur` VARCHAR(255),
  `idOrganisateur` VARCHAR(100),
  `image` VARCHAR(255) DEFAULT NULL COMMENT 'Chemin image evenement',
  `intervenants` TEXT COMMENT 'Liste des intervenants',
  `inscrits` INT DEFAULT 0,
  `max` INT DEFAULT 0 COMMENT 'Nombre maximum de participants',
  `statut` VARCHAR(50) DEFAULT 'Ouvert' COMMENT 'Ouvert, Complet, Annulé',
  `programme` TEXT COMMENT 'Programme détaillé',
  `documents` TEXT COMMENT 'URLs des documents',
  `replays` TEXT COMMENT 'URLs des replays vidéo',
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `date_modification` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  INDEX `idx_date` (`date`),
  INDEX `idx_categorie` (`categorie`),
  INDEX `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: Inscription
-- =====================================================
CREATE TABLE IF NOT EXISTS `inscription` (
  `idInscription` INT AUTO_INCREMENT PRIMARY KEY,
  `idEvenement` INT NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `prenom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) NOT NULL,
  `dateInscription` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `statut` VARCHAR(50) DEFAULT 'Confirmée' COMMENT 'Confirmée, Annulée, Présent, Absent',
  UNIQUE KEY `unique_inscription` (`idEvenement`, `email`),
  FOREIGN KEY (`idEvenement`) REFERENCES `evenement`(`idEvenement`) ON DELETE CASCADE,
  INDEX `idx_evenement` (`idEvenement`),
  INDEX `idx_statut` (`statut`),
  INDEX `idx_email` (`email`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Données d'exemples pour Evenement
-- =====================================================
INSERT IGNORE INTO `evenement` (`idEvenement`, `titre`, `description`, `date`, `heure`, `lieu`, `categorie`, `organisateur`, `idOrganisateur`, `intervenants`, `inscrits`, `max`, `statut`, `programme`) VALUES
(1, 'Tunisia Tech Summit 2026', 'Une conférence majeure sur les dernières technologies', '2026-05-08', '09:00', 'Palais des Congrès, Tunis', 'tech', 'Mohamed Ben Ali', '1', 'Ahmed Trabelsi, Leila Khazri', 380, 500, 'Ouvert', 'Accueil, Conférences IA, Pause, Workshops'),
(2, 'Hackathon Innovation Sociale', 'Un hackathon pour développer des solutions sociales', '2026-05-15', '10:00', 'Hub Sfax, Sfax', 'tech', 'Leila Khazri', '2', 'Rami Ben Fraj', 120, 150, 'Ouvert', 'Présentation, Hack, Dîner, Présentation projets'),
(3, 'Journée Portes Ouvertes Emploi', 'Rencontrez les meilleures entreprises tunisiennes', '2026-05-22', '08:30', 'Palais des Expositions, Tunis', 'emploi', 'Jobyfind', '6', 'Entreprises partenaires', 610, 800, 'Ouvert', 'Accueil, Stands entreprises, CV déposés, Entretiens'),
(4, 'Festival de Cinéma Arabe', 'Festival de cinéma avec films et débats', '2026-06-01', '18:00', 'Cité de la Culture, Tunis', 'culture', 'Sonia Mansour', '8', 'Réalisateurs invités', 200, 200, 'Complet', 'Ouverture, Projections, Débat, Clôture');
