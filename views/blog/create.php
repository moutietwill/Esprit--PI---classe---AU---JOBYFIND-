<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Créer un article - Blog</title>
    <link href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.4.0/css/all.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/css/bootstrap.min.css" rel="stylesheet">
    <link href="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.css" rel="stylesheet">
    <style>
        :root {
            --primary: #667eea;
            --secondary: #764ba2;
        }

        body {
            background: linear-gradient(135deg, #f7f9fc 0%, #fff 100%);
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
        }

        .page-header {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            padding: 2rem 0;
            margin-bottom: 2rem;
        }

        .form-container {
            background: white;
            border-radius: 12px;
            padding: 2rem;
            box-shadow: 0 4px 15px rgba(0,0,0,0.08);
        }

        .form-group {
            margin-bottom: 1.5rem;
        }

        .form-group label {
            font-weight: 600;
            color: #333;
            margin-bottom: 0.5rem;
            display: block;
        }

        .form-group input,
        .form-group select,
        .form-group textarea {
            width: 100%;
            padding: 0.8rem;
            border: 2px solid #eee;
            border-radius: 8px;
            font-size: 0.95rem;
            font-family: inherit;
            transition: all 0.3s ease;
        }

        .form-group input:focus,
        .form-group select:focus,
        .form-group textarea:focus {
            outline: none;
            border-color: var(--primary);
            box-shadow: 0 0 0 3px rgba(102, 126, 234, 0.1);
        }

        .form-group textarea {
            resize: vertical;
            min-height: 100px;
        }

        .image-preview {
            max-width: 100%;
            max-height: 300px;
            border-radius: 8px;
            margin-top: 1rem;
            display: none;
        }

        .image-upload {
            position: relative;
            cursor: pointer;
        }

        .file-input {
            display: none;
        }

        .upload-label {
            display: inline-block;
            padding: 1rem 2rem;
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
            border-radius: 8px;
            cursor: pointer;
            font-weight: 600;
            transition: all 0.3s ease;
        }

        .upload-label:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
        }

        .form-buttons {
            display: flex;
            gap: 1rem;
            justify-content: flex-end;
            margin-top: 2rem;
            padding-top: 2rem;
            border-top: 1px solid #eee;
        }

        .btn {
            padding: 0.8rem 1.5rem;
            border-radius: 8px;
            font-weight: 600;
            border: none;
            cursor: pointer;
            transition: all 0.3s ease;
            text-decoration: none;
            display: inline-block;
        }

        .btn-primary {
            background: linear-gradient(135deg, var(--primary) 0%, var(--secondary) 100%);
            color: white;
        }

        .btn-primary:hover {
            transform: translateY(-3px);
            box-shadow: 0 6px 20px rgba(102, 126, 234, 0.4);
            color: white;
        }

        .btn-secondary {
            background: white;
            border: 2px solid #eee;
            color: #666;
        }

        .btn-secondary:hover {
            border-color: var(--primary);
            color: var(--primary);
        }

        .form-hint {
            font-size: 0.85rem;
            color: #999;
            margin-top: 0.3rem;
        }

        .alert {
            border-radius: 8px;
            border: none;
            margin-bottom: 1.5rem;
        }

        .summernote {
            border: 2px solid #eee !important;
            border-radius: 8px !important;
        }

        .note-editable {
            min-height: 300px;
        }

        .form-section {
            margin-bottom: 2rem;
            padding-bottom: 2rem;
            border-bottom: 1px solid #eee;
        }

        .form-section:last-of-type {
            border-bottom: none;
        }

        .section-title {
            font-size: 1.1rem;
            font-weight: 700;
            color: #333;
            margin-bottom: 1rem;
        }

        @media (max-width: 768px) {
            .form-buttons {
                flex-direction: column;
            }

            .btn {
                width: 100%;
                text-align: center;
            }
        }
    </style>
