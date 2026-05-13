<?php
/**
 * Post Model
 * Représente un article de blog
 */
class Post {
    private $id;
    private $title;
    private $content;
    private $category_id;
    private $category;
    private $cover_image;
    private $excerpt;
    private $status;
    private $views;
    private $likes;
    private $author_id;
    private $created_at;
    private $updated_at;

    public function __construct($data = []) {
        $this->id = $data['id'] ?? null;
        $this->title = $data['title'] ?? '';
        $this->content = $data['content'] ?? '';
        $this->category_id = $data['category_id'] ?? null;
        $this->category = $data['category'] ?? 'Général';
        $this->cover_image = $data['cover_image'] ?? 'public/assets/images/blog/default.jpg';
        $this->excerpt = $data['excerpt'] ?? '';
        $this->status = $data['status'] ?? 'draft';
        $this->views = $data['views'] ?? 0;
        $this->likes = $data['likes'] ?? 0;
        $this->author_id = $data['author_id'] ?? null;
        $this->created_at = $data['created_at'] ?? date('Y-m-d H:i:s');
        $this->updated_at = $data['updated_at'] ?? date('Y-m-d H:i:s');
    }

    // ═══════════════════════════════════════════════════════════
    // GETTERS
    // ═══════════════════════════════════════════════════════════
    public function getId() { return $this->id; }
    public function getTitle() { return $this->title; }
    public function getContent() { return $this->content; }
    public function getCategoryId() { return $this->category_id; }
    public function getCategory() { return $this->category; }
    public function getCoverImage() { return $this->cover_image; }
    public function getExcerpt() { return $this->excerpt; }
    public function getStatus() { return $this->status; }
    public function getViews() { return $this->views; }
    public function getLikes() { return $this->likes; }
    public function getAuthorId() { return $this->author_id; }
    public function getCreatedAt() { return $this->created_at; }
    public function getUpdatedAt() { return $this->updated_at; }

    // ═══════════════════════════════════════════════════════════
    // SETTERS
    // ═══════════════════════════════════════════════════════════
    public function setTitle($title) { $this->title = $title; }
    public function setContent($content) { $this->content = $content; }
    public function setCategoryId($id) { $this->category_id = $id; }
    public function setCategory($category) { $this->category = $category; }
    public function setCoverImage($image) { $this->cover_image = $image; }
    public function setExcerpt($excerpt) { $this->excerpt = $excerpt; }
    public function setStatus($status) { $this->status = $status; }
    public function setViews($views) { $this->views = $views; }
    public function setLikes($likes) { $this->likes = $likes; }
    public function setAuthorId($id) { $this->author_id = $id; }

    // ═══════════════════════════════════════════════════════════
    // UTILITIES
    // ═══════════════════════════════════════════════════════════
    public function toArray() {
        return [
            'id' => $this->id,
            'title' => $this->title,
            'content' => $this->content,
            'category_id' => $this->category_id,
            'category' => $this->category,
            'cover_image' => $this->cover_image,
            'excerpt' => $this->excerpt,
            'status' => $this->status,
            'views' => $this->views,
            'likes' => $this->likes,
            'author_id' => $this->author_id,
            'created_at' => $this->created_at,
            'updated_at' => $this->updated_at,
        ];
    }

    public function isPublished() {
        return $this->status === 'published';
    }

    public function getFormattedDate() {
        return date('d/m/Y H:i', strtotime($this->created_at));
    }

    public function getShortContent($length = 150) {
        return substr(strip_tags($this->content), 0, $length) . '...';
    }
}
