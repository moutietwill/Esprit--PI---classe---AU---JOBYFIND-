<?php
header('Content-Type: application/json; charset=utf-8');

include_once '../model/avis.php';
include_once '../controller/avisC.php';

// Only accept POST
if ($_SERVER['REQUEST_METHOD'] !== 'POST') {
    http_response_code(405);
    echo json_encode(['success' => false, 'error' => 'Method Not Allowed']);
    exit;
}

$input = json_decode(file_get_contents('php://input'), true);
$id_formation = isset($input['id_formation']) ? (int)$input['id_formation'] : 0;
$note = isset($input['note']) ? (int)$input['note'] : 0;
$commentaire = isset($input['commentaire']) ? trim($input['commentaire']) : null;

if ($id_formation <= 0 || $note < 1 || $note > 5) {
    http_response_code(400);
    echo json_encode(['success' => false, 'error' => 'Invalid parameters']);
    exit;
}

$avisC = new avisC();
$avis = new avis($id_formation, $note, $commentaire);

if ($avisC->addAvis($avis)) {
    // Return the new average rating
    $stats = $avisC->getAverageRating($id_formation);
    echo json_encode(['success' => true, 'new_average' => $stats['moyenne'], 'new_count' => $stats['count']]);
} else {
    http_response_code(500);
    echo json_encode(['success' => false, 'error' => 'Database error']);
}
?>
