# Intégration du Système de Blog

## 📋 Vue d'ensemble

Le système de blog a été intégré avec succès dans le projet de gestion des événements. Voici ce qui a été ajouté:

## ✅ Ce qui a été fait

### 1. **Controllers Blog**
- `controllers/BlogController.php` - Gestion des posts (CRUD, recherche, pagination, analyses)
- `controllers/CategoryController.php` - Gestion des catégories
- `controllers/CommentController.php` - Gestion des commentaires
- `controllers/StoryController.php` - Gestion des stories

### 2. **Models Blog**
- `models/PostModel.php` - Modèle Post
- `models/CategoryModel.php` - Modèle Catégorie
- `models/CommentModel.php` - Modèle Commentaire
- `models/StoryModel.php` - Modèle Story

### 3. **Tables de Base de Données**
Les tables suivantes ont été créées dans `gestion_evenements`:
- `categories` - Catégories de blog
- `posts` - Articles/posts
- `comments` - Commentaires sur les posts
- `likes` - Likes/j'aimes
- `stories` - Stories (publication temporaire)

### 4. **Fichiers AJAX**
- `public/ajax_load_posts.php` - Chargement des posts (pagination)
- `public/ajax_add_comment.php` - Ajout de commentaires
- `public/ajax_toggle_like.php` - Basculer les likes
- `public/ajax_rate_post.php` - Notation des posts
- `public/ajax_track_view.php` - Suivi des vues
- `public/ajax_track_story_view.php` - Suivi des vues des stories
- `public/ajax_generate_blog.php` - Génération de contenu blog

### 5. **Fichiers d'Export**
- `public/export_post_pdf.php` - Export d'un post en PDF
- `public/export_stats_excel.php` - Export des statistiques en Excel
- `public/export_stats_pdf.php` - Export des statistiques en PDF

### 6. **Vues**
Les vues du blog ont été copiées dans `views/blog/`:
- Frontoffice et backoffice
- Gestion des posts, catégories, commentaires
- Pages de connexion/inscription

### 7. **Routes**
Les routes suivantes ont été ajoutées au Router:
- `/blog` - Accueil du blog
- `/posts` - Liste des posts
- `/categories` - Gestion des catégories
- `/comments` - Gestion des commentaires
- `/stories` - Gestion des stories

## 🚀 Utilisation

### Accéder au blog
```
http://localhost/projetweb_avec_evenements_fix/public/blog-index.php
```

### Initialiser la base de données
```bash
php init-blog.php
```

## 📁 Structure des fichiers

```
projetweb_avec_evenements_fix/
├── controllers/
│   ├── BlogController.php
│   ├── CategoryController.php
│   ├── CommentController.php
│   └── StoryController.php
├── models/
│   ├── PostModel.php
│   ├── CategoryModel.php
│   ├── CommentModel.php
│   └── StoryModel.php
├── public/
│   ├── blog-index.php (nouvelle page d'accueil du blog)
│   ├── ajax_*.php
│   ├── export_*.php
│   └── uploads/ (images du blog)
├── views/
│   └── blog/ (nouvelles vues du blog)
├── database/
│   └── blog-schema.sql (schéma SQL)
└── init-blog.php (script d'initialisation)
```

## 🔧 Configuration

### Database
La classe `Database` (config/Database.php) a été modifiée pour supporter à la fois:
- Gestion des événements
- Gestion du blog

Tous les controllers du blog utilisent maintenant `Database::getInstance()->getConnection()` pour accéder à la base de données.

### Authentification
L'authentification du blog utilise le même système que le reste du projet.

## 📝 Fonctionnalités

### Posts
- ✅ Créer, lire, mettre à jour, supprimer (CRUD)
- ✅ Pagination
- ✅ Recherche
- ✅ Filtrage par catégorie
- ✅ Upload d'images (cover image)
- ✅ Suivi des vues
- ✅ Export en PDF

### Commentaires
- ✅ Ajouter des commentaires
- ✅ Afficher les commentaires
- ✅ Modération

### Catégories
- ✅ Créer/Modifier/Supprimer des catégories
- ✅ Lister les catégories

### Stories
- ✅ Créer/Modifier/Supprimer des stories
- ✅ Stories temporelles (dates de début/fin)
- ✅ Suivi des vues

### Likes
- ✅ Basculer les likes
- ✅ Comptage des likes
- ✅ Limitation par IP

## 🔒 Sécurité

- ✅ Utilisation de requêtes préparées (PDO)
- ✅ Validation et sanitization des inputs
- ✅ Protection contre les injections SQL
- ✅ Gestion des erreurs

## 📚 Documentation supplémentaire

- Consultez les fichiers CRUD_GUIDE.md et SEARCH_FEATURE.md pour plus de détails
- Les fichiers de configuration se trouvent dans `config/`

## ⚠️ Notes importantes

1. Les répertoires `uploads/` et `uploads/stories/` doivent avoir les permissions en écriture (777)
2. L'intégration utilise la base de données `gestion_evenements` existante
3. Tous les fichiers du blog ont été adaptés pour utiliser la classe Database du projet

## 🐛 Dépannage

Si vous rencontrez des problèmes:

1. **Erreur de connexion DB**: Vérifiez que MySQL est démarré et que les identifiants dans `config/Database.php` sont corrects

2. **Erreur 404 sur les fichiers AJAX**: Vérifiez que les chemins d'inclusion sont correctement mis à jour

3. **Erreur d'upload d'images**: Vérifiez que le répertoire `public/uploads/` existe et a les bonnes permissions

4. **Tables introuvables**: Exécutez `php init-blog.php` pour créer les tables

## ✨ Prochaines étapes

- Adapter les vues du blog au design global du projet
- Intégrer un éditeur riche (TinyMCE, Quill, etc.)
- Ajouter un système de tags
- Implémenter un cache pour les performances
- Ajouter des webhooks pour les notifications
