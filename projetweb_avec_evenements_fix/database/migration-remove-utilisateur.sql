-- =====================================================
-- Migration: Supprimer la dépendance à la table utilisateur
-- =====================================================

-- Étape 1: Désactiver les vérifications de clé étrangère
SET FOREIGN_KEY_CHECKS=0;

-- Étape 2: Supprimer la table utilisateur si elle existe
DROP TABLE IF EXISTS `utilisateur`;

-- Étape 3: Ajouter les colonnes manquantes dans inscription
ALTER TABLE `inscription`
  ADD COLUMN `nom` VARCHAR(100) NOT NULL DEFAULT '' AFTER `idInscription`,
  ADD COLUMN `prenom` VARCHAR(100) NOT NULL DEFAULT '' AFTER `nom`,
  ADD COLUMN `email` VARCHAR(255) NOT NULL DEFAULT '' AFTER `prenom`;

-- Étape 4: Ajouter les index manquants
ALTER TABLE `inscription`
  ADD INDEX `idx_email` (`email`),
  ADD INDEX `idx_evenement` (`idEvenement`);

-- Étape 5: Supprimer la colonne idUtilisateur de inscription
ALTER TABLE `inscription` DROP COLUMN `idUtilisateur`;

-- Étape 6: Recréer la contrainte unique sur inscription
ALTER TABLE `inscription` DROP INDEX `unique_inscription`;
ALTER TABLE `inscription` ADD UNIQUE KEY `unique_inscription` (`idEvenement`, `email`);

-- Étape 7: Modifier idOrganisateur dans evenement
ALTER TABLE `evenement` MODIFY `idOrganisateur` VARCHAR(100) DEFAULT NULL;

-- Étape 8: Supprimer la contrainte de clé étrangère de evenement
ALTER TABLE `evenement` DROP FOREIGN KEY `evenement_ibfk_1`;

-- Réactiver les vérifications des clés étrangères
SET FOREIGN_KEY_CHECKS=1;

-- =====================================================
-- Migration terminée!
-- =====================================================
