<?php

require_once __DIR__ . '/../config/Database.php';
require_once __DIR__ . '/../models/StoryModel.php';

class StoryController {

    private $uploadDir = __DIR__ . '/../uploads/stories/';

    public function __construct() {
        $this->EnsureStorySchema();
    }

    private function EnsureStorySchema() {
        $db = Database::getInstance()->getConnection();
        try {
            $db->exec("CREATE TABLE IF NOT EXISTS `stories` (
                `id` int(11) NOT NULL AUTO_INCREMENT,
                `post_id` int(11) DEFAULT NULL,
                `title` varchar(180) NOT NULL,
                `content` text,
                `cta_label` varchar(80) DEFAULT 'Lire le blog',
                `media_image` varchar(500) DEFAULT NULL,
                `status` varchar(30) DEFAULT 'published',
                `views_count` int unsigned NOT NULL DEFAULT 0,
                `starts_at` datetime NOT NULL DEFAULT CURRENT_TIMESTAMP,
                `expires_at` datetime NOT NULL,
                `created_at` timestamp DEFAULT CURRENT_TIMESTAMP,
                `updated_at` timestamp DEFAULT CURRENT_TIMESTAMP ON UPDATE CURRENT_TIMESTAMP,
                PRIMARY KEY (`id`),
                KEY `idx_story_status_dates` (`status`, `starts_at`, `expires_at`),
                KEY `idx_story_post` (`post_id`),
                CONSTRAINT `stories_ibfk_1` FOREIGN KEY (`post_id`) REFERENCES `blog_posts` (`id`) ON DELETE SET NULL
            ) ENGINE=InnoDB DEFAULT CHARSET=utf8mb4 COLLATE=utf8mb4_unicode_ci;");
        } catch (Exception $e) {
            // The rest of the blog must keep working even if the migration is blocked.
        }
    }

    public function AfficherStories($status = null) {
        $sql = "SELECT s.*, p.titre AS post_title
                FROM stories s
                LEFT JOIN blog_posts p ON s.post_id = p.id";

        $params = [];
        if ($status) {
            $sql .= " WHERE s.status = :status";
            $params[':status'] = $status;
        }

        $sql .= " ORDER BY s.created_at DESC";

        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->execute($params);
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function RecupererStory($id) {
        $sql = "SELECT s.*, p.titre AS post_title
                FROM stories s
                LEFT JOIN blog_posts p ON s.post_id = p.id
                WHERE s.id = :id";
        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            return false;
        }
    }

