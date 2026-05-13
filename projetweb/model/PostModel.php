<?php

class PostModel {

    private $id = null;
    private $title = null;
    private $excerpt = null;
    private $content = null;
    private $category_id = null;
    private $instructor = null;
    private $price = 0;
    private $duration_hours = 0;
    private $coverImage = null;
    private $status = 'published';

    function __construct($title, $content = '', $category_id = null, $coverImage = null, $status = 'published', $excerpt = '', $instructor = '', $price = 0, $duration_hours = 0) {
        $this->title          = $title;
        $this->content        = $content;
        $this->category_id    = $category_id;
        $this->coverImage     = $coverImage;
        $this->status         = $status;
        $this->excerpt        = $excerpt;
        $this->instructor     = $instructor;
        $this->price          = $price;
        $this->duration_hours = $duration_hours;
    }

    // Getters
    function getId()              { return $this->id; }
    function getTitle()           { return $this->title; }
    function getExcerpt()         { return $this->excerpt; }
    function getContent()         { return $this->content; }
    function getCategoryId()      { return $this->category_id; }
    function getInstructor()      { return $this->instructor; }
    function getPrice()           { return $this->price; }
    function getDurationHours()   { return $this->duration_hours; }
    function getCoverImage()      { return $this->coverImage; }
    function getStatus()          { return $this->status; }

    function setId($id) { $this->id = $id; }
}
?>