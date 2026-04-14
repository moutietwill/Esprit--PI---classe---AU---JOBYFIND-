-- =====================================================
-- Base de données: gestion_evenements
-- Créé: 2026-04-12
-- Description: Gestion des événements et inscriptions
-- =====================================================

-- =====================================================
-- Table: Utilisateur
-- =====================================================
CREATE TABLE IF NOT EXISTS `utilisateur` (
  `idUtilisateur` INT AUTO_INCREMENT PRIMARY KEY,
  `prenom` VARCHAR(100) NOT NULL,
  `nom` VARCHAR(100) NOT NULL,
  `email` VARCHAR(255) UNIQUE NOT NULL,
  `role` VARCHAR(50) NOT NULL COMMENT 'Entrepreneur, Mentor, Entreprise',
  `status` VARCHAR(50) NOT NULL DEFAULT 'Actif' COMMENT 'Actif, En attente, Suspendu',
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `date_modification` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  `date_derniere_activite` TIMESTAMP NULL,
  INDEX `idx_email` (`email`),
  INDEX `idx_role` (`role`),
  INDEX `idx_status` (`status`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

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
  `idOrganisateur` INT,
  `intervenants` TEXT COMMENT 'Liste des intervenants',
  `inscrits` INT DEFAULT 0,
  `max` INT DEFAULT 0 COMMENT 'Nombre maximum de participants',
  `statut` VARCHAR(50) DEFAULT 'Ouvert' COMMENT 'Ouvert, Complet, Annulé',
  `programme` TEXT COMMENT 'Programme détaillé',
  `documents` TEXT COMMENT 'URLs des documents',
  `replays` TEXT COMMENT 'URLs des replays vidéo',
  `date_creation` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `date_modification` TIMESTAMP DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
  FOREIGN KEY (`idOrganisateur`) REFERENCES `utilisateur`(`idUtilisateur`) ON DELETE SET NULL,
  INDEX `idx_date` (`date`),
  INDEX `idx_categorie` (`categorie`),
  INDEX `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Table: Inscription
-- =====================================================
CREATE TABLE IF NOT EXISTS `inscription` (
  `idInscription` INT AUTO_INCREMENT PRIMARY KEY,
  `idUtilisateur` INT NOT NULL,
  `idEvenement` INT NOT NULL,
  `dateInscription` TIMESTAMP DEFAULT CURRENT_TIMESTAMP,
  `statut` VARCHAR(50) DEFAULT 'Confirmée' COMMENT 'Confirmée, Annulée, Présent, Absent',
  UNIQUE KEY `unique_inscription` (`idUtilisateur`, `idEvenement`),
  FOREIGN KEY (`idUtilisateur`) REFERENCES `utilisateur`(`idUtilisateur`) ON DELETE CASCADE,
  FOREIGN KEY (`idEvenement`) REFERENCES `evenement`(`idEvenement`) ON DELETE CASCADE,
  INDEX `idx_utilisateur` (`idUtilisateur`),
  INDEX `idx_evenement` (`idEvenement`),
  INDEX `idx_statut` (`statut`)
) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;

-- =====================================================
-- Données d'exemples pour Utilisateur
-- =====================================================
INSERT IGNORE INTO `utilisateur` (`idUtilisateur`, `prenom`, `nom`, `email`, `role`, `status`) VALUES
(1, 'Amine', 'Trabelsi', 'amine.t@gmail.com', 'Entrepreneur', 'Actif'),
(2, 'Sarra', 'Boughanmi', 'sarra.b@outlook.com', 'Mentor', 'Actif'),
(3, 'Karim', 'Mansouri', 'k.mansouri@entreprise.tn', 'Entreprise', 'En attente'),
(4, 'Lina', 'Hamdi', 'lina.h@gmail.com', 'Entrepreneur', 'Actif'),
(5, 'Yassine', 'Karoui', 'y.karoui@gmail.com', 'Mentor', 'Suspendu'),
(6, 'Rania', 'Zouari', 'rania.z@startup.tn', 'Entreprise', 'Actif'),
(7, 'Bilel', 'Ferchichi', 'bilel.f@gmail.com', 'Entrepreneur', 'En attente'),
(8, 'Maha', 'Sfar', 'maha.s@mentor.tn', 'Mentor', 'Actif'),
(9, 'Omar', 'Jouini', 'omar.j@corp.tn', 'Entreprise', 'Actif'),
(10, 'Nour', 'Chaabane', 'nour.c@gmail.com', 'Entrepreneur', 'Suspendu');

-- =====================================================
-- Données d'exemples pour Evenement
-- =====================================================
INSERT IGNORE INTO `evenement` (`idEvenement`, `titre`, `description`, `date`, `heure`, `lieu`, `categorie`, `organisateur`, `idOrganisateur`, `intervenants`, `inscrits`, `max`, `statut`, `programme`) VALUES
(1, 'Tunisia Tech Summit 2026', 'Une conférence majeure sur les dernières technologies', '2026-05-08', '09:00', 'Palais des Congrès, Tunis', 'tech', 'Mohamed Ben Ali', 1, 'Ahmed Trabelsi, Leila Khazri', 380, 500, 'Ouvert', 'Accueil, Conférences IA, Pause, Workshops'),
(2, 'Hackathon Innovation Sociale', 'Un hackathon pour développer des solutions sociales', '2026-05-15', '10:00', 'Hub Sfax, Sfax', 'tech', 'Leila Khazri', 2, 'Rami Ben Fraj', 120, 150, 'Ouvert', 'Présentation, Hack, Dîner, Présentation projets'),
(3, 'Journée Portes Ouvertes Emploi', 'Rencontrez les meilleures entreprises tunisiennes', '2026-05-22', '08:30', 'Palais des Expositions, Tunis', 'emploi', 'Jobyfind', 6, 'Entreprises partenaires', 610, 800, 'Ouvert', 'Accueil, Stands entreprises, CV déposés, Entretiens'),
(4, 'Festival de Cinéma Arabe', 'Festival de cinéma avec films et débats', '2026-06-01', '18:00', 'Cité de la Culture, Tunis', 'culture', 'Sonia Mansour', 8, 'Réalisateurs invités', 200, 200, 'Complet', 'Ouverture, Projections, Débat, Clôture');

-- =====================================================
-- Données d'exemples pour Inscription
-- =====================================================
INSERT IGNORE INTO `inscription` (`idUtilisateur`, `idEvenement`, `statut`) VALUES
(1, 1, 'Confirmée'),
(1, 2, 'Confirmée'),
(2, 1, 'Confirmée'),
(3, 3, 'Confirmée'),
(4, 1, 'Confirmée'),
(4, 2, 'Confirmée'),
(5, 1, 'Confirmée'),
(6, 3, 'Confirmée'),
(7, 2, 'Confirmée'),
(8, 1, 'Confirmée'),
(9, 3, 'Confirmée'),
(10, 1, 'Confirmée');
