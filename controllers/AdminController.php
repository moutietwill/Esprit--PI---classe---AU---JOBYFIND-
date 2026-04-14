<?php
require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/User.php';

class AdminController extends Controller {

    public function __construct() {
        // Initialization if needed
    }

    /**
     * Resolve organiser text to a valid utilisateur ID.
     * If the organiser value is numeric, use it directly.
     * Otherwise create a new utilisateur entry based on the organiser name.
     */
    private function resolveOrganizerId($organizerInput) {
        $organizerInput = trim($organizerInput);
        if ($organizerInput === '') {
            return null;
        }

        if (is_numeric($organizerInput)) {
            return (int) $organizerInput;
        }

        $email = strtolower(preg_replace('/[^a-z0-9]+/', '.', $organizerInput));
        $email = trim($email, '.') . '@organisateur.local';

        $existingUser = User::getByEmail($email);
        if ($existingUser) {
            return $existingUser->getId();
        }

        $parts = preg_split('/\s+/', $organizerInput);
        $prenom = $parts[0] ?? 'Organisateur';
        $nom = isset($parts[1]) ? implode(' ', array_slice($parts, 1)) : 'Nom';

        $user = new User([
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'role' => 'Entrepreneur',
            'status' => 'Actif'
        ]);

        $createdUser = $user->save();
        return $createdUser ? $createdUser->getId() : null;
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
        $events = Event::getAll();
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

        // idOrganisateur: required - positive integer OR non-empty string
        if (!isset($data['idOrganisateur']) || empty(trim($data['idOrganisateur'] ?? ''))) {
            $errors[] = 'L\'ID organisateur est requis';
        } else {
            $idOrgValue = trim($data['idOrganisateur']);
            $idOrgNum = filter_var($idOrgValue, FILTER_VALIDATE_INT);
            
            // If numeric, must be positive integer > 0
            if ($idOrgNum !== false) {
                if ($idOrgNum <= 0) {
                    $errors[] = 'Si numérique, l\'ID organisateur doit être un entier positif (> 0)';
                }
            }
            // If string, must be at least 2 characters
            else if (strlen($idOrgValue) < 2) {
                $errors[] = 'L\'ID organisateur doit être soit un chiffre positif, soit au moins 2 caractères';
            }
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

                $idOrganisateur = isset($_POST['idOrganisateur']) ? (int)$_POST['idOrganisateur'] : null;
                
                // Check if organizer exists; if not, try to reuse an existing user by generated email
                if ($idOrganisateur) {
                    $organizer = User::getById($idOrganisateur);
                    if (!$organizer) {
                        $organizerEmail = 'organizer-' . $idOrganisateur . '@organizer.local';
                        $existingUser = User::getByEmail($organizerEmail);

                        if ($existingUser) {
                            $idOrganisateur = $existingUser->getId();
                        } else {
                            // Create a new user as organizer
                            $newUser = new User([
                                'prenom' => 'Organisateur',
                                'nom' => 'ID ' . $idOrganisateur,
                                'email' => $organizerEmail,
                                'role' => 'Entrepreneur',
                                'status' => 'Actif'
                            ]);
                            $createdUser = $newUser->save();
                            if ($createdUser) {
                                $idOrganisateur = $createdUser->getId();
                            }
                        }
                    }
                }

                $event = new Event([
                    'titre' => $_POST['titre'] ?? '',
                    'description' => $_POST['description'] ?? '',
                    'date' => $_POST['date'] ?? '',
                    'lieu' => $_POST['lieu'] ?? '',
                    'idOrganisateur' => $idOrganisateur
                ]);

                error_log('storeEvent: Creating event with data: ' . json_encode($event->toArray()));

                $result = $event->save();
                
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
                $event = Event::getById($id);
                
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
                $event->setIdOrganisateur(isset($_POST['idOrganisateur']) ? (int)$_POST['idOrganisateur'] : null);

                $result = $event->save();
                
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
            $result = Event::delete($id);
            
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
     * Create new user (store)
     */
    public function storeUser() {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                // Check if email already exists
                $existing = User::getByEmail($_POST['email'] ?? '');
                if ($existing) {
                    $this->redirect('/admin?error=1&msg=Email already exists');
                    return;
                }

                $user = new User([
                    'prenom' => $_POST['prenom'] ?? '',
                    'nom' => $_POST['nom'] ?? '',
                    'email' => $_POST['email'] ?? '',
                    'role' => $_POST['role'] ?? '',
                    'status' => $_POST['status'] ?? 'Actif',
                    'date' => date('d M Y'),
                    'last' => 'Jamais'
                ]);

                $result = $user->save();
                
                if ($result) {
                    $this->redirect('/admin?success=1&action=create');
                } else {
                    $this->redirect('/admin?error=1');
                }
            } catch (Exception $e) {
                error_log('Error creating user: ' . $e->getMessage());
                $this->redirect('/admin?error=1&msg=' . urlencode($e->getMessage()));
            }
        }
    }

    /**
     * Update user
     */
    public function updateUser($id) {
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            try {
                $user = User::getById($id);
                
                if (!$user) {
                    $this->redirect('/admin?error=1&msg=User not found');
                    return;
                }

                $user->setPrenom($_POST['prenom'] ?? '');
                $user->setNom($_POST['nom'] ?? '');
                $user->setEmail($_POST['email'] ?? '');
                $user->setRole($_POST['role'] ?? '');
                $user->setStatus($_POST['status'] ?? '');

                $result = $user->save();
                
                if ($result) {
                    $this->redirect('/admin?success=1&action=update');
                } else {
                    $this->redirect('/admin?error=1');
                }
            } catch (Exception $e) {
                error_log('Error updating user: ' . $e->getMessage());
                $this->redirect('/admin?error=1&msg=' . urlencode($e->getMessage()));
            }
        }
    }

    /**
     * Delete user
     */
    public function deleteUser($id) {
        try {
            $result = User::delete($id);
            
            if ($result) {
                $this->redirect('/admin?success=1&action=delete');
            } else {
                $this->redirect('/admin?error=1');
            }
        } catch (Exception $e) {
            error_log('Error deleting user: ' . $e->getMessage());
            $this->redirect('/admin?error=1&msg=' . urlencode($e->getMessage()));
        }
    }

    /**
     * Get events for category (via API)
     */
    public function getEventsByCategory($category) {
        header('Content-Type: application/json');

        try {
            $events = Event::getByCategory($category);
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
            $events = Event::search($term);
            echo json_encode($events);
        } catch (Exception $e) {
            echo json_encode(['error' => $e->getMessage()]);
        }
    }

    // Legacy methods for backward compatibility
    public function getEvents() {
        return Event::getAll();
    }

    public function getUsers() {
        return User::getAll();
    }

    private function findEvent($id) {
        return Event::getById($id);
    }

    private function findUser($id) {
        return User::getById($id);
    }
}
?>
