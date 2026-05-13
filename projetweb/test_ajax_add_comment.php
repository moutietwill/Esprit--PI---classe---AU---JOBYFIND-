<?php
$_POST['post_id'] = 2;
$_POST['content'] = 'Test comment via CLI';
$_POST['user_name'] = 'TestUser';

// Simulate an HTTP POST request
$_SERVER['REQUEST_METHOD'] = 'POST';

// We override file_get_contents to return empty json, since the code falls back to $_POST
$json_input = '{}';

// Since we cannot mock file_get_contents easily for php://input, wait, the code does:
// $data = json_decode(file_get_contents('php://input'), true);
// $postId = isset($data['post_id']) ? (int)$data['post_id'] : (isset($_POST['post_id']) ? (int)$_POST['post_id'] : 0);

require 'ajax_add_comment.php';