</head>
<body>
    <!-- Header -->
    <div class="page-header">
        <div class="container">
            <h1><i class="fas fa-pen-fancy"></i> Créer un nouvel article</h1>
            <p class="mb-0">Partagez vos connaissances avec notre communauté</p>
        </div>
    </div>

    <!-- Form Container -->
    <div class="container my-5">
        <div class="row justify-content-center">
            <div class="col-lg-8">
                <?php if (isset($_GET['error'])): ?>
                    <div class="alert alert-danger" role="alert">
                        <i class="fas fa-exclamation-circle"></i>
                        <?php
                        $errors = [
                            'title_invalid' => 'Le titre est invalide ou trop court',
                            'content_invalid' => 'Le contenu est invalide ou trop court',
                            'save_failed' => 'Erreur lors de la sauvegarde',
                            'exception' => 'Une erreur s\'est produite'
                        ];
                        echo $errors[$_GET['error']] ?? 'Une erreur s\'est produite';
                        ?>
                    </div>
                <?php endif; ?>

                <form method="POST" action="<?php echo htmlspecialchars($url('/blog/store'), ENT_QUOTES, 'UTF-8'); ?>" enctype="multipart/form-data" class="form-container">
                    <!-- Section Titre et Catégorie -->
                    <div class="form-section">
                        <div class="section-title"><i class="fas fa-info-circle"></i> Informations générales</div>

                        <div class="form-group">
                            <label for="title">Titre de l'article *</label>
                            <input type="text" id="title" name="title" required 
                                   placeholder="Entrez le titre de votre article"
                                   minlength="5" maxlength="255">
                            <div class="form-hint">Au minimum 5 caractères</div>
                        </div>

                        <div class="form-group">
                            <label for="category_id">Catégorie *</label>
                            <select id="category_id" name="category_id" required>
                                <option value="">-- Sélectionner une catégorie --</option>
                                <?php if (isset($categories) && !empty($categories)): ?>
                                    <?php foreach ($categories as $category): ?>
                                        <option value="<?php echo htmlspecialchars($category['id']); ?>">
                                            <?php echo htmlspecialchars($category['name']); ?>
                                        </option>
                                    <?php endforeach; ?>
                                <?php endif; ?>
                            </select>
                        </div>

                        <div class="form-group">
                            <label for="excerpt">Résumé court (optionnel)</label>
                            <textarea id="excerpt" name="excerpt" 
                                      placeholder="Un court résumé de votre article (150 caractères max)"
                                      maxlength="150"></textarea>
                            <div class="form-hint">Optionnel: sera généré automatiquement si vide</div>
                        </div>
                    </div>

                    <!-- Section Image -->
                    <div class="form-section">
                        <div class="section-title"><i class="fas fa-image"></i> Image de couverture</div>

                        <div class="form-group">
                            <label for="cover_image">Image de couverture (optionnel)</label>
                            <div class="image-upload">
                                <input type="file" id="cover_image" name="cover_image" class="file-input" 
                                       accept="image/*" onchange="previewImage(event)">
                                <label for="cover_image" class="upload-label">
                                    <i class="fas fa-cloud-upload-alt"></i> Choisir une image
                                </label>
                            </div>
                            <img id="imagePreview" class="image-preview" alt="Aperçu">
                            <div class="form-hint">Formats acceptés: JPG, PNG, GIF (Max 5MB)</div>
                        </div>
                    </div>

                    <!-- Section Contenu -->
                    <div class="form-section">
                        <div class="section-title"><i class="fas fa-file-alt"></i> Contenu de l'article</div>

                        <div class="form-group">
                            <label for="content">Contenu *</label>
                            <textarea id="content" name="content" class="summernote" required
                                      placeholder="Écrivez votre article ici..."></textarea>
                            <div class="form-hint">Au minimum 20 caractères</div>
                        </div>
                    </div>

                    <!-- Boutons d'action -->
                    <div class="form-buttons">
                        <a href="<?php echo htmlspecialchars($url('/blog'), ENT_QUOTES, 'UTF-8'); ?>" class="btn btn-secondary">
                            <i class="fas fa-times"></i> Annuler
                        </a>
                        <button type="submit" class="btn btn-primary">
                            <i class="fas fa-check"></i> Publier l'article
                        </button>
                    </div>
                </form>
            </div>
        </div>
    </div>

    <!-- Scripts -->
    <script src="https://code.jquery.com/jquery-3.6.0.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/popper.js@2.11.6/dist/umd/popper.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/bootstrap@5.3.0/dist/js/bootstrap.bundle.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/dist/summernote-lite.min.js"></script>
    <script src="https://cdn.jsdelivr.net/npm/summernote@0.8.20/lang/summernote-fr-FR.js"></script>

    <script>
        // Initialiser l'éditeur Summernote
        $(document).ready(function() {
            $('#content').summernote({
                lang: 'fr-FR',
                height: 300,
                toolbar: [
                    ['style', ['style']],
                    ['font', ['bold', 'underline', 'clear']],
                    ['color', ['color']],
                    ['para', ['ul', 'ol', 'paragraph']],
                    ['table', ['table']],
                    ['insert', ['link', 'picture', 'video']],
                    ['view', ['fullscreen', 'codeview', 'help']]
                ]
            });
        });

        // Aperçu d'image
        function previewImage(event) {
            const input = event.target;
            const preview = document.getElementById('imagePreview');
            
            if (input.files && input.files[0]) {
                const reader = new FileReader();
                reader.onload = function(e) {
                    preview.src = e.target.result;
                    preview.style.display = 'block';
                };
                reader.readAsDataURL(input.files[0]);
            }
        }

        // Validation du formulaire
        document.querySelector('form').addEventListener('submit', function(e) {
            const title = document.getElementById('title').value.trim();
            const content = document.getElementById('content').value.trim();

            if (title.length < 5) {
                e.preventDefault();
                alert('Le titre doit avoir au moins 5 caractères');
                return;
            }

            if (content.length < 20) {
                e.preventDefault();
                alert('Le contenu doit avoir au moins 20 caractères');
                return;
            }
        });
    </script>
</body>
</html>
