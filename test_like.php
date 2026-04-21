<?php
require "connexion.php";
require "controller/BlogController.php";

try {
    $c = new BlogController();
    $liked = $c->ToggleLike(2, 1);
    var_dump($liked);
    $count = $c->GetLikesCount(2);
    var_dump($count);
} catch (Exception $e) {
    echo "Exception: " . $e->getMessage() . "\n";
}
