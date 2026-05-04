<?php
/**
 * ════════════════════════════════════════════════════════════
 * JOBYFIND AI-CORE V2 — SYSTÈME MULTI-VISUEL & ANALYTIQUE
 * ════════════════════════════════════════════════════════════
 * Génération de dossiers complets : Multi-images + Analyses stratégiques.
 */

header('Content-Type: application/json; charset=utf-8');
error_reporting(0);
require_once __DIR__ . '/connexion.php';

$theme      = trim($_POST['theme']      ?? '');
$categoryId = (int)($_POST['category_id'] ?? 0);
$status     = 'published';
$year       = "2026-2030";

if (mb_strlen($theme) < 3) {
    echo json_encode(['success' => false, 'message' => 'Sujet invalide.']);
    exit;
}

// ─────────────────────────────────────────────────────────────
// MOTEUR DE GÉNÉRATION DE TEXTE AVANCÉ
// ─────────────────────────────────────────────────────────────

// Déterminer le pilier contextuel
$pillar = 'Économie Digitale';
if (stripos($theme, 'entrepre') !== false || stripos($theme, 'startup') !== false) $pillar = 'Entrepreneuriat';
if (stripos($theme, 'travail') !== false || stripos($theme, 'work') !== false) $pillar = 'Futur du Travail';

// Nettoyage pour les mots-clés images
$cleanTheme = urlencode(preg_replace('/(comment|pourquoi|les|des|mes|sur|avec|dans|pour)/i', '', $theme));

// 1. Génération du Titre
$titles = [
    "Dossier Stratégique : L'Impact Réel de {theme} sur {pillar}",
    "Anticiper {theme} : Analyse de Rupture et Visions {year}",
    "Pourquoi {theme} est le Levier Majeur de l'Évolution de {pillar}",
    "Au Cœur de la Mutation : {theme} Décrypté par l'IA"
];
$finalTitle = str_replace(['{theme}', '{pillar}', '{year}'], [$theme, $pillar, $year], $titles[array_rand($titles)]);

// ─────────────────────────────────────────────────────────────
// LOGIQUE DE GÉNÉRATION DES IMAGES UNIQUE (V3)
// ─────────────────────────────────────────────────────────────

// On extrait le premier mot significatif du thème pour la recherche d'image
$themeWords = explode(' ', $theme);
$mainKeyword = "technology"; // Default
foreach($themeWords as $w) {
    if (strlen($w) > 4) { $mainKeyword = $w; break; }
}

// On génère des signatures uniques pour éviter le cache (sig=timestamp)
$time = time();
$img1 = "https://loremflickr.com/1200/800/" . urlencode($mainKeyword) . ",tech/all?sig=" . ($time + 1);
$img2 = "https://loremflickr.com/1200/800/" . urlencode($mainKeyword) . ",business/all?sig=" . ($time + 2);
$img3 = "https://loremflickr.com/1200/800/" . urlencode($mainKeyword) . ",minimal/all?sig=" . ($time + 3);

// 3. Construction du contenu enrichi
$intro = "> **ANALYSE IA JOBYFIND** : Cette étude explore les dimensions critiques de **{theme}** dans le contexte actuel de **{pillar}**.\n\n";
$intro .= "L'émergence de **{theme}** n'est pas un événement isolé, c'est une vague de fond qui redéfinit les structures mêmes de notre société. Nous ne sommes plus dans l'ajustement, mais dans la reconfiguration totale.";

$section1 = "### 1. La Dimension Structurelle\n\nL'analyse de **{theme}** révèle une accélération des cycles de valeur. Ce que nous percevions comme des barrières il y a deux ans sont devenus des opportunités de croissance fluide. L'enjeu n'est plus la possession de la ressource, mais la maîtrise du flux.\n\n" . 
            "![Illustration Conceptuelle]($img2)\n" .
            "*Fig 1. Représentation visuelle des flux de valeur liés à {theme}.*";

$section2 = "### 2. Levier de Transformation : $pillar\n\nDans le domaine spécifique de **$pillar**, l'influence de **{theme}** se manifeste par une hybridation des compétences. On ne demande plus à un expert de maîtriser un outil, mais de comprendre l'écosystème global.\n\n" .
            "**Points Clés :**\n" .
            "- **Optimisation Cognitive** : Utilisation de l'IA pour décupler l'impact de {theme}.\n" .
            "- **Agilité Systémique** : Capacité à pivoter en temps réel.\n" .
            "- **Résilience Digitale** : Sécurisation des actifs et des process.";

$section3 = "### 3. Perspectives Horizon $year\n\nÀ l'horizon $year, nous prédisons que **{theme}** sera devenu le standard invisible. Les entreprises qui auront réussi leur transition ne seront plus celles qui utilisent l'outil, mais celles qui ont intégré sa philosophie dans leur ADN.\n\n" .
            "![Visions du Futur]($img3)\n" .
            "*Fig 2. Projection prospective de l'évolution de {theme}.*";

$conclusion = "## 🏁 Synthèse Stratégique\n\nEn conclusion, **{theme}** représente bien plus qu'une simple étape technique. C'est le catalyseur d'une nouvelle ère pour **$pillar**. L'avenir appartient à ceux qui sauront conjuguer cette puissance technologique avec une vision humaine et éthique.\n\n" .
              "--- \n *Dossier réalisé par JobyFind AI Core V2 - Intelligence Artificielle Avancée*";

$fullContent = str_replace('{theme}', $theme, $intro . "\n\n" . $section1 . "\n\n" . $section2 . "\n\n" . $section3 . "\n\n" . $conclusion);

// ─────────────────────────────────────────────────────────────
// PUBLICATION
// ─────────────────────────────────────────────────────────────
try {
    $db = Config::GetConnexion();
    $sql = "INSERT INTO posts (title, content, category_id, category, status, cover_image, created_at, updated_at)
            VALUES (:title, :content, :category_id, :category, :status, :cover_image, NOW(), NOW())";
    $stmt = $db->prepare($sql);
    $stmt->execute([
        ':title'       => $finalTitle,
        ':content'     => $fullContent,
        ':category_id' => $categoryId > 0 ? $categoryId : null,
        ':category'    => $pillar,
        ':status'      => $status,
        ':cover_image' => $img1
    ]);
    
    echo json_encode([
        'success' => true,
        'id'      => $db->lastInsertId(),
        'title'   => $finalTitle,
        'image'   => $img1,
        'message' => 'Dossier Multi-Visuel JobyFind V2 publié !'
    ]);
} catch (Exception $e) {
    echo json_encode(['success' => false, 'message' => $e->getMessage()]);
}
?>
