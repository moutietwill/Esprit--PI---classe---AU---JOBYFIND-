<?php
require_once __DIR__ . '/../models/Candidature.php';
require_once __DIR__ . '/../models/Offre.php';
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../lib/MailManager.php';

class CandidatureController {
    private $errors = [];
    private $formData = [];

    // Validation methods
    private function validateEmail($email) {
        return filter_var($email, FILTER_VALIDATE_EMAIL) !== false;
    }

    private function validateTelephone($tel) {
        $tel = preg_replace('/\s+/', '', $tel);
        return strlen($tel) >= 8 && preg_match('/^[0-9+()-]+$/', $tel);
    }

    private function validateCandidatureData($data, $isEdit = false) {
        $this->errors = [];
        $this->formData = $data;

        // Validate Nom
        if (empty($data['nom_candidat'])) {
            $this->errors['nom_candidat'] = 'Le nom est obligatoire.';
        } elseif (strlen(trim($data['nom_candidat'])) < 2) {
            $this->errors['nom_candidat'] = 'Le nom doit contenir au minimum 2 caractères.';
        } elseif (strlen($data['nom_candidat']) > 255) {
            $this->errors['nom_candidat'] = 'Le nom ne peut pas dépasser 255 caractères.';
        }

        // Validate Prenom
        if (empty($data['prenom_candidat'])) {
            $this->errors['prenom_candidat'] = 'Le prénom est obligatoire.';
        } elseif (strlen(trim($data['prenom_candidat'])) < 2) {
            $this->errors['prenom_candidat'] = 'Le prénom doit contenir au minimum 2 caractères.';
        } elseif (strlen($data['prenom_candidat']) > 255) {
            $this->errors['prenom_candidat'] = 'Le prénom ne peut pas dépasser 255 caractères.';
        }

        // Validate Email
        if (empty($data['email_candidat'])) {
            $this->errors['email_candidat'] = 'L\'email est obligatoire.';
        } elseif (!$this->validateEmail($data['email_candidat'])) {
            $this->errors['email_candidat'] = 'L\'email saisi n\'est pas valide.';
        } elseif (strlen($data['email_candidat']) > 255) {
            $this->errors['email_candidat'] = 'L\'email ne peut pas dépasser 255 caractères.';
        }

        // Validate Telephone
        if (empty($data['telephone'])) {
            $this->errors['telephone'] = 'Le téléphone est obligatoire.';
        } elseif (!$this->validateTelephone($data['telephone'])) {
            $this->errors['telephone'] = 'Le téléphone est invalide. Minimum 8 chiffres.';
        } elseif (strlen($data['telephone']) > 20) {
            $this->errors['telephone'] = 'Le téléphone ne peut pas dépasser 20 caractères.';
        }

        // Validate Lettre Motivation
        if (empty($data['lettre_motivation'])) {
            $this->errors['lettre_motivation'] = 'La lettre de motivation est obligatoire.';
        } elseif (strlen(trim($data['lettre_motivation'])) < 20) {
            $this->errors['lettre_motivation'] = 'La lettre de motivation doit contenir au minimum 20 caractères.';
        } elseif (strlen($data['lettre_motivation']) > 5000) {
            $this->errors['lettre_motivation'] = 'La lettre de motivation ne peut pas dépasser 5000 caractères.';
        }

        // Validate Statut (if in edit mode)
        if ($isEdit && isset($data['statut'])) {
            $statuts_valides = ['En attente', 'Acceptée', 'Rejetée'];
            if (!in_array($data['statut'], $statuts_valides)) {
                $this->errors['statut'] = 'Le statut sélectionné est invalide.';
            }
        }

        return empty($this->errors);
    }

