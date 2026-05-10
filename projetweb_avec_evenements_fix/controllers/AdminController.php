<?php
require_once __DIR__ . '/../models/Event.php';

class AdminController extends Controller {

    public function __construct() {
        if (!isset($_SESSION)) {
            session_start();
        }
        if (!isset($_SESSION['user_id']) || !isset($_SESSION['role']) || $_SESSION['role'] !== 'Admin') {
            $this->redirect('../../views/frontoffice/signin.php');
        }
    }

    /**
     * Display admin dashboard
     */
    public function index() {
        $this->redirect('/admin/events');
    }

    /**
     * Display events management page
     */
    public function events() {
        $events = $this->fetchAllEvents();
        $this->render('admin/events', ['events' => $events]);
    }

    /**
     * Validate event data
     */
    private function validateEventData($data, $isUpdate = false) {
        $errors = [];

        // titre: required string, not only numbers, must contain at least one letter
        if (empty(trim($data['titre'] ?? ''))) {
            $errors[] = 'Le titre est requis';
        } elseif (!is_string($data['titre'])) {
            $errors[] = 'Le titre doit être une chaîne de caractères';
        } else {
            $titre = trim($data['titre']);
            // Check if title is only numbers
            if (preg_match('/^\d+$/', $titre)) {
                $errors[] = 'Le titre doit être une chaîne de caractères (pas seulement des chiffres)';
            } 
            // Check if title contains at least one letter
            elseif (!preg_match('/[a-zA-Zàâäçéèêëîïôöùûüœæ]/ui', $titre)) {
                $errors[] = 'Le titre doit contenir au moins une lettre';
            }
        }

        // description: required text, min 10 chars, at least one letter
        if (empty(trim($data['description'] ?? ''))) {
            $errors[] = 'La description est requise';
        } elseif (!is_string($data['description'])) {
            $errors[] = 'La description doit être une chaîne de caractères';
        } else {
            $desc = trim($data['description']);
            if (strlen($desc) < 10) {
                $errors[] = 'La description doit contenir au moins 10 caractères';
            } elseif (preg_match('/^\d+$/', $desc)) {
                $errors[] = 'La description doit être une chaîne de caractères (pas seulement des chiffres)';
            } elseif (!preg_match('/[a-zA-Zàâäçéèêëîïôöùûüœæ]/ui', $desc)) {
                $errors[] = 'La description doit contenir au moins une lettre';
            }
        }

        // date: required valid date
        if (empty($data['date'] ?? '')) {
            $errors[] = 'La date est requise';
        } else {
            $date = DateTime::createFromFormat('Y-m-d', $data['date']);
            if (!$date || $date->format('Y-m-d') !== $data['date']) {
                $errors[] = 'La date doit être au format YYYY-MM-DD valide';
            }
        }

        // lieu: required string, not only numbers, must contain at least one letter
        if (empty(trim($data['lieu'] ?? ''))) {
            $errors[] = 'Le lieu est requis';
        } elseif (!is_string($data['lieu'])) {
            $errors[] = 'Le lieu doit être une chaîne de caractères';
        } else {
            $lieu = trim($data['lieu']);
            if (preg_match('/^\d+$/', $lieu)) {
                $errors[] = 'Le lieu doit être une chaîne de caractères (pas seulement des chiffres)';
            } elseif (!preg_match('/[a-zA-Zàâäçéèêëîïôöùûüœæ]/ui', $lieu)) {
                $errors[] = 'Le lieu doit contenir au moins une lettre';
            }
        }

        // idOrganisateur: optional - can be text or number
        $idOrgValue = trim($data['idOrganisateur'] ?? '');
        if ($idOrgValue !== '' && strlen($idOrgValue) < 2 && !is_numeric($idOrgValue)) {
            $errors[] = 'L\'ID organisateur doit être soit un chiffre, soit au moins 2 caractères';
        }

        return $errors;
    }

    /**
     * Create new event (store)
     */
    public function storeEvent() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            $isAjax = !empty($_SERVER['HTTP_X_REQUESTED_WITH']) && strtolower($_SERVER['HTTP_X_REQUESTED_WITH']) === 'xmlhttprequest';

