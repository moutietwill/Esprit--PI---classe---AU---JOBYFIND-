<?php
$ch = curl_init("http://localhost/projetweb/projetweb/ajax_toggle_like.php");
curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);
curl_setopt($ch, CURLOPT_POST, true);
curl_setopt($ch, CURLOPT_POSTFIELDS, json_encode(["post_id" => 2]));
curl_setopt($ch, CURLOPT_HTTPHEADER, ["Content-Type: application/json"]);
$response = curl_exec($ch);
var_dump($response);
$error = curl_error($ch);
var_dump($error);
?>
