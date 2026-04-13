<?php require 'c:/xampp/htdocs/projetweb/projetweb/connexion.php'; echo json_encode(Config::GetConnexion()->query('SHOW CREATE TABLE posts')->fetchAll());
