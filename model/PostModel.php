<?php

class PostModel {

    private $id = null;
    private $title = null;
    private $content = null;
    private $category_id = null;
    private $coverImage = null;
    private $status = 'published';

    function __construct($title, $content, $category_id, $coverImage, $status) {
        $this->title          = $title;
        $this->content        = $content;
        $this->category_id    = $category_id;
        $this->coverImage     = $coverImage;
        $this->status         = $status;
    }

    // Getters
    function getId()              { return $this->id; }
    function getTitle()           { return $this->title; }
    function getContent()         { return $this->content; }
    function getCategoryId()      { return $this->category_id; }
    function getCoverImage()      { return $this->coverImage; }
    function getStatus()          { return $this->status; }

    function setId($id) { $this->id = $id; }
}
?>