<?php
$data = array('titre' => 'Formation React', 'categorie' => 'Développement Web');
$ch = curl_init('http://localhost/amen/controller/api_gemini.php');
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode($data));
curl_setopt($ch, CURLOPT_HTTPHEADER, array('Content-Type: application/json'));
$response = curl_exec($ch);
echo $response;
?>
