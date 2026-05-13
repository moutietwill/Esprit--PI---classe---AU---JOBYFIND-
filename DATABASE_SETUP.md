# Installation et Configuration de la Base de Données

## 🎯 Objectif
Intégrer la base de données MySQL existante (`gestion_evenements`) avec l'application PHP MVC.

## ✅ Fichiers Créés

### 1. **Classe Database** (`app/config/Database.php`)
- Connexion PDO à MySQL
- Singleton pattern pour une unique connexion
- Méthodes: execute(), fetchAll(), fetch(), lastInsertId()
- Base de données: `gestion_evenements`

### 2. **EventRepository** (`app/repositories/EventRepository.php`)
- CRUD complet pour les événements
- Méthodes:
  - `getAll()` - Récupère tous les événements
  - `getById($id)` - Récupère un événement par ID
  - `create(Event $event)` - Crée un nouvel événement
  - `update(Event $event)` - Modifie un événement
  - `delete($id)` - Supprime un événement
  - `getByCategory($category)` - Filtre par catégorie
  - `search($term)` - Recherche par titre
  - `count()` - Compte le total

### 3. **UserRepository** (`app/repositories/UserRepository.php`)
- CRUD complet pour les utilisateurs
- Méthodes similaires à EventRepository
- Plus: `getByEmail()`, `getByRole()`, `getByStatus()`, `updateLastActivity()`

### 4. **Schema SQL** (`database/schema.sql`)
- Tables: `utilisateur`, `evenement`, `inscription`
- Données d'exemple pré-chargées
- Relations avec clés étrangères

### 5. **AdminController Modifié** (`app/controllers/AdminController.php`)
- Intégration des repositories
- Gestion des erreurs de base de données
- Fallback si DB indisponible

## 🚀 Installation

### Étape 1 : Exécuter le fichier SQL

#### Via PhpMyAdmin:
1. Ouvrez PhpMyAdmin: `http://localhost/phpmyadmin`
2. Cliquez sur l'onglet **SQL**
3. Copiez le contenu de `database/schema.sql`
4. Collez-le dans la zone de texte
5. Cliquez sur **Exécuter**

#### Via Command Line (MySQL):
```bash
mysql -u root < database/schema.sql
```

#### Via Command Line (avec password):
```bash
mysql -u root -p < database/schema.sql
```

### Étape 2 : Vérifier la connexion

Accédez à:
```
http://localhost/projetweb_avec_evenements/public/index.php/admin
```

Si la page affiche les événements et utilisateurs sans erreur, la base de données est correctement configurée.

## 📋 Configuration de la Base de Données

### Fichier: `app/config/Database.php`

Les paramètres de connexion sont définis comme suit:
```php
private $host = 'localhost';
private $db_name = 'gestion_evenements';
private $username = 'root';
private $password = '';
```

### Pour changer les identifiants:
Modifiez les propriétés privées dans `Database.php`:

```php
private $host = 'votre_serveur';
private $db_name = 'votre_base';
private $username = 'votre_utilisateur';
private $password = 'votre_motdepasse';
```

## 📊 Structure de la Base de Données

### Table: `utilisateur`
```sql
idUtilisateur (PK)
prenom
nom
email (UNIQUE)
role (Entrepreneur, Mentor, Entreprise)
status (Actif, En attente, Suspendu)
date_creation
date_modification
date_derniere_activite
```

### Table: `evenement`
```sql
idEvenement (PK)
titre
description
date
heure
lieu
categorie (tech, emploi, culture, formation)
organisateur
idOrganisateur (FK -> utilisateur)
intervenants
inscrits
max
statut (Ouvert, Complet, Annulé)
programme
documents
replays
date_creation
date_modification
```

### Table: `inscription`
```sql
idInscription (PK)
idUtilisateur (FK -> utilisateur)
idEvenement (FK -> evenement)
dateInscription
statut (Confirmée, Annulée, Présent, Absent)
```

## 🧪 Test des Opérations CRUD

### Ajouter un événement:
1. Accédez à: `http://localhost/projetweb_avec_evenements/public/index.php/admin/events`
2. Cliquez sur **"+ Ajouter"**
3. Remplissez le formulaire
4. Cliquez sur **"Ajouter"**
5. Vérifiez que l'événement apparaît dans la liste

### Modifier un événement:
1. Cliquez sur l'icône modifier (crayon) d'un événement
2. Modifiez les informations
3. Cliquez sur **"Modifier"**

### Supprimer un événement:
1. Cliquez sur l'icône supprimer (poubelle)
2. Confirmez la suppression

## ✔️ Données de Test

La base de données est pré-peuplée avec:
- **10 utilisateurs** (Entrepreneur, Mentor, Entreprise)
- **4 événements** (Tech, Emploi, Culture)
- **12 inscriptions** d'exemple

## 🔧 Dépannage

### Erreur: "Database Connection Error: SQLSTATE[HY000]"
- Vérifiez que MySQL est actif (XAMPP Control Panel)
- Vérifiez les identifiants dans `Database.php`

### Erreur: "Unknown database 'gestion_evenements'"
- Exécutez le fichier `database/schema.sql` (voir Installation)

### Erreur: "Class 'Database' not found"
- Vérifiez que le fichier `app/config/Database.php` existe
- Vérifiez le path dans les require_once

### Les données ne sont pas persistées
- Vérifiez que les INSERT dans `schema.sql` se sont exécutés
- Vérifiez les permissions MySQL

## 📝 Prochaines Étapes

- [ ] Ajouter l'authentification (login/logout)
- [ ] Ajouter la validation des données côté serveur
- [ ] Ajouter les timestamps pour audit trail
- [ ] Ajouter la pagination pour les listes
- [ ] Intégrer un ORM (Doctrine, Eloquent)
- [ ] Export CSV/PDF des événements

## 📚 Architecture MVC

```
app/
├── config/
│   └── Database.php          (Connexion PDO)
├── repositories/
│   ├── EventRepository.php   (Couche DAL pour événements)
│   └── UserRepository.php    (Couche DAL pour utilisateurs)
├── controllers/
│   ├── AdminController.php   (Utilise les repositories)
│   ├── EventsController.php
│   └── Controller.php
├── models/
│   ├── Event.php
│   └── User.php
└── views/
    ├── admin/
    │   ├── events.php
    │   └── index.php
    └── events/
        └── index.php

database/
└── schema.sql                 (Création et données)

public/
├── index.php                  (Front Controller)
└── .htaccess                  (URL Rewriting)
```

## 💡 Flux de Données

```
User Request
    ↓
Front Controller (public/index.php)
    ↓
Router
    ↓
Controller (AdminController)
    ↓
Repository (EventRepository)
    ↓
Database (PDO)
    ↓
MySQL (gestion_evenements)
    ↓
Response View
```

##  Connexion Testée ✅
La base de données `gestion_evenements` est maintenant entièrement intégrée avec l'application.
