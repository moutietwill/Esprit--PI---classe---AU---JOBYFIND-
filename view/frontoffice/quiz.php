<?php
require_once __DIR__ . "/../../config/Database.php";
require_once __DIR__ . "/../../model/quizz.php";
require_once __DIR__ . "/../../controller/quizzController.php";

$quizController = new QuizController();
$listQuizzes = $quizController->listQuiz()->fetchAll(PDO::FETCH_ASSOC);

// Filtrer seulement les quiz publiés
$publishedQuizzes = array_filter($listQuizzes, function($quiz) {
    return isset($quiz['statut']) && $quiz['statut'] === 'Publié';
});

// Compter les quiz par domaine
$domaineCounts = [];
foreach ($publishedQuizzes as $quiz) {
    $domaine = $quiz['domaine'];
    $domaineCounts[$domaine] = ($domaineCounts[$domaine] ?? 0) + 1;
}

// Fonction pour obtenir l'icône du domaine
function getDomaineIcon($domaine) {
    $icons = [
        'Marketing' => '📈',
        'Finance' => '💰',
        'Management' => '👔',
        'Tech & Dev' => '💻',
        'RH' => '👥',
        'Entrepreneuriat' => '🚀'
    ];
    return $icons[$domaine] ?? '📚';
}

// Fonction pour obtenir la couleur du domaine
function getDomaineColor($domaine) {
    $colors = [
        'Marketing' => 'bg-dark-blue',
        'Finance' => 'bg-teal',
        'Management' => 'bg-purple',
        'Tech & Dev' => 'bg-blue',
        'RH' => 'bg-green',
        'Entrepreneuriat' => 'bg-orange'
    ];
    return $colors[$domaine] ?? 'bg-gray';
}
?>

<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Jobyfind — Quiz</title>
    <link rel="stylesheet" href="style.css">
    <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
</head>
<body>

    <nav class="navbar">
        <div class="logo">Joby<span>find</span></div>
        <ul class="nav-links">
            <li><a href="#" class="active">Quiz</a></li>
            <li><a href="#">Formations</a></li>
            <li><a href="#">À propos</a></li>
            <li><a href="#">Contact</a></li>
        </ul>
        <div class="nav-auth">
            <button class="btn-outline">Connexion</button>
            <button class="btn-filled">S'inscrire</button>
        </div>
    </nav>

    <main class="container">
        <section class="filters">
            <button class="filter-btn active" data-filter="all">📁 Tout voir <span><?php echo count($publishedQuizzes); ?></span></button>
            <?php foreach ($domaineCounts as $domaine => $count): ?>
            <button class="filter-btn" data-filter="<?php echo htmlspecialchars(strtolower($domaine)); ?>">
                <?php echo getDomaineIcon($domaine); ?> <?php echo htmlspecialchars($domaine); ?> <span><?php echo $count; ?></span>
            </button>
            <?php endforeach; ?>
        </section>

        <div class="courses-grid">
            <?php foreach ($publishedQuizzes as $quiz): ?>
            <div class="course-card" data-domaine="<?php echo htmlspecialchars(strtolower($quiz['domaine'])); ?>">
                <div class="card-header <?php echo getDomaineColor($quiz['domaine']); ?>">
                    <span class="badge-cat"><?php echo getDomaineIcon($quiz['domaine']); ?> <?php echo htmlspecialchars($quiz['domaine']); ?></span>
                    <span class="badge-type online">🧠 Quiz interactif</span>
                    <div class="hover-info">
                        <p><i class="fa fa-clock"></i> Durée : 15 minutes</p>
                        <p><i class="fa fa-question-circle"></i> Questions à choix multiples</p>
                        <p><i class="fa fa-trophy"></i> Certification incluse</p>
                        <p><i class="fa fa-calendar"></i> Créé le <?php echo date('d/m/Y', strtotime($quiz['dateCreation'])); ?></p>
                        <button class="btn-enroll" onclick="startQuiz(<?php echo $quiz['id_quiz']; ?>)">Commencer le quiz →</button>
                    </div>
                </div>
                <div class="card-body">
                    <h3><?php echo htmlspecialchars($quiz['titre']); ?></h3>
                    <div class="instructor">
                        <div class="avatar" style="background: linear-gradient(135deg, #667eea 0%, #764ba2 100%);">
                            <?php echo strtoupper(substr($quiz['domaine'], 0, 2)); ?>
                        </div>
                        <span>Niveau <?php echo htmlspecialchars($quiz['niveau']); ?></span>
                    </div>
                    <div class="card-footer">
                        <span class="price">Gratuit</span>
                        <span class="sessions">
                            <i class="fa fa-question-circle"></i>
                            <?php
                            // Simuler un nombre de questions basé sur l'ID (vous pouvez ajuster selon vos besoins)
                            $questionsCount = rand(10, 20);
                            echo $questionsCount . ' questions';
                            ?>
                        </span>
                    </div>
                </div>
            </div>
            <?php endforeach; ?>
        </div>
    </main>

    <script>
        // Filtrage des quiz
        document.querySelectorAll('.filter-btn').forEach(btn => {
            btn.addEventListener('click', function() {
                // Retirer la classe active de tous les boutons
                document.querySelectorAll('.filter-btn').forEach(b => b.classList.remove('active'));
                // Ajouter la classe active au bouton cliqué
                this.classList.add('active');

                const filter = this.dataset.filter;

                // Filtrer les cartes
                document.querySelectorAll('.course-card').forEach(card => {
                    if (filter === 'all' || card.dataset.domaine === filter) {
                        card.style.display = 'block';
                    } else {
                        card.style.display = 'none';
                    }
                });
            });
        });

        // Fonction pour commencer un quiz
        function startQuiz(quizId) {
            // Redirection vers la page du quiz (à créer)
            window.location.href = `quiz-take.php?id=${quizId}`;
        }

        // Animation au survol des cartes
        document.querySelectorAll('.course-card').forEach(card => {
            card.addEventListener('mouseenter', function() {
                this.style.transform = 'translateY(-5px)';
                this.style.boxShadow = '0 10px 25px rgba(0,0,0,0.15)';
            });

            card.addEventListener('mouseleave', function() {
                this.style.transform = 'translateY(0)';
                this.style.boxShadow = '0 4px 15px rgba(0,0,0,0.1)';
            });
        });
    </script>

</body>
</html>