# Guide CRUD - Gestion des Événements

## Vue d'ensemble
Un système CRUD complet (Create, Read, Update, Delete) pour la gestion des événements dans l'application Jobyfind.

## Accès à l'Interface Admin

### URL
```
http://localhost/projetweb_avec_evenements/public/index.php/admin/events
```

## Fonctionnalités

### 1. Afficher les événements
- Page `/admin/events` affiche un tableau avec tous les événements
- Informations affichées:
  - Titre
  - Date et heure
  - Lieu
  - Organisateur
  - Nombre inscrits / max
  - Statut (Badge: Ouvert/Complet/Annulé)

### 2. Ajouter un événement
- Cliquez sur le bouton **"+ Ajouter"**
- Remplissez le formulaire pour créer un nouvel événement
- Cliquez sur **"Ajouter"**
- Notification de succès (toast) s'affichera

#### Champs requis (*)
- **Titre**: Nom de l'événement
- **Date**: Format YYYY-MM-DD
- **Heure**: Format HH:MM (24h)
- **Lieu**: Endroit de l'événement
- **Catégorie**: Tech, Emploi, Culture, Formation
- **Max participants**: Nombre maximum d'inscrits

#### Champs optionnels
- Organisateur
- Intervenants
- Programme
- Statut (Ouvert par défaut)
- Documents (URLs)
- Replays (URL vidéo)

### 3. Modifier un événement
- Cliquez sur l'icône **Modifier** (crayon) dans la ligne de l'événement
- La modal s'ouvre avec les données pré-remplies
- Modifiez les informations nécessaires
- Cliquez sur **"Modifier"**
- Notification de succès

### 4. Supprimer un événement
- Cliquez sur l'icône **Supprimer** (poubelle) dans la ligne de l'événement
- Une modal de confirmation apparaît
- Cliquez sur **"Supprimer"** pour confirmer
- L'événement est supprimé de la liste

## Filtres et Recherche

### Filtrer par catégorie
- Utilisez la liste déroulante **"Toutes catégories"**
- Sélectionnez: Tech, Emploi, Culture, Formation
- Le tableau se met à jour automatiquement

### Rechercher par titre
- Tapez dans la barre de recherche en haut
- Le tableau se met à jour en temps réel
- Recherche non sensible à la casse

## Badges de Statut

| Badge | Couleur | Signification |
|-------|---------|---------------|
| Ouvert | Vert | L'événement accepte les inscriptions |
| Complet | Rouge | L'événement est complet (max atteint) |
| Annulé | Gris | L'événement est annulé |

## Format des Données

### Exemple d'événement
```php
[
    'id' => 1234567890,
    'titre' => 'Tunisia Tech Summit 2026',
    'date' => '2026-05-08',
    'heure' => '09:00',
    'lieu' => 'Tunis',
    'categorie' => 'tech',
    'organisateur' => 'Mohamed Ben Ali',
    'intervenants' => 'Ahmed Trabelsi, Leila Khazri',
    'max' => 500,
    'inscrits' => 380,
    'statut' => 'Ouvert',
    'programme' => 'Accueil, Conférences IA, Pause, Workshops',
    'documents' => '',
    'replays' => ''
]
```

## Cas d'usage courants

### Créer un événement tech
1. Cliquez sur **"+ Ajouter"**
2. Titre: "Conférence AI 2026"
3. Date: 2026-06-15
4. Heure: 10:00
5. Lieu: Salle Convention, Tunis
6. Catégorie: **Tech**
7. Max: 200
8. Organisateur: Tech Tunisia
9. Intervenants: Experts en IA
10. Programme: Keynotes, Workshops, Networking
11. Cliquez **"Ajouter"**

### Marquer un événement comme complet
1. Cliquez sur l'icône modifier
2. Changez le statut à **"Complet"**
3. Cliquez **"Modifier"**

### Annuler un événement
1. Cliquez sur l'icône modifier
2. Changez le statut à **"Annulé"**
3. Cliquez **"Modifier"**

## Limitation actuelle

⚠️ **Important**: Les données ne sont **pas persistées** actuellement. 
- À chaque rechargement du serveur, les données reviennent à l'état initial
- Les modifications ne sont stockées que en mémoire pendant la session actuelle
- Pour persister les données, une intégration avec une base de données (MySQL/PostgreSQL) est nécessaire

## Structure du code

```
app/
├── controllers/
│   ├── AdminController.php      (CRUD methods pour les événements)
│   ├── EventsController.php     (Affichage public + registration)
│   └── Controller.php           (Classe de base)
├── models/
│   ├── Event.php                (Getters/Setters pour Event)
│   └── User.php                 (Getters/Setters pour User)
└── views/
    └── admin/
        └── events.php           (Interface CRUD pour événements)

public/
├── index.php                     (Front Controller)
├── .htaccess                     (URL Rewriting)
└── assets/                       (CSS, JS, Images)
```

## Prochaines étapes

Pour une implémentation complète:
1. [ ] Ajouter une base de données (MySQL)
2. [ ] Intégrer un ORM ou un adaptateur DB
3. [ ] Ajouter la validation des données côté serveur
4. [ ] Ajouter l'authentification/autorisation
5. [ ] Ajouter les timestamps created_at/updated_at
6. [ ] Ajouter la pagination pour les listes
7. [ ] Export des événements (CSV, PDF)
8. [ ] Intégration calendrier