    private function validateCVFile($file) {
        if ($file['error'] == UPLOAD_ERR_NO_FILE) {
            return null;
        }

        if ($file['error'] != UPLOAD_ERR_OK) {
            $errors = [
                UPLOAD_ERR_INI_SIZE => 'Le fichier dépasse la taille maximale du serveur.',
                UPLOAD_ERR_FORM_SIZE => 'Le fichier dépasse la taille maximale du formulaire.',
                UPLOAD_ERR_PARTIAL => 'Le fichier n\'a pas été entièrement téléchargé.',
                UPLOAD_ERR_NO_FILE => 'Aucun fichier n\'a été sélectionné.',
                UPLOAD_ERR_NO_TMP_DIR => 'Dossier temporaire manquant.',
                UPLOAD_ERR_CANT_WRITE => 'Impossible d\'écrire le fichier.',
                UPLOAD_ERR_EXTENSION => 'Extension interdite par le serveur.'
            ];
            $this->errors['cv_fichier'] = $errors[$file['error']] ?? 'Erreur lors du téléchargement.';
            return false;
        }

        $mime_type = mime_content_type($file['tmp_name']);
        $allowed_types = ['application/pdf', 'application/msword', 
                         'application/vnd.openxmlformats-officedocument.wordprocessingml.document'];
        
        if (!in_array($mime_type, $allowed_types)) {
            $this->errors['cv_fichier'] = 'Le fichier doit être un PDF ou un document Word (DOC, DOCX).';
            return false;
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            $this->errors['cv_fichier'] = 'Le fichier ne peut pas dépasser 5 MB.';
            return false;
        }

        return true;
    }

