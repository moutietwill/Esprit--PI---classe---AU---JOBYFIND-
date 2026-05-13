<?php

require_once __DIR__ . '/../connexion.php';
require_once __DIR__ . '/../model/CommentModel.php';

class CommentController {

    // ═══════════════════════════════════════════════════════════
    // ─────────────── LIRE / AFFICHER ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Récupère tous les commentaires avec le titre du post
     */
    function getComments() {
        $sql = "SELECT c.*, p.title AS post_title 
                FROM comments c 
                LEFT JOIN posts p ON c.post_id = p.id 
                ORDER BY c.created_at DESC";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            return $query->fetchAll();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Récupère un commentaire par ID
     */
    function getComment($id) {
        $sql = "SELECT c.*, p.title AS post_title 
                FROM comments c 
                LEFT JOIN posts p ON c.post_id = p.id 
                WHERE c.id = :id";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return $query->fetch();
        } catch (Exception $e) {
            throw new Exception('Erreur: ' . $e->getMessage());
        }
    }

    /**
     * Compte le nombre total de commentaires
     */
    function countComments() {
        $sql = "SELECT COUNT(*) AS total FROM comments";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute();
            $result = $query->fetch();
            return (int) $result['total'];
        } catch (Exception $e) {
            return 0;
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── CRÉER ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Ajoute un nouveau commentaire
     */
    function addComment($comment) {
        $sql = "INSERT INTO comments (post_id, user_id, user_name, content, created_at) 
                VALUES (:post_id, :user_id, :user_name, :content, NOW())";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':post_id'   => $comment->getPostId(),
                ':user_id'   => $comment->getUserId(),
                ':user_name' => $comment->getUserName(),
                ':content'   => $comment->getContent(),
            ]);
            return $db->lastInsertId();
        } catch (Exception $e) {
            throw new Exception('Erreur lors de l\'ajout: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── METTRE À JOUR ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Met à jour un commentaire existant
     */
    function updateComment($id, $content, $userName) {
        $sql = "UPDATE comments SET content = :content, user_name = :user_name WHERE id = :id";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([
                ':content'   => $content,
                ':user_name' => $userName,
                ':id'        => $id,
            ]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la modification: ' . $e->getMessage());
        }
    }

    // ═══════════════════════════════════════════════════════════
    // ─────────────── SUPPRIMER ───────────────
    // ═══════════════════════════════════════════════════════════

    /**
     * Supprime un commentaire
     */
    function deleteComment($id) {
        $sql = "DELETE FROM comments WHERE id = :id";
        $db = Config::GetConnexion();
        try {
            $query = $db->prepare($sql);
            $query->execute([':id' => $id]);
            return true;
        } catch (Exception $e) {
            throw new Exception('Erreur lors de la suppression: ' . $e->getMessage());
        }
    }
}
?>