    public function AjouterStory($story, $file = null) {
        $mediaImage = null;
        if ($file && !empty($file['name'])) {
            $mediaImage = $this->UploadImage($file);
        }

        $startsAt = $this->NormalizeDate($story->getStartsAt(), date('Y-m-d H:i:s'));
        $expiresAt = $this->NormalizeDate($story->getExpiresAt(), date('Y-m-d H:i:s', strtotime('+24 hours')));
        $this->ValidateDates($startsAt, $expiresAt);

        $sql = "INSERT INTO stories (post_id, title, content, cta_label, media_image, status, starts_at, expires_at, created_at, updated_at)
                VALUES (:post_id, :title, :content, :cta_label, :media_image, :status, :starts_at, :expires_at, NOW(), NOW())";
        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':post_id'     => $this->NullableInt($story->getPostId()),
                ':title'       => trim($story->getTitle()),
                ':content'     => trim($story->getContent()),
                ':cta_label'   => trim($story->getCtaLabel()) ?: 'Lire le blog',
                ':media_image' => $mediaImage,
                ':status'      => $this->NormalizeStatus($story->getStatus()),
                ':starts_at'   => $startsAt,
                ':expires_at'  => $expiresAt
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la creation de la story: ' . $e->getMessage());
        }
    }

    public function ModifierStory($story, $id, $file = null) {
        $oldStory = $this->RecupererStory($id);
        if (!$oldStory) {
            throw new Exception('Story introuvable.');
        }

        if ($file && !empty($file['name'])) {
            $mediaImage = $this->UploadImage($file);
            if (!empty($oldStory['media_image'])) {
                @unlink($this->uploadDir . $oldStory['media_image']);
            }
        } else {
            $mediaImage = $story->getMediaImage();
        }

        $startsAt = $this->NormalizeDate($story->getStartsAt(), $oldStory['starts_at']);
        $expiresAt = $this->NormalizeDate($story->getExpiresAt(), $oldStory['expires_at']);
        $this->ValidateDates($startsAt, $expiresAt);

        $sql = "UPDATE stories
                SET post_id = :post_id,
                    title = :title,
                    content = :content,
                    cta_label = :cta_label,
                    media_image = :media_image,
                    status = :status,
                    starts_at = :starts_at,
                    expires_at = :expires_at,
                    updated_at = NOW()
                WHERE id = :id";
        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':post_id'     => $this->NullableInt($story->getPostId()),
                ':title'       => trim($story->getTitle()),
                ':content'     => trim($story->getContent()),
                ':cta_label'   => trim($story->getCtaLabel()) ?: 'Lire le blog',
                ':media_image' => $mediaImage,
                ':status'      => $this->NormalizeStatus($story->getStatus()),
                ':starts_at'   => $startsAt,
                ':expires_at'  => $expiresAt,
                ':id'          => (int) $id
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la modification de la story: ' . $e->getMessage());
        }
    }

    public function SupprimerStory($id) {
        $story = $this->RecupererStory($id);
        $sql = "DELETE FROM stories WHERE id = :id";
        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => (int) $id]);
            if ($story && !empty($story['media_image'])) {
                @unlink($this->uploadDir . $story['media_image']);
            }
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression de la story: ' . $e->getMessage());
        }
    }

    public function GetActiveStories($limit = 8) {
        $limit = max(1, (int) $limit);
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT s.*, p.titre AS post_title
                FROM stories s
                LEFT JOIN blog_posts p ON s.post_id = p.id
                WHERE s.status = 'published'
                  AND s.starts_at <= :now
                  AND s.expires_at >= :now2
                ORDER BY s.created_at DESC
                LIMIT $limit";
        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':now', $now);
            $query->bindValue(':now2', $now);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function CountActiveStories() {
        $now = date('Y-m-d H:i:s');
        $sql = "SELECT COUNT(*) FROM stories
                WHERE status = 'published'
                  AND starts_at <= :now
                  AND expires_at >= :now2";
        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->bindValue(':now', $now);
            $query->bindValue(':now2', $now);
            $query->execute();
            return (int) $query->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function GetTotalStoryViews() {
        $sql = "SELECT COALESCE(SUM(views_count), 0) FROM stories";
        $db = Database::getInstance()->getConnection();
        try {
            return (int) $db->query($sql)->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function GetTopStories($limit = 5) {
        $limit = max(1, (int) $limit);
        $sql = "SELECT id, title, views_count
                FROM stories
                ORDER BY views_count DESC, created_at DESC
                LIMIT $limit";
        $db = Database::getInstance()->getConnection();
        try {
            return $db->query($sql)->fetchAll();
        } catch (Exception $e) {
            return [];
        }
    }

    public function IncrementStoryView($storyId) {
        $story = $this->RecupererStory($storyId);
        if (!$story || !$this->IsStoryActive($story)) {
            return 0;
        }

        $sql = "UPDATE stories SET views_count = views_count + 1 WHERE id = :id";
        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => (int) $storyId]);
            return $this->GetStoryViewCount($storyId);
        } catch (Exception $e) {
            return 0;
        }
    }

    public function GetStoryViewCount($storyId) {
        $sql = "SELECT COALESCE(views_count, 0) FROM stories WHERE id = :id";
        $db = Database::getInstance()->getConnection();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => (int) $storyId]);
            return (int) $query->fetchColumn();
        } catch (Exception $e) {
            return 0;
        }
    }

    public function IsStoryActive($story) {
        if (!$story || ($story['status'] ?? '') !== 'published') {
            return false;
        }

        $now = time();
        return strtotime($story['starts_at']) <= $now && strtotime($story['expires_at']) >= $now;
    }

    public function UploadImage($file) {
        if (!is_dir($this->uploadDir)) {
            mkdir($this->uploadDir, 0777, true);
        }

        $extensionsAutorisees = ['jpg', 'jpeg', 'png', 'gif', 'webp'];
        $extension = strtolower(pathinfo($file['name'], PATHINFO_EXTENSION));

        if (!in_array($extension, $extensionsAutorisees)) {
            throw new Exception('Extension non autorisee. Formats acceptes: jpg, png, gif, webp');
        }

        if ($file['size'] > 5 * 1024 * 1024) {
            throw new Exception('Image trop lourde. Maximum 5MB');
        }

        if (!getimagesize($file['tmp_name'])) {
            throw new Exception("Le fichier n'est pas une image valide");
        }

        $nouveauNom = uniqid('story_', true) . '.' . $extension;
        if (move_uploaded_file($file['tmp_name'], $this->uploadDir . $nouveauNom)) {
            return $nouveauNom;
        }

        throw new Exception("Echec du telechargement de l'image");
    }

    private function NormalizeDate($value, $fallback) {
        $value = trim((string) $value);
        if ($value === '') {
            $value = $fallback;
        }

        $timestamp = strtotime($value);
        if ($timestamp === false) {
            throw new Exception('Date de story invalide.');
        }

        return date('Y-m-d H:i:s', $timestamp);
    }

    private function ValidateDates($startsAt, $expiresAt) {
        if (strtotime($expiresAt) <= strtotime($startsAt)) {
            throw new Exception('La date de fin doit etre apres la date de debut.');
        }
    }

    private function NormalizeStatus($status) {
        return $status === 'draft' ? 'draft' : 'published';
    }

    private function NullableInt($value) {
        $value = (int) $value;
        return $value > 0 ? $value : null;
    }
}
?>
