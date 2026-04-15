<?php

class Database {
    private static $pdo = null;

    public static function getConnection() {
        if (!isset(self::$pdo)) {
            // Configuration for XAMPP (default credentials)
            $servername = "localhost";
            $username = "root";
            $password = "";
            $dbname = "jobyfind";

            try {
                // Instanciation de PDO selon les consignes
                self::$pdo = new PDO("mysql:host=$servername;dbname=$dbname;charset=utf8",
                    $username,
                    $password,
                    [
                        PDO::ATTR_ERRMODE => PDO::ERRMODE_EXCEPTION,
                        PDO::ATTR_DEFAULT_FETCH_MODE => PDO::FETCH_ASSOC
                    ]
                );
            } catch (PDOException $e) {
                // Die en cas d'erreur de connexion
                die('Erreur de connexion : ' . $e->getMessage());
            }
        }
        return self::$pdo;
    }
}
?>
