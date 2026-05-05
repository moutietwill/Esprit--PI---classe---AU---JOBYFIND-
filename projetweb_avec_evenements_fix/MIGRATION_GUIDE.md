# Migration vers une Base de Données sans Table Utilisateur

## Situation actuelle
Votre projet utilisait une table `utilisateur` qui a maintenant été supprimée. Voici comment adapter votre application.

---

## ✅ Changements effectués

### 1. **Modèle Inscription** 
- ✓ Suppression de la propriété `idUtilisateur`
- ✓ Conservation des propriétés: `nom`, `prenom`, `email`
- ✓ Méthodes getters/setters mises à jour

### 2. **Schema SQL** 
- ✓ Suppression de la table `utilisateur` du schéma
- ✓ Table `inscription` indépendante:
  - `idInscription` (clé primaire)
  - `idEvenement` (clé étrangère)
  - `nom` (VARCHAR 100)
  - `prenom` (VARCHAR 100)
  - `email` (VARCHAR 255)
  - `dateInscription` (TIMESTAMP)
  - `statut` (VARCHAR 50)

### 3. **Table Événement**
- ✓ `idOrganisateur` changé de INT à VARCHAR(100)
- ✓ Suppression de la clé étrangère vers `utilisateur`
- ✓ `idOrganisateur` peut maintenant contenir du texte libre ou un identifiant

### 4. **Contrôleurs**
- ✓ AdminController: Suppression de la création automatique d'utilisateurs
- ✓ EventsController: Inscription directe sans passage par la table `utilisateur`
- ✓ Controller: Méthodes `findOrCreateUtilisateur()` et `alreadyInscrit()` supprimées

---

## 🚀 Exécution de la Migration

### Option 1: Exécuter le script SQL via phpMyAdmin
1. Ouvrez phpMyAdmin
2. Allez dans votre base de données `gestion_evenements`
3. Cliquez sur l'onglet **SQL**
4. Copiez le contenu de `database/migration-remove-utilisateur.sql`
5. Collez-le dans l'éditeur SQL
6. Cliquez sur **Exécuter**

### Option 2: Exécuter le script PHP (plus simple)
Accédez à cette URL dans votre navigateur:
```
http://localhost/projetweb_avec_evenements_fix/migrate-remove-utilisateur.php
```

---

## 📋 Vérification post-migration

Après la migration, vérifiez que:

1. ✅ La table `utilisateur` est supprimée
2. ✅ La table `inscription` a les colonnes: `nom`, `prenom`, `email`
3. ✅ La table `evenement` n'a pas de clé étrangère vers `utilisateur`
4. ✅ Aucune données anciennes ne reste

### Requête de vérification SQL:
```sql
-- Vérifier la structure de inscription
DESCRIBE inscription;

-- Vérifier les données
SELECT * FROM inscription LIMIT 5;

-- Vérifier que utilisateur n'existe pas
SHOW TABLES LIKE 'utilisateur';
```

---

## 🔄 Flux d'inscription (nouveau)

### Sur le **FRONT-END**:
1. Formulaire d'inscription avec: **Prénom**, **Nom**, **Email**
2. Envoi AJAX vers `/events/inscrire/{idEvenement}`

### Sur le **BACK-END**:
1. ✅ Validation des données
2. ✅ Vérification du formulaire
3. ✅ Insertion directe dans la table `inscription`
4. ✅ Réponse JSON au front-end

**Plus besoin de créer d'utilisateur!**

---

## 📝 Exemple d'utilisation dans le code

```php
// Front-end: Envoi AJAX
fetch('/events/inscrire/1', {
    method: 'POST',
    headers: {'Content-Type': 'application/x-www-form-urlencoded'},
    body: new URLSearchParams({
        prenom: 'Jean',
        nom: 'Dupont',
        email: 'jean@example.com'
    })
})
.then(r => r.json())
.then(data => console.log(data));

// Back-end: Traitement
$inscription = new Inscription([
    'idEvenement' => 1,
    'nom' => 'Dupont',
    'prenom' => 'Jean',
    'email' => 'jean@example.com',
    'dateInscription' => date('Y-m-d'),
    'statut' => 'confirmée'
]);

$this->saveInscription($inscription);
```

---

## ❌ Code supprimé

Les méthodes suivantes ont été supprimées du Controller:
- `findOrCreateUtilisateur()` - Plus besoin de créer d'utilisateur
- `alreadyInscrit()` - Vérification simplifiée au niveau de la BD avec UNIQUE key

---

## 📚 Fichiers modifiés

1. `models/Inscription.php` - Suppression de `idUtilisateur`
2. `controllers/Controller.php` - Simplification de `saveInscription()`
3. `controllers/EventsController.php` - Adaptation de `inscrire()`
4. `controllers/AdminController.php` - Suppression de la création d'utilisateurs
5. `database/schema.sql` - Mise à jour du schéma
6. `database/migration-remove-utilisateur.sql` - Script de migration

---

## 🎯 Prochaines étapes

1. **Exécutez la migration** via phpMyAdmin ou le script PHP
2. **Testez une inscription** sur le front-end
3. **Vérifiez les données** dans phpMyAdmin
4. **Adaptez les vues** si nécessaire (formulaires, affichage des inscriptions)

---

## 📞 Support

Si vous avez des erreurs lors de la migration, assurez-vous que:
- ✅ La table `inscription` existe
- ✅ La base de données est correctement sélectionnée
- ✅ Vous avez les droits administrateur sur la base de données
