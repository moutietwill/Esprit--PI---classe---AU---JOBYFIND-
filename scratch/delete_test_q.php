<?php
try {
    $db = new PDO('mysql:host=localhost;dbname=jobyfind', 'root', '');
    $id_to_delete = 4;
    $db->exec("DELETE FROM reponse WHERE id_question = $id_to_delete"); 
    $db->exec("DELETE FROM question WHERE id_question = $id_to_delete");
    echo "Question ID $id_to_delete suppressed successfully.";
} catch (Exception $e) {
    echo $e->getMessage();
}
