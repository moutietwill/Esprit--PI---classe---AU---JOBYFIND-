<?php
require_once __DIR__ . '/../models/Formation.php';

class FormationController extends Controller {

    public function index() {
        $formations = $this->fetchAllFormations();
        $this->render('formation/index', ['formations' => $formations]);
    }

    public function show($id) {
        $formation = $this->fetchFormationById($id);
        if (!$formation) {
            $this->redirect('/formation');
            return;
        }
        $this->render('formation/show', ['formation' => $formation]);
    }
}