            try {
                // Validate input data
                $validationErrors = $this->validateEventData($_POST);
                if (!empty($validationErrors)) {
                    $errorMsg = implode(', ', $validationErrors);
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => $errorMsg]);
                        exit;
                    }
                    $this->redirect('/admin/events?error=1&msg=' . urlencode($errorMsg));
                    return;
                }

                $imagePath = 'public/assets/images/event/e1.png';
                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imagePath = $this->handleImageUpload($_FILES['image']);
                }

                $idOrganisateur = $_POST['idOrganisateur'] ?? '';
                if (empty($idOrganisateur) && isset($_SESSION['user_id'])) {
                    $idOrganisateur = $_SESSION['user_id'];
                }

                $event = new Event([
                    'titre' => $_POST['titre'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'date' => $_POST['date'] ?? '',
                    'lieu' => $_POST['lieu'] ?? '',
                    'idOrganisateur' => $idOrganisateur,
                    'image' => $imagePath
                ]);

                error_log('storeEvent: Creating event with data: ' . json_encode($event->toArray()));

                $result = $this->persistEvent($event);
                
                error_log('storeEvent: Result: ' . ($result ? 'Success ID=' . $result->getId() : 'Failed'));

                if ($result) {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => true, 'id' => $result->getId(), 'event' => $result->toArray()]);
                        exit;
                    }
                    $this->redirect('/admin/events?success=1&action=create');
                } else {
                    if ($isAjax) {
                        header('Content-Type: application/json');
                        echo json_encode(['success' => false, 'error' => 'Failed to create event']);
                        exit;
                    }
                    $this->redirect('/admin/events?error=1');
                }
            } catch (Exception $e) {
                error_log('Error creating event: ' . $e->getMessage());
                if ($isAjax) {
                    header('Content-Type: application/json');
                    echo json_encode(['success' => false, 'error' => $e->getMessage()]);
                    exit;
                }
                $this->redirect('/admin/events?error=1&msg=' . urlencode($e->getMessage()));
            }
        }
    }

    /**
     * Update event
     */
    public function updateEvent($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            // Validate idEvenement - positive integer OR non-empty string
            $idNum = filter_var($id, FILTER_VALIDATE_INT);
            if ($idNum !== false && $idNum <= 0) {
                $this->redirect('/admin/events?error=1&msg=Si numérique, l\'ID événement doit être un entier positif (> 0)');
                return;
            }
            if (empty(trim($id))) {
                $this->redirect('/admin/events?error=1&msg=L\'ID événement est requis');
                return;
            }

            try {
                $event = $this->fetchEventById($id);
                
                if (!$event) {
                    $this->redirect('/admin/events?error=1&msg=Event not found');
                    return;
                }

                // Validate input data
                $validationErrors = $this->validateEventData($_POST, true);
                if (!empty($validationErrors)) {
                    $errorMsg = implode(', ', $validationErrors);
                    $this->redirect('/admin/events?error=1&msg=' . urlencode($errorMsg));
                    return;
                }

                $event->setTitre($_POST['titre'] ?? '');
                $event->setDescription($_POST['description'] ?? '');
                $event->setDate($_POST['date'] ?? '');
                $event->setLieu($_POST['lieu'] ?? '');
                $idOrganisateur = trim($_POST['idOrganisateur'] ?? '');
                if (empty($idOrganisateur)) {
                    $idOrganisateur = $event->getIdOrganisateur();
                }
                $event->setIdOrganisateur($idOrganisateur);

                if (isset($_FILES['image']) && $_FILES['image']['error'] === UPLOAD_ERR_OK) {
                    $imagePath = $this->handleImageUpload($_FILES['image'], $event->getImage());
                    $event->setImage($imagePath);
                }

                $result = $this->persistEvent($event);
                
                if ($result) {
                    $this->redirect('/admin/events?success=1&action=update');
                } else {
                    $this->redirect('/admin/events?error=1');
                }
            } catch (Exception $e) {
                error_log('Error updating event: ' . $e->getMessage());
                $this->redirect('/admin/events?error=1&msg=' . urlencode($e->getMessage()));
            }
        }
    }

    /**
     * Delete event
     */
    public function deleteEvent($id) {
        // Validate idEvenement - positive integer OR non-empty string
        $idNum = filter_var($id, FILTER_VALIDATE_INT);
        if ($idNum !== false && $idNum <= 0) {
            $this->redirect('/admin/events?error=1&msg=Si numérique, l\'ID événement doit être un entier positif (> 0)');
            return;
        }
        if (empty(trim($id))) {
            $this->redirect('/admin/events?error=1&msg=L\'ID événement est requis');
            return;
        }

        try {
            $result = $this->removeEventRecord($id);
            
            if ($result) {
                $this->redirect('/admin/events?success=1&action=delete');
            } else {
                $this->redirect('/admin/events?error=1');
            }
        } catch (Exception $e) {
            error_log('Error deleting event: ' . $e->getMessage());
            $this->redirect('/admin/events?error=1&msg=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Get events for category (via API)
     */
    public function getEventsByCategory($category) {
        header('Content-Type: application/json');

        try {
            $events = $this->fetchEventsByCategory($category);
            echo json_encode($events);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Search events (via API)
     */
    public function searchEvents($term) {
        header('Content-Type: application/json');

        try {
            $events = $this->searchEventRecords($term);
            echo json_encode($events);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Display inscriptions management page
     */
    public function inscriptions() {
        $inscriptions = $this->fetchAllInscriptions();
        $this->render('admin/inscriptions', ['inscriptions' => $inscriptions]);
    }

    /**
     * Update inscription (store changes)
     */
    public function updateInscription($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $inscription = $this->fetchInscriptionById($id);
                
                if (!$inscription) {
                    $this->redirect('/admin/inscriptions?error=1&msg=Inscription not found');
                    return;
                }

                // Validate data
                $errors = [];
                if (empty(trim($_POST['nom'] ?? ''))) {
                    $errors[] = 'Le nom est requis';
                }
                if (empty(trim($_POST['prenom'] ?? ''))) {
                    $errors[] = 'Le prénom est requis';
                }
                if (empty(trim($_POST['email'] ?? ''))) {
                    $errors[] = 'L\'email est requis';
                }
                if (!filter_var($_POST['email'] ?? '', FILTER_VALIDATE_EMAIL)) {
                    $errors[] = 'L\'email n\'est pas valide';
                }

                if (!empty($errors)) {
                    $errorMsg = implode(', ', $errors);
                    $this->redirect('/admin/inscriptions?error=1&msg=' . urlencode($errorMsg));
                    return;
                }

                $inscription->setNom(trim($_POST['nom'] ?? ''));
                $inscription->setPrenom(trim($_POST['prenom'] ?? ''));
                $inscription->setEmail(trim($_POST['email'] ?? ''));
                $inscription->setStatut($_POST['statut'] ?? 'confirmée');

                $result = $this->updateInscriptionData($inscription);
                
                if ($result) {
                    $this->redirect('/admin/inscriptions?success=1&action=update');
                } else {
                    $this->redirect('/admin/inscriptions?error=1');
                }
            } catch (Exception $e) {
                error_log('Error updating inscription: ' . $e->getMessage());
                $this->redirect('/admin/inscriptions?error=1&msg=' . urlencode($e->getMessage()));
            }
        }
    }

    /**
     * Delete inscription
     */
    public function deleteInscription($id) {
        try {
            $inscription = $this->fetchInscriptionById($id);
            
            if (!$inscription) {
                $this->redirect('/admin/inscriptions?error=1&msg=Inscription not found');
                return;
            }

            $result = $this->removeInscription($id);
            
            if ($result) {
                $this->redirect('/admin/inscriptions?success=1&action=delete');
            } else {
                $this->redirect('/admin/inscriptions?error=1');
            }
        } catch (Exception $e) {
            error_log('Error deleting inscription: ' . $e->getMessage());
            $this->redirect('/admin/inscriptions?error=1&msg=' . urlencode($e->getMessage()));
        }
    }

    // Legacy methods for backward compatibility
    public function getEvents() {
        return $this->fetchAllEvents();
    }
}
?>
