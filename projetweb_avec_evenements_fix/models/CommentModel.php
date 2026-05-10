<?php

class CommentModel {

    private $id = null;
    private $post_id = null;
    private $user_id = null;
    private $user_name = null;
    private $content = null;
    private $created_at = null;

    function __construct($post_id, $user_name, $content, $user_id = 0) {
        $this->post_id   = $post_id;
        $this->user_name = $user_name;
        $this->content   = $content;
        $this->user_id   = $user_id;
    }

    // Getters
    function getId()        { return $this->id; }
    function getPostId()    { return $this->post_id; }
    function getUserId()    { return $this->user_id; }
    function getUserName()  { return $this->user_name; }
    function getContent()   { return $this->content; }
    function getCreatedAt() { return $this->created_at; }

    // Setters
    function setId($id)               { $this->id = $id; }
    function setPostId($post_id)      { $this->post_id = $post_id; }
    function setUserName($user_name)  { $this->user_name = $user_name; }
    function setContent($content)     { $this->content = $content; }
}
?>
