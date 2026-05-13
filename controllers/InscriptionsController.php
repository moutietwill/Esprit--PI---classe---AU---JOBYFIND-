
<?php

require_once __DIR__ . '/../models/Inscription.php';
require_once __DIR__ . '/../models/Event.php';
require_once __DIR__ . '/../models/User.php';

class InscriptionsController extends Controller
{
    private function payload(): array
    {
        $raw = file_get_contents('php://input');
        $decoded = json_decode((string) $raw, true);
        if (is_array($decoded)) {
            return $decoded;
        }

        return $_POST;
    }

    private function validateStatus($status): bool
    {
        $normalized = Inscription::normalizeStatus($status);
        return in_array($normalized, Inscription::getAllowedStatuses(), true);
    }

    private function resolveUserIdFromPayload(array $data): int
    {
        $idUtilisateur = (int) ($data['idUtilisateur'] ?? 0);
        if ($idUtilisateur > 0) {
            return $this->fetchUserById($idUtilisateur) ? $idUtilisateur : 0;
        }

        $prenom = trim((string) ($data['prenom'] ?? ''));
        $nom = trim((string) ($data['nom'] ?? ''));
        $email = trim((string) ($data['email'] ?? ''));

        if ($prenom === '' || $nom === '' || $email === '') {
            return 0;
        }

        if (!filter_var($email, FILTER_VALIDATE_EMAIL)) {
            return 0;
        }

        $existing = User::getByEmail($email);
        if ($existing) {
            return (int) $existing->getId();
        }

        $user = new User([
            'prenom' => $prenom,
            'nom' => $nom,
            'email' => $email,
            'role' => 'Entrepreneur',
            'status' => 'Actif',
        ]);
        $created = $user->save();

        return $created ? (int) $created->getId() : 0;
    }

    public function index()
    {
        $inscriptions = Inscription::getAll();
        $users = $this->fetchAllUsers();
        $events = $this->fetchAllEvents();
        $statuses = Inscription::getAllowedStatuses();

        $this->render('admin/inscriptions', [
            'inscriptions' => $inscriptions,
            'users' => $users,
            'events' => $events,
            'statuses' => $statuses,
        ]);
    }

    public function byEvent($id = null)
    {
        $eventId = $id ?? ($_GET['id'] ?? null);
        if (!$eventId) {
            $this->json(['error' => 'Event ID required'], 400);
        }

        $inscriptions = Inscription::getByEvent((int) $eventId);
        $this->json(['success' => true, 'inscriptions' => $inscriptions]);
    }

    public function create()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $data = $this->payload();
        $idUtilisateur = $this->resolveUserIdFromPayload($data);
        $idEvenement = (int) ($data['idEvenement'] ?? 0);
        $statut = $data['statut'] ?? 'Confirmee';
        $dateInscription = $data['dateInscription'] ?? date('Y-m-d H:i:s');

        if ($idUtilisateur <= 0 || $idEvenement <= 0) {
            $this->json(['error' => 'Champs obligatoires: prenom, nom, email, evenement'], 400);
        }

        if (!$this->fetchEventById($idEvenement)) {
            $this->json(['error' => 'Evenement introuvable'], 404);
        }

        if (!$this->validateStatus($statut)) {
            $this->json(['error' => 'Statut invalide'], 400);
        }

        if (Inscription::isRegistered($idUtilisateur, $idEvenement)) {
            $this->json(['error' => 'User already registered for this event'], 409);
        }

        $inscription = new Inscription([
            'idUtilisateur' => $idUtilisateur,
            'idEvenement' => $idEvenement,
            'statut' => $statut,
            'dateInscription' => $dateInscription,
        ]);

        $result = $inscription->save();
        if (!$result) {
            $this->json(['error' => 'Failed to create inscription'], 500);
        }

        $created = Inscription::getById($result);
        $this->json([
            'success' => true,
            'message' => 'Inscription creee avec succes',
            'inscription' => $created,
        ], 201);
    }

    public function update()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $data = $this->payload();
        $id = (int) ($data['id'] ?? ($_GET['id'] ?? 0));
        $idUtilisateur = (int) ($data['idUtilisateur'] ?? 0);
        $idEvenement = (int) ($data['idEvenement'] ?? 0);
        $statut = $data['statut'] ?? '';
        $dateInscription = $data['dateInscription'] ?? null;

        if ($id <= 0 || $idUtilisateur <= 0 || $idEvenement <= 0 || $statut === '') {
            $this->json(['error' => 'Missing required fields'], 400);
        }

        if (!$this->fetchUserById($idUtilisateur)) {
            $this->json(['error' => 'Utilisateur introuvable'], 404);
        }

        if (!$this->fetchEventById($idEvenement)) {
            $this->json(['error' => 'Evenement introuvable'], 404);
        }

        $current = Inscription::findById($id);
        if (!$current) {
            $this->json(['error' => 'Inscription not found'], 404);
        }

        if (!$this->validateStatus($statut)) {
            $this->json(['error' => 'Statut invalide'], 400);
        }

        $existingSamePair = Inscription::isRegistered($idUtilisateur, $idEvenement);
        if (
            $existingSamePair
            && ((int) $current['idUtilisateur'] !== $idUtilisateur || (int) $current['idEvenement'] !== $idEvenement)
        ) {
            $this->json(['error' => 'Cette inscription existe deja'], 409);
        }

        $updated = Inscription::updateById($id, $idUtilisateur, $idEvenement, $statut, $dateInscription);
        if (!$updated) {
            $this->json(['error' => 'Failed to update inscription'], 500);
        }

        $inscription = Inscription::getById($id);
        $this->json([
            'success' => true,
            'message' => 'Inscription modifiee avec succes',
            'inscription' => $inscription,
        ]);
    }

    public function delete()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'DELETE' && $_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $data = $this->payload();
        $id = (int) ($data['id'] ?? ($_GET['id'] ?? 0));

        if ($id <= 0) {
            $this->json(['error' => 'Inscription ID required'], 400);
        }

        if (!Inscription::findById($id)) {
            $this->json(['error' => 'Inscription not found'], 404);
        }

        if (!Inscription::delete($id)) {
            $this->json(['error' => 'Failed to delete inscription'], 500);
        }

        $this->json(['success' => true, 'message' => 'Inscription supprimee']);
    }

    public function updateStatus()
    {
        if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
            $this->json(['error' => 'Method not allowed'], 405);
        }

        $data = $this->payload();
        $id = (int) ($data['id'] ?? ($_GET['id'] ?? 0));
        $statut = $data['statut'] ?? '';

        if ($id <= 0 || $statut === '') {
            $this->json(['error' => 'Missing required fields'], 400);
        }

        if (!Inscription::findById($id)) {
            $this->json(['error' => 'Inscription not found'], 404);
        }

        if (!$this->validateStatus($statut)) {
            $this->json(['error' => 'Statut invalide'], 400);
        }

        if (!Inscription::updateStatusById($id, $statut)) {
            $this->json(['error' => 'Failed to update status'], 500);
        }

        $this->json(['success' => true, 'message' => 'Statut mis a jour']);
    }
}
