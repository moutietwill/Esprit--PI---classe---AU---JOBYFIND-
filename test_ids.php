<?php
require "connexion.php";
$db = Config::GetConnexion();
$q = $db->query("SELECT id FROM posts LIMIT 1");
var_dump($q->fetchAll());
