<?php
include '../controller/formationC.php';

$formationC = new formationC();
$listeFormations = $formationC->listeFormation();
?>
<!DOCTYPE html>
<html lang="fr">
<head>
    <meta charset="UTF-8">
    <meta name="viewport" content="width=device-width, initial-scale=1.0">
    <title>Liste des Formations</title>
    <style>
        body {
            font-family: 'Segoe UI', Tahoma, Geneva, Verdana, sans-serif;
            background-color: #f4f6f9;
            margin: 0;
            padding: 40px 20px;
            color: #333;
        }
        h1 {
            text-align: center;
            color: #2c3e50;
            margin-bottom: 30px;
            font-size: 2rem;
            font-weight: 700;
        }
        .container {
            max-width: 1100px;
            margin: 0 auto;
            background: #ffffff;
            padding: 30px;
            border-radius: 12px;
            box-shadow: 0 8px 20px rgba(0,0,0,0.06);
        }
        .top-bar {
            display: flex;
            justify-content: space-between;
            align-items: center;
            margin-bottom: 25px;
        }
        .btn-add {
            background-color: #27ae60;
            color: white;
            padding: 12px 24px;
            text-decoration: none;
            border-radius: 6px;
            font-weight: 600;
            transition: all 0.3s ease;
            box-shadow: 0 4px 6px rgba(39, 174, 96, 0.2);
        }
        .btn-add:hover {
            background-color: #219150;
            transform: translateY(-2px);
            box-shadow: 0 6px 12px rgba(39, 174, 96, 0.3);
        }
        table {
            width: 100%;
            border-collapse: separate;
            border-spacing: 0;
            overflow: hidden;
            border-radius: 8px;
            border: 1px solid #e1e5eb;
        }
        th, td {
            padding: 16px 20px;
            text-align: left;
            border-bottom: 1px solid #e1e5eb;
        }
        th {
            background-color: #34495e;
            color: white;
            text-transform: uppercase;
            font-size: 13px;
            font-weight: 600;
            letter-spacing: 0.5px;
        }
        tr:last-child td {
            border-bottom: none;
        }
        tbody tr {
            transition: background-color 0.2s ease;
        }
        tbody tr:hover {
            background-color: #f8fafc;
        }
        .action-btns {
            display: flex;
            gap: 10px;
        }
        .action-btns a {
            padding: 8px 16px;
            text-decoration: none;
            border-radius: 5px;
            color: white;
            font-size: 13px;
            font-weight: 600;
            transition: all 0.2s ease;
        }
        .btn-edit {
            background-color: #3498db;
            box-shadow: 0 2px 4px rgba(52, 152, 219, 0.2);
        }
        .btn-edit:hover {
            background-color: #2980b9;
            transform: translateY(-1px);
        }
        .btn-delete {
            background-color: #e74c3c;
            box-shadow: 0 2px 4px rgba(231, 76, 60, 0.2);
        }
        .btn-delete:hover {
            background-color: #c0392b;
            transform: translateY(-1px);
        }
    </style>
</head>
<body>

<div class="container">
    <div class="top-bar">
        <h1>Liste des Formations</h1>
        <a href="ajouterFormation.php" class="btn-add">+ Ajouter une formation</a>
    </div>

    <table>
        <thead>
            <tr>
                <th>ID</th>
                <th>Titre</th>
                <th>Prix</th>
                <th>Date</th>
                <th>Durée</th>
                <th>Description</th>
                <th>Catégorie</th>
                <th>Actions</th>
            </tr>
        </thead>
        <tbody>
            <?php foreach ($listeFormations as $formation) { ?>
                <tr>
                    <td><?= htmlspecialchars($formation['id']) ?></td>
                    <td><strong><?= htmlspecialchars($formation['titre']) ?></strong></td>
                    <td><?= htmlspecialchars($formation['prix']) ?> €</td>
                    <td><?= htmlspecialchars($formation['date']) ?></td>
                    <td><?= htmlspecialchars($formation['duree']) ?></td>
                    <td><?= htmlspecialchars($formation['description']) ?></td>
                    <td><span style="background: #ecf0f1; padding: 4px 8px; border-radius: 12px; font-size: 12px; color: #7f8c8d; font-weight: bold;"><?= htmlspecialchars($formation['categorie']) ?></span></td>
                    <td>
                        <div class="action-btns">
                            <a href="modifierFormation.php?id=<?= $formation['id'] ?>" class="btn-edit">Modifier</a>
                            <a href="supprimerFormation.php?id=<?= $formation['id'] ?>" class="btn-delete" onclick="return confirm('Vraiment supprimer cette formation ?');">Supprimer</a>
                        </div>
                    </td>
                </tr>
            <?php } ?>
        </tbody>
    </table>
</div>

</body>
</html>