    public function index() {
        $db = Database::getInstance();

        // Tri dynamique sécurisé
        $allowed_sort = ['nom_candidat', 'email_candidat', 'titre_offre', 'date_candidature', 'statut'];
        $sort  = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort))
                 ? $_GET['sort'] : 'date_candidature';
        $order = (isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC') ? 'ASC' : 'DESC';

        // Mappage colonne alias → colonne réelle
        $sort_col = ($sort === 'titre_offre') ? 'o.titre' : "c.`$sort`";

        // Pagination
        $per_page    = 10;
        $total_count = $db->query("SELECT COUNT(*) FROM candidature")->fetchColumn();
        $total_pages = max(1, (int)ceil($total_count / $per_page));
        $page        = (isset($_GET['page']) && is_numeric($_GET['page']))
                       ? max(1, min((int)$_GET['page'], $total_pages)) : 1;
        $offset      = ($page - 1) * $per_page;

        $stmt = $db->query("SELECT c.*, o.titre as titre_offre FROM candidature c JOIN offre o ON c.id_offre = o.id_offre ORDER BY $sort_col $order LIMIT $per_page OFFSET $offset");
        $candidatures = $stmt->fetchAll();

        $current_sort  = $sort;
        $current_order = $order;

        require_once __DIR__ . '/../views/back/candidatures/list.php';
    }

    public function indexByOffre() {
        if (!isset($_GET['id_offre']) || empty($_GET['id_offre'])) {
            header('Location: index.php?action=list_offres');
            exit();
        }
        
        $id_offre = intval($_GET['id_offre']);
        $db = Database::getInstance();
        $stmtO = $db->prepare("SELECT * FROM offre WHERE id_offre = :id");
        $stmtO->execute(['id' => $id_offre]);
        $offre = $stmtO->fetch();
        
        if (!$offre) {
            header('Location: index.php?action=list_offres');
            exit();
        }

        // Tri dynamique sécurisé
        $allowed_sort = ['id_candidature', 'nom_candidat', 'email_candidat', 'telephone', 'date_candidature', 'statut'];
        $sort  = (isset($_GET['sort']) && in_array($_GET['sort'], $allowed_sort))
                 ? $_GET['sort'] : 'date_candidature';
        $order = (isset($_GET['order']) && strtoupper($_GET['order']) === 'ASC') ? 'ASC' : 'DESC';

        // Pagination
        $per_page    = 10;
        $total_count = $db->prepare("SELECT COUNT(*) FROM candidature WHERE id_offre = :id_offre");
        $total_count->execute(['id_offre' => $id_offre]);
        $total_count = (int)$total_count->fetchColumn();
        $total_pages = max(1, (int)ceil($total_count / $per_page));
        $page        = (isset($_GET['page']) && is_numeric($_GET['page']))
                       ? max(1, min((int)$_GET['page'], $total_pages)) : 1;
        $offset      = ($page - 1) * $per_page;
        
        $stmtC = $db->prepare("SELECT c.*, o.titre as titre_offre FROM candidature c JOIN offre o ON c.id_offre = o.id_offre WHERE c.id_offre = :id_offre ORDER BY c.`$sort` $order LIMIT $per_page OFFSET $offset");
        $stmtC->execute(['id_offre' => $id_offre]);
        $candidatures = $stmtC->fetchAll();

        $current_sort  = $sort;
        $current_order = $order;
        
        require_once __DIR__ . '/../views/back/candidatures/list_by_offre.php';
    }

    public function create() {
        $id_offre = '';
        $offre = null;
        $db = Database::getInstance();
        
        if (isset($_GET['id_offre']) && !empty($_GET['id_offre'])) {
            $id_offre = intval($_GET['id_offre']);
            $stmt = $db->prepare("SELECT * FROM offre WHERE id_offre = :id");
            $stmt->execute(['id' => $id_offre]);
            $offre = $stmt->fetch();
        }
        
        $stmtAll = $db->query("SELECT * FROM offre ORDER BY datePublication DESC");
        $offres = $stmtAll->fetchAll();

        $candidatureData = null;
        $errors = [];

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $post_data = [
                'nom_candidat' => isset($_POST['nom_candidat']) ? trim($_POST['nom_candidat']) : '',
                'prenom_candidat' => isset($_POST['prenom_candidat']) ? trim($_POST['prenom_candidat']) : '',
                'email_candidat' => isset($_POST['email_candidat']) ? trim($_POST['email_candidat']) : '',
                'telephone' => isset($_POST['telephone']) ? trim($_POST['telephone']) : '',
                'lettre_motivation' => isset($_POST['lettre_motivation']) ? trim($_POST['lettre_motivation']) : ''
            ];

            // Sanitize inputs
            $post_data['nom_candidat'] = htmlspecialchars($post_data['nom_candidat'], ENT_QUOTES, 'UTF-8');
            $post_data['prenom_candidat'] = htmlspecialchars($post_data['prenom_candidat'], ENT_QUOTES, 'UTF-8');
            $post_data['email_candidat'] = filter_var($post_data['email_candidat'], FILTER_SANITIZE_EMAIL);
            $post_data['telephone'] = preg_replace('/[^0-9+() -]/', '', $post_data['telephone']);
            $post_data['lettre_motivation'] = htmlspecialchars($post_data['lettre_motivation'], ENT_QUOTES, 'UTF-8');

            // Validate Offre presence
            if (empty($_POST['id_offre'])) {
                $this->errors['id_offre'] = "L'offre est obligatoire.";
            }

            if ($this->validateCandidatureData($post_data) && empty($this->errors)) {
                $candidature = new Candidature();
                $candidature->setIdOffre(intval($_POST['id_offre']));
                $candidature->setNomCandidat($post_data['nom_candidat']);
                $candidature->setPrenomCandidat($post_data['prenom_candidat']);
                $candidature->setEmailCandidat($post_data['email_candidat']);
                $candidature->setTelephone($post_data['telephone']);
                $candidature->setLettreMotivation($post_data['lettre_motivation']);
                $candidature->setDateCandidature(date('Y-m-d H:i:s'));
                $candidature->setStatut('En attente');

                // Handle file upload
                $cv_file = null;
                if (isset($_FILES['cv_fichier'])) {
                    $cv_validation = $this->validateCVFile($_FILES['cv_fichier']);
                    
                    if ($cv_validation === false) {
                        $errors = $this->errors;
                    } elseif ($cv_validation === true) {
                        $target_dir = "uploads/cv/";
                        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                        
                        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['cv_fichier']['name']));
                        $target_file = $target_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['cv_fichier']['tmp_name'], $target_file)) {
                            $candidature->setCvFichier($file_name);
                        } else {
                            $errors['cv_fichier'] = 'Impossible de sauvegarder le fichier.';
                        }
                    }
                } else {
                    $errors['cv_fichier'] = 'Le fichier CV est obligatoire.';
                }

                if (empty($errors)) {
                    $stmtIns = $db->prepare("INSERT INTO candidature (id_offre, nom_candidat, prenom_candidat, email_candidat, telephone, cv_fichier, lettre_motivation, date_candidature, statut) 
                                             VALUES (:id_offre, :nom_candidat, :prenom_candidat, :email_candidat, :telephone, :cv_fichier, :lettre_motivation, :date_candidature, :statut)");
                    $res = $stmtIns->execute([
                        'id_offre' => $candidature->getIdOffre(),
                        'nom_candidat' => $candidature->getNomCandidat(),
                        'prenom_candidat' => $candidature->getPrenomCandidat(),
                        'email_candidat' => $candidature->getEmailCandidat(),
                        'telephone' => $candidature->getTelephone(),
                        'cv_fichier' => $candidature->getCvFichier(),
                        'lettre_motivation' => $candidature->getLettreMotivation(),
                        'date_candidature' => $candidature->getDateCandidature(),
                        'statut' => $candidature->getStatut()
                    ]);
                    
                    if ($res) {
                        // ── Envoi Email de Confirmation ──
                        $mailResult = ['success' => false, 'error' => 'Mail non envoyé'];
                        try {
                            // Récupérer le titre de l'offre
                            $stmtOffre = $db->prepare("SELECT titre FROM offre WHERE id_offre = :id");
                            $stmtOffre->execute(['id' => intval($_POST['id_offre'])]);
                            $offreData = $stmtOffre->fetch();
                            $titreOffre = $offreData ? $offreData['titre'] : 'Offre';

                            // Envoyer l'email
                            $mailResult = MailManager::sendConfirmation(
                                $post_data['email_candidat'],
                                $post_data['prenom_candidat'],
                                $post_data['nom_candidat'],
                                $titreOffre
                            );
                        } catch (Exception $e) {
                            $mailResult = ['success' => false, 'error' => $e->getMessage()];
                        }

                        // Redirection selon la source
                        if (isset($_POST['is_front']) && $_POST['is_front'] == '1') {
                            session_start();
                            $_SESSION['mail_details'] = [
                                'email'  => $post_data['email_candidat'],
                                'prenom' => $post_data['prenom_candidat'],
                                'nom'    => $post_data['nom_candidat']
                            ];
                            $mailStatus = $mailResult['success'] ? 'sent' : 'failed';
                            header('Location: index.php?action=candidature_success&id_offre=' . intval($_POST['id_offre']) . '&mail=' . $mailStatus);
                            exit();
                        } else {
                            // Back-office → liste candidatures
                            header('Location: index.php?action=list_candidatures_offre&id_offre=' . intval($_POST['id_offre']));
                            exit();
                        }
                    } else {
                        $errors['general'] = 'Erreur lors de l\'enregistrement de la candidature.';
                    }
                }
            } else {
                $errors = $this->errors;
                $candidatureData = $post_data;
            }
        }

        require_once __DIR__ . '/../views/back/candidatures/form.php';
    }

    public function edit() {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header('Location: index.php?action=list_candidatures');
            exit();
        }
        
        $id = intval($_GET['id']);
        $db = Database::getInstance();
        $stmtC = $db->prepare("SELECT * FROM candidature WHERE id_candidature = :id");
        $stmtC->execute(['id' => $id]);
        $candidatureData = $stmtC->fetch();
        
        if (!$candidatureData) {
            header('Location: index.php?action=list_candidatures');
            exit();
        }

        $errors = [];
        $id_offre = $candidatureData['id_offre'];
        
        $stmtO = $db->prepare("SELECT * FROM offre WHERE id_offre = :id");
        $stmtO->execute(['id' => $id_offre]);
        $offre = $stmtO->fetch();

        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $post_data = [
                'nom_candidat' => isset($_POST['nom_candidat']) ? trim($_POST['nom_candidat']) : '',
                'prenom_candidat' => isset($_POST['prenom_candidat']) ? trim($_POST['prenom_candidat']) : '',
                'email_candidat' => isset($_POST['email_candidat']) ? trim($_POST['email_candidat']) : '',
                'telephone' => isset($_POST['telephone']) ? trim($_POST['telephone']) : '',
                'lettre_motivation' => isset($_POST['lettre_motivation']) ? trim($_POST['lettre_motivation']) : '',
                'statut' => isset($_POST['statut']) ? trim($_POST['statut']) : ''
            ];

            // Sanitize inputs
            $post_data['nom_candidat'] = htmlspecialchars($post_data['nom_candidat'], ENT_QUOTES, 'UTF-8');
            $post_data['prenom_candidat'] = htmlspecialchars($post_data['prenom_candidat'], ENT_QUOTES, 'UTF-8');
            $post_data['email_candidat'] = filter_var($post_data['email_candidat'], FILTER_SANITIZE_EMAIL);
            $post_data['telephone'] = preg_replace('/[^0-9+() -]/', '', $post_data['telephone']);
            $post_data['lettre_motivation'] = htmlspecialchars($post_data['lettre_motivation'], ENT_QUOTES, 'UTF-8');

            if ($this->validateCandidatureData($post_data, true)) {
                $candidature = new Candidature();
                $candidature->setIdCandidature($id);
                $candidature->setIdOffre($id_offre);
                $candidature->setNomCandidat($post_data['nom_candidat']);
                $candidature->setPrenomCandidat($post_data['prenom_candidat']);
                $candidature->setEmailCandidat($post_data['email_candidat']);
                $candidature->setTelephone($post_data['telephone']);
                $candidature->setLettreMotivation($post_data['lettre_motivation']);
                $candidature->setStatut($post_data['statut']);

                // Handle file upload
                if (isset($_FILES['cv_fichier']) && $_FILES['cv_fichier']['error'] != UPLOAD_ERR_NO_FILE) {
                    $cv_validation = $this->validateCVFile($_FILES['cv_fichier']);
                    
                    if ($cv_validation === false) {
                        $errors = $this->errors;
                    } elseif ($cv_validation === true) {
                        $target_dir = "uploads/cv/";
                        if (!is_dir($target_dir)) mkdir($target_dir, 0755, true);
                        
                        $file_name = time() . '_' . preg_replace('/[^a-zA-Z0-9._-]/', '', basename($_FILES['cv_fichier']['name']));
                        $target_file = $target_dir . $file_name;
                        
                        if (move_uploaded_file($_FILES['cv_fichier']['tmp_name'], $target_file)) {
                            $candidature->setCvFichier($file_name);
                        } else {
                            $errors['cv_fichier'] = 'Impossible de sauvegarder le fichier.';
                        }
                    }
                } else {
                    $candidature->setCvFichier($candidatureData['cv_fichier']);
                }

                if (empty($errors)) {
                    $stmtUp = $db->prepare("UPDATE candidature SET 
                              id_offre = :id_offre,
                              nom_candidat = :nom_candidat, 
                              prenom_candidat = :prenom_candidat, 
                              email_candidat = :email_candidat,
                              telephone = :telephone,
                              cv_fichier = :cv_fichier,
                              lettre_motivation = :lettre_motivation,
                              statut = :statut
                              WHERE id_candidature = :id");
                    $res = $stmtUp->execute([
                        'id_offre' => $candidature->getIdOffre(),
                        'nom_candidat' => $candidature->getNomCandidat(),
                        'prenom_candidat' => $candidature->getPrenomCandidat(),
                        'email_candidat' => $candidature->getEmailCandidat(),
                        'telephone' => $candidature->getTelephone(),
                        'cv_fichier' => $candidature->getCvFichier(),
                        'lettre_motivation' => $candidature->getLettreMotivation(),
                        'statut' => $candidature->getStatut(),
                        'id' => $candidature->getIdCandidature()
                    ]);
                    
                    if ($res) {
                        header('Location: index.php?action=list_candidatures_offre&id_offre=' . $id_offre);
                        exit();
                    } else {
                        $errors['general'] = 'Erreur lors de la modification de la candidature.';
                    }
                }
            } else {
                $errors = $this->errors;
            }
        }

        require_once __DIR__ . '/../views/back/candidatures/form.php';
    }

    public function delete() {
        if (!isset($_GET['id']) || empty($_GET['id'])) {
            header('Location: index.php?action=list_candidatures');
            exit();
        }

        $id = intval($_GET['id']);
        $db = Database::getInstance();
        $stmtSel = $db->prepare("SELECT * FROM candidature WHERE id_candidature = :id");
        $stmtSel->execute(['id' => $id]);
        $candidature = $stmtSel->fetch();
        
        if ($candidature) {
            $stmtDel = $db->prepare("DELETE FROM candidature WHERE id_candidature = :id");
            $stmtDel->execute(['id' => $id]);
            header('Location: index.php?action=list_candidatures_offre&id_offre=' . $candidature['id_offre']);
        } else {
            header('Location: index.php?action=list_candidatures');
        }
        exit();
    }

    // ─── STATISTIQUES CANDIDATURES ──────────────────────────────────────────
    public function stats() {
        $db = Database::getInstance();

        // KPIs globaux
        $totalCandidatures  = $db->query("SELECT COUNT(*) FROM candidature")->fetchColumn();
        $totalEnAttente     = $db->query("SELECT COUNT(*) FROM candidature WHERE statut='En attente'")->fetchColumn();
        $totalAcceptees     = $db->query("SELECT COUNT(*) FROM candidature WHERE statut='Acceptée'")->fetchColumn();
        $totalRejetees      = $db->query("SELECT COUNT(*) FROM candidature WHERE statut='Rejetée'")->fetchColumn();

        // Répartition par statut
        $stmtStatut = $db->query("SELECT statut, COUNT(*) as total FROM candidature GROUP BY statut ORDER BY total DESC");
        $statsByStatut = $stmtStatut->fetchAll();

        // Candidatures par mois (12 derniers mois)
        $stmtMonth = $db->query("SELECT DATE_FORMAT(date_candidature, '%Y-%m') as mois, COUNT(*) as total
                                  FROM candidature
                                  WHERE date_candidature >= DATE_SUB(CURDATE(), INTERVAL 12 MONTH)
                                  GROUP BY mois ORDER BY mois ASC");
        $statsByMonth = $stmtMonth->fetchAll();

        // Top 5 offres par nombre de candidatures
        $stmtTop = $db->query("SELECT o.titre, COUNT(c.id_candidature) as total
                               FROM candidature c
                               JOIN offre o ON c.id_offre = o.id_offre
                               GROUP BY o.id_offre, o.titre
                               ORDER BY total DESC
                               LIMIT 5");
        $topOffres = $stmtTop->fetchAll();

        // Taux d'acceptation global
        $tauxAcceptation = $totalCandidatures > 0
            ? round($totalAcceptees / $totalCandidatures * 100, 1) : 0;

        require_once __DIR__ . '/../views/back/candidatures/stats.php';
    }
}
?>
