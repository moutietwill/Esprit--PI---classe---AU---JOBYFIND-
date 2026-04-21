
<?php

class Inscription
{
    private $idInscription;
    private $idUtilisateur;
    private $idEvenement;
    private $dateInscription;
    private $statut;

    public function __construct($data = [])
    {
        if (is_object($data)) {
            $data = (array) $data;
        }

        $this->idInscription = $data['idInscription'] ?? null;
        $this->idUtilisateur = (int) ($data['idUtilisateur'] ?? 0);
        $this->idEvenement = (int) ($data['idEvenement'] ?? 0);
        $this->dateInscription = $data['dateInscription'] ?? date('Y-m-d H:i:s');
        $this->statut = self::normalizeStatus($data['statut'] ?? 'Confirmee');
    }

    public function getId() { return $this->idInscription; }
    public function getIdUtilisateur() { return $this->idUtilisateur; }
    public function getIdEvenement() { return $this->idEvenement; }
    public function getDateInscription() { return $this->dateInscription; }
    public function getStatut() { return $this->statut; }

    public function setIdUtilisateur($value) { $this->idUtilisateur = (int) $value; }
    public function setIdEvenement($value) { $this->idEvenement = (int) $value; }
    public function setDateInscription($value) { $this->dateInscription = (string) $value; }
    public function setStatut($value) { $this->statut = self::normalizeStatus($value); }

    public function toArray()
    {
        return [
            'idInscription' => $this->idInscription,
            'idUtilisateur' => $this->idUtilisateur,
            'idEvenement' => $this->idEvenement,
            'dateInscription' => $this->dateInscription,
            'statut' => $this->statut,
        ];
    }

    public static function getAllowedStatuses()
    {
        return ['Confirmee', 'Present', 'Absent', 'Annulee'];
    }

    public static function normalizeStatus($status)
    {
        $value = trim((string) $status);
        if ($value === '') {
            return 'Confirmee';
        }

        $normalized = strtolower($value);
        $map = [
            'confirmee' => 'Confirmee',
            'confirmã©e' => 'Confirmee',
            'present' => 'Present',
            'prã©sent' => 'Present',
            'absent' => 'Absent',
            'annulee' => 'Annulee',
            'annulã©e' => 'Annulee',
        ];

        if (isset($map[$normalized])) {
            return $map[$normalized];
        }

        return in_array($value, self::getAllowedStatuses(), true) ? $value : 'Confirmee';
    }

    public function save()
    {
        $db = Database::getInstance()->getConnection();

        if ($this->idInscription) {
            $stmt = $db->prepare(
                'UPDATE inscription SET idUtilisateur = ?, idEvenement = ?, statut = ?, dateInscription = ? WHERE idInscription = ?'
            );

            return $stmt->execute([
                $this->idUtilisateur,
                $this->idEvenement,
                self::normalizeStatus($this->statut),
                $this->dateInscription,
                $this->idInscription,
            ]);
        }

        $stmt = $db->prepare(
            'INSERT INTO inscription (idUtilisateur, idEvenement, statut, dateInscription) VALUES (?, ?, ?, ?)'
        );

        $stmt->execute([
            $this->idUtilisateur,
            $this->idEvenement,
            self::normalizeStatus($this->statut),
            $this->dateInscription,
        ]);

        $this->idInscription = $db->lastInsertId();
        return $this->idInscription;
    }

    public static function getAll()
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            'SELECT i.*, u.prenom, u.nom, u.email, e.titre AS titre_evenement
             FROM inscription i
             LEFT JOIN utilisateur u ON i.idUtilisateur = u.idUtilisateur
             LEFT JOIN evenement e ON i.idEvenement = e.idEvenement
             ORDER BY i.dateInscription DESC'
        );
        $stmt->execute();

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getById($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            'SELECT i.*, u.prenom, u.nom, u.email, e.titre AS titre_evenement
             FROM inscription i
             LEFT JOIN utilisateur u ON i.idUtilisateur = u.idUtilisateur
             LEFT JOIN evenement e ON i.idEvenement = e.idEvenement
             WHERE i.idInscription = ?'
        );
        $stmt->execute([(int) $id]);

        return $stmt->fetch(PDO::FETCH_OBJ);
    }

    public static function findById($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT * FROM inscription WHERE idInscription = ?');
        $stmt->execute([(int) $id]);

        return $stmt->fetch(PDO::FETCH_ASSOC);
    }

    public static function getByEvent($idEvenement)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            'SELECT i.*, u.prenom, u.nom, u.email
             FROM inscription i
             LEFT JOIN utilisateur u ON i.idUtilisateur = u.idUtilisateur
             WHERE i.idEvenement = ?
             ORDER BY i.dateInscription DESC'
        );
        $stmt->execute([(int) $idEvenement]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function getByUser($idUtilisateur)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare(
            'SELECT i.*, e.titre, e.date, e.lieu, e.statut AS statut_evenement
             FROM inscription i
             LEFT JOIN evenement e ON i.idEvenement = e.idEvenement
             WHERE i.idUtilisateur = ?
             ORDER BY i.dateInscription DESC'
        );
        $stmt->execute([(int) $idUtilisateur]);

        return $stmt->fetchAll(PDO::FETCH_OBJ);
    }

    public static function updateById($id, $idUtilisateur, $idEvenement, $statut, $dateInscription = null)
    {
        $db = Database::getInstance()->getConnection();
        if ($dateInscription === null) {
            $stmt = $db->prepare(
                'UPDATE inscription SET idUtilisateur = ?, idEvenement = ?, statut = ? WHERE idInscription = ?'
            );
            return $stmt->execute([
                (int) $idUtilisateur,
                (int) $idEvenement,
                self::normalizeStatus($statut),
                (int) $id,
            ]);
        } else {
            $stmt = $db->prepare(
                'UPDATE inscription SET idUtilisateur = ?, idEvenement = ?, statut = ?, dateInscription = ? WHERE idInscription = ?'
            );
            return $stmt->execute([
                (int) $idUtilisateur,
                (int) $idEvenement,
                self::normalizeStatus($statut),
                $dateInscription,
                (int) $id,
            ]);
        }
    }

    public static function updateStatusById($id, $statut)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('UPDATE inscription SET statut = ? WHERE idInscription = ?');

        return $stmt->execute([
            self::normalizeStatus($statut),
            (int) $id,
        ]);
    }

    public static function delete($id)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('DELETE FROM inscription WHERE idInscription = ?');

        return $stmt->execute([(int) $id]);
    }

    public static function isRegistered($idUtilisateur, $idEvenement)
    {
        $db = Database::getInstance()->getConnection();
        $stmt = $db->prepare('SELECT COUNT(*) AS count FROM inscription WHERE idUtilisateur = ? AND idEvenement = ?');
        $stmt->execute([(int) $idUtilisateur, (int) $idEvenement]);

        $result = $stmt->fetch(PDO::FETCH_OBJ);
        return ((int) ($result->count ?? 0)) > 0;
    }
}
