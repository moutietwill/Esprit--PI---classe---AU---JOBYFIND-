<?php
require_once __DIR__ . '/../models/Event.php';

class EventsController extends Controller {

    public function __construct() {
        // Initialization if needed
    }

    /**
     * List all events (public view)
     */
    public function index() {
        $events = $this->fetchAllEvents();
        $users = $this->fetchAllUsers();
        $db = $this->getEventDb();
        $stmt = $db->prepare("SELECT COUNT(*) AS total FROM inscription");
        $stmt->execute();
        $inscriptionsCount = (int) ($stmt->fetch(PDO::FETCH_ASSOC)['total'] ?? 0);

        $this->render('events/index', [
            'events' => $events,
            'users' => $users,
            'inscriptionsCount' => $inscriptionsCount
        ]);
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
                'idOrganisateur' => (int)($_POST['idOrganisateur'] ?? 0)
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
     * Register user for event
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
