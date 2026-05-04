<?php

class StoryModel {

    private $id = null;
    private $title = null;
    private $content = null;
    private $post_id = null;
    private $cta_label = null;
    private $media_image = null;
    private $status = 'published';
    private $starts_at = null;
    private $expires_at = null;

    public function __construct($title, $content, $post_id, $cta_label, $media_image, $status, $starts_at, $expires_at) {
        $this->title       = $title;
        $this->content     = $content;
        $this->post_id     = $post_id;
        $this->cta_label   = $cta_label;
        $this->media_image = $media_image;
        $this->status      = $status;
        $this->starts_at   = $starts_at;
        $this->expires_at  = $expires_at;
    }

    public function getId()         { return $this->id; }
    public function getTitle()      { return $this->title; }
    public function getContent()    { return $this->content; }
    public function getPostId()     { return $this->post_id; }
    public function getCtaLabel()   { return $this->cta_label; }
    public function getMediaImage() { return $this->media_image; }
    public function getStatus()     { return $this->status; }
    public function getStartsAt()   { return $this->starts_at; }
    public function getExpiresAt()  { return $this->expires_at; }

    public function setId($id) { $this->id = $id; }
}
?>
