-- Correction de la contrainte de clé étrangère
-- Exécutez ce script dans phpMyAdmin pour corriger l'erreur

SET FOREIGN_KEY_CHECKS=0;

-- Supprimer la contrainte de clé étrangère sur evenement
ALTER TABLE `evenement` DROP FOREIGN KEY `evenement_ibfk_1`;

-- Supprimer la contrainte de clé étrangère sur inscription (idUtilisateur)
ALTER TABLE `inscription` DROP FOREIGN KEY `inscription_ibfk_1`;

-- Supprimer la colonne idUtilisateur de la table inscription
ALTER TABLE `inscription` DROP COLUMN `idUtilisateur`;

-- Mettre à jour le type de idOrganisateur dans evenement pour autoriser du texte libre
ALTER TABLE `evenement` MODIFY `idOrganisateur` VARCHAR(100) DEFAULT NULL;

-- Réactiver les vérifications
SET FOREIGN_KEY_CHECKS=1;
