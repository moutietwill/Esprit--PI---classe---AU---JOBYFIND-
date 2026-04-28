<?php
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../config/Mailer.php';

class EventsController extends Controller {

    public function __construct() {
        // Initialization if needed
    }

    /**
     * List all events (public view)
     */
    public function index() {
        $events = $this->fetchAllEvents();
        $this->render('events/index', ['events' => $events]);
    }

    /**
     * Show single event details
     */
    public function show($id) {
        try {
            $event = $this->fetchEventById($id);
            if (!$event) {
                $this->render('events/show', ['error' => 'Event not found']); // Fallback 404
                return;
            }
            $this->render('events/show', ['event' => $event]);
        } catch (Exception $e) {
            error_log('Error showing event: ' . $e->getMessage());
            $this->render('errors/500');
        }
    }

    /**
     * Create new event (show form)
     */
    public function create() {
        $this->render('events/create');
    }

    /**
     * Store new event in database
     */
   public function store() {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $description = trim($_POST['description'] ?? '');
            if (empty($description) || strlen($description) < 10) {
                 die("Erreur : La description est obligatoire et doit avoir au moins 10 caracteres");
            }

            $event = new Event([
                'titre' => $_POST['titre'] ?? '',
                'description' => $description,
                'date' => $_POST['date'] ?? '',
                'lieu' => $_POST['lieu'] ?? '',
                'idOrganisateur' => (int)($_POST['idOrganisateur'] ?? 0),
                'image' => 'public/assets/images/event/default.jpg'
            ]);

            $result = $this->persistEvent($event);

            if ($result) {
                $this->redirect('/events?success=1');
            } else {
                $this->redirect('/events?error=1');
            }
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->redirect('/events?error=1');
        }
    }
}

    /**
     * Edit event form
     */
    public function edit($id) {
        try {
            $event = $this->fetchEventById($id);
            if (!$event) {
                $this->redirect('/events?error=1&msg=Event not found');
                return;
            }
            $this->render('events/edit', ['event' => $event]);
        } catch (Exception $e) {
            error_log('Error editing event: ' . $e->getMessage());
            $this->redirect('/events?error=1&msg=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Update event in database
     */
    public function update($id) {
    if ($_SERVER['REQUEST_METHOD'] === 'POST') {
        try {
            $description = trim($_POST['description'] ?? '');
            if (empty($description) || strlen($description) < 10) {
                 die("Erreur : La description est obligatoire et doit avoir au moins 10 caracteres");
            }

            $event = $this->fetchEventById($id);

            if (!$event) {
                $this->redirect('/events?error=notfound');
                return;
            }

            $event->setTitre($_POST['titre']);
            $event->setDescription($description);
            $event->setDate($_POST['date']);
            $event->setLieu($_POST['lieu']);
            $event->setIdOrganisateur((int)$_POST['idOrganisateur']);

            $this->persistEvent($event);

            $this->redirect('/events?success=update');
        } catch (Exception $e) {
            error_log($e->getMessage());
            $this->redirect('/events?error=1');
        }
    }
}

    /**
     * Delete event from database
     */
    public function delete($id) {
        try {
            $result = $this->removeEventRecord($id);
            
            if ($result) {
                $this->redirect('/events?success=1&action=delete');
            } else {
                $this->redirect('/events?error=1');
            }
        } catch (Exception $e) {
            error_log('Error deleting event: ' . $e->getMessage());
            $this->redirect('/events?error=1&msg=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Store user registration — handles AJAX POST from front-end
     * URL: /events/inscrire/{idEvenement}
     */
    public function inscrire($idEvenement) {
        header('Content-Type: application/json');

        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            echo json_encode(['success' => false, 'message' => 'Méthode non autorisée.']);
            exit;
        }

        try {
            $idEvenement = (int) $idEvenement;
            $prenom = trim($_POST['prenom'] ?? '');
            $nom    = trim($_POST['nom']    ?? '');
            $email  = trim($_POST['email']  ?? '');

            if (!$prenom || !$nom || !$email) {
                echo json_encode(['success' => false, 'message' => 'Tous les champs sont requis.']);
                exit;
            }

            if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
                echo json_encode(['success' => false, 'message' => 'Adresse email invalide.']);
                exit;
            }

            $event = $this->fetchEventById($idEvenement);
            if (!$event) {
                echo json_encode(['success' => false, 'message' => 'Événement introuvable.']);
                exit;
            }

            // Save inscription
            $inscription = new Inscription([
                'idEvenement'     => $idEvenement,
                'nom'             => $nom,
                'prenom'          => $prenom,
                'email'           => $email,
                'dateInscription' => date('Y-m-d'),
                'statut'          => 'confirmée',
            ]);

            $ok = $this->saveInscription($inscription);

            if ($ok) {
                // Envoyer un email de confirmation
                try {
                    $mailer = new Mailer();
                    $mailer->sendInscriptionConfirmation(
                        $prenom,
                        $nom,
                        $email,
                        $event->getTitre(),
                        $event->getDate(),
                        $event->getLieu()
                    );
                } catch (Exception $emailError) {
                    error_log('Email sending error: ' . $emailError->getMessage());
                    // Continue même si l'email échoue - l'inscription est confirmée
                }
                
                $total = $this->countInscriptions($idEvenement);
                echo json_encode([
                    'success' => true,
                    'message' => "Inscription confirmée pour {$prenom} {$nom} ! Un email de confirmation a été envoyé.",
                    'inscrits' => $total,
                ]);
            } else {
                echo json_encode(['success' => false, 'message' => 'Erreur lors de l\'inscription.']);
            }

        } catch (Exception $e) {
            error_log('Inscription error: ' . $e->getMessage());
            echo json_encode(['success' => false, 'message' => 'Erreur serveur : ' . $e->getMessage()]);
        }
        exit;
    }

    /**
     * Register user for event (old form-based view — kept for compatibility)
     */
    public function register($id) {
        try {
            $event = $this->fetchEventById($id);
            if (!$event) {
                $this->render('errors/404');
                return;
            }
            $this->render('events/register', ['event' => $event]);
        } catch (Exception $e) {
            error_log('Error registering: ' . $e->getMessage());
            $this->render('errors/500');
        }
    }

    /**
     * Store user registration
     */
    public function storeRegistration($id) {
        try {
            $event = $this->fetchEventById($id);
            if (!$event) {
                $this->redirect('/events');
                return;
            }

            if ($_SERVER['REQUEST_METHOD'] === 'POST') {
                $prenom = $_POST['prenom'] ?? '';
                $nom = $_POST['nom'] ?? '';
                $email = $_POST['email'] ?? '';

                if (!$prenom || !$nom || !$email) {
                    $this->render('events/register', [
                        'event' => $event, 
                        'error' => 'Tous les champs sont requis'
                    ]);
                    return;
                }

                $this->redirect('/events?success=1&msg=Registration successful');
            }
        } catch (Exception $e) {
            error_log('Error storing registration: ' . $e->getMessage());
            $this->redirect('/events?error=1&msg=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Get all events (for API)
     */
    public function getEvents() {
        return $this->fetchAllEvents();
    }

    /**
     * Search events API
     */
    public function search($term) {
        header('Content-Type: application/json');

        try {
            $events = $this->searchEventRecords($term);
            echo json_encode($events);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    /**
     * Get events by category API
     */
    public function getByCategory($category) {
        header('Content-Type: application/json');

        try {
            $events = $this->fetchEventsByCategory($category);
            echo json_encode($events);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }
}
?>
