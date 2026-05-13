# 🚀 Démarrage Rapide - Blog + Événements Intégrés

## ⚡ En 3 Étapes

### 1️⃣ Accédez à votre plateforme
```
http://localhost/projetweb/public/
```

### 2️⃣ Naviguez via le menu
- **Blog** → `/blog`
- **Événements** → `/events`
- **Accueil** → `/` (combiné)

### 3️⃣ Explorez les fonctionnalités

---

## 🎯 Liens Essentiels

### Blog
| Lien | Description |
|------|------------|
| `/blog` | 📰 Tous les articles |
| `/blog/create` | ✍️ Créer un article |
| `/blog?category=Technologie` | 🏷️ Articles par catégorie |
| `/blog?search=php` | 🔍 Rechercher |
| `/blog/1` | 📖 Lire un article |

### Événements
| Lien | Description |
|------|------------|
| `/events` | 📅 Tous les événements |
| `/events/1` | 📌 Détail d'un événement |
| `/admin/events` | ⚙️ Gestion admin |

### Autres
| Lien | Description |
|------|------------|
| `/` | 🏠 Accueil (Blog + Événements) |

---

## 📚 Catégories Disponibles

- 📱 **Technologie** - Articles tech
- 🎓 **Formation** - Guides & tutoriels
- 💼 **Emploi** - Conseils & actualités
- 📅 **Événements** - Couverture d'événements
- 📝 **Général** - Articles divers

---

## 🎨 Design

### Thème Couleurs
- **Primaire** : Violet/Bleu (#667eea)
- **Secondaire** : Mauve (#764ba2)
- **Fond** : Gris clair (#f7f9fc)

### Responsive
- ✅ Mobile (< 576px)
- ✅ Tablet (576px - 992px)
- ✅ Desktop (> 992px)

---

## 🔥 Top Fonctionnalités

### Blog
✨ **Commentaires** - Laissez vos avis
⭐ **Évaluations** - Notez les articles (1-5 stars)
❤️ **J'aime** - Marquez vos favoris
👁️ **Vues** - Suivi des lectures
🏷️ **Catégories** - Filtrez par thème
🔍 **Recherche** - Trouvez rapidement

### Événements
📅 **Inscription** - S'inscrire en un clic
📧 **Notifications** - Confirmation email
📌 **Détails** - Programme & lieu
👥 **Participants** - Voir qui s'inscrit
🎫 **QR Code** - Pour l'accès

---

## 📋 Contenu Pré-chargé

### Articles de Démonstration (3)
1. **Introduction à React.js** (156 vues)
2. **Les Meilleures Pratiques en PHP** (89 vues)
3. **Débuter avec Node.js** (210 vues)

### Événements de Démonstration (4)
1. **Tunisia Tech Summit 2026** (380 inscrits)
2. **Hackathon Innovation Sociale** (120 inscrits)
3. **Journée Portes Ouvertes Emploi** (610 inscrits)
4. **Festival de Cinéma Arabe** (200 inscrits - COMPLET)

---

## 💡 Astuces

### Créer Rapidement
```
/blog/create → Remplir le formulaire → Publier
```

### Rechercher
```
/blog?search=php
/blog?category=Formation
/blog?search=node&category=Formation
```

### Pagination
```
/blog?page=1
/blog?page=2
/blog?page=3
```

### Articles par Catégorie
```
/blog?category=Technologie
/blog?category=Formation
/blog?category=Emploi
```

---

## ⌨️ Raccourcis Clavier

| Touche | Action |
|--------|--------|
| `Entrée` | Publier commentaire |
| `Ctrl+Entrée` | Publier article (dans l'éditeur) |
| `Esc` | Fermer modal |

---

## 🔔 Notifications

- ✉️ Email de confirmation pour inscriptions événements
- 💬 Notification de nouveau commentaire (modération)
- ⭐ Alerte de nouvelles évaluations

---

## 🆘 Support Rapide

### Ça ne fonctionne pas?

**Blog ne charge pas**
```bash
→ Vérifier: http://localhost/blog
→ Vérifier la console du navigateur (F12)
→ Vérifier les logs PHP
```

**Images ne s'affichent pas**
```bash
→ Vérifier dossier: public/assets/images/blog/
→ Vérifier permissions: chmod 755
```

**Commentaires ne s'affichent pas**
```bash
→ Vérifier table comments en base de données
→ Vérifier statut commentaire = "approved"
```

---

## 📞 Questions?

Consultez:
- 📖 [BLOG_INTEGRATION.md](./BLOG_INTEGRATION.md) - Documentation complète
- 💻 [Controllers](./controllers/) - Code source
- 🗄️ [Database Schema](./database/) - Structure BD

---

## 🎉 Vous Êtes Prêt!

**Commencez par:**
1. Visitez `/` pour la page d'accueil
2. Cliquez sur "Blog" ou "Événements"
3. Explorez et profitez!

**Bon amusement!** 🚀
