<!DOCTYPE html>
<html lang="fr">
<head>
  <meta charset="utf-8">
  <meta name="viewport" content="width=device-width, initial-scale=1">
  <title>Admin - Gestion des Formations</title>
  <link href="https://fonts.googleapis.com/css2?family=DM+Sans:wght@300;400;500;600&display=swap" rel="stylesheet">
  <link rel="stylesheet" href="https://cdnjs.cloudflare.com/ajax/libs/font-awesome/6.5.2/css/all.min.css">
  <link rel="stylesheet" href="https://cdn.jsdelivr.net/npm/flatpickr/dist/flatpickr.min.css">
  <style>
    *, *::before, *::after { box-sizing: border-box; margin: 0; padding: 0; }
    :root {
      --blue: #2d79ff; --navy: #0b1f4b; --sidebar-w: 240px; --header-h: 60px;
      --bg: #f0f2f8; --surface: #ffffff; --border: #e2e8f0; --text: #374151;
      --muted: #9ca3af; --danger: #ef4444; --success: #22c55e; --radius: 10px;
    }
    body { font-family: "DM Sans", sans-serif; background: var(--bg); color: var(--text); display: flex; min-height: 100vh; font-size: 14px; }
    .sidebar { width: var(--sidebar-w); background: var(--navy); min-height: 100vh; position: fixed; top: 0; left: 0; display: flex; flex-direction: column; z-index: 100; }
    .sidebar-logo { display: flex; align-items: center; gap: 10px; padding: 20px; border-bottom: 1px solid rgba(255,255,255,.08); }
    .sidebar-logo-icon { width: 34px; height: 34px; background: var(--blue); border-radius: 8px; display: flex; align-items: center; justify-content: center; color: #fff; font-size: 14px; font-weight: 600; }
    .sidebar-logo-text { color: #fff; font-size: 17px; font-weight: 600; }
    .sidebar-section { padding: 18px 12px; }
    .sidebar-link { display: flex; align-items: center; gap: 10px; padding: 10px 12px; border-radius: 8px; text-decoration: none; color: rgba(255,255,255,.55); transition: all .15s; }
    .sidebar-link:hover { background: rgba(255,255,255,.07); color: rgba(255,255,255,.85); }
    .sidebar-link.active { background: var(--blue); color: #fff; }
    .main { margin-left: var(--sidebar-w); flex: 1; display: flex; flex-direction: column; }
    .header { height: var(--header-h); background: var(--surface); border-bottom: 1px solid var(--border); display: flex; align-items: center; padding: 0 28px; }
    .header-breadcrumb { display: flex; align-items: center; gap: 8px; font-size: 13px; color: var(--muted); }
    .header-breadcrumb .current { color: var(--navy); font-weight: 600; }
    .content { padding: 28px; flex: 1; }
    .table-card { background: var(--surface); border-radius: var(--radius); border: 1px solid var(--border); overflow: hidden; }
    .table-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--border); }
    .btn-primary { background: var(--blue); color: #fff; border: none; padding: 10px 16px; border-radius: 8px; cursor: pointer; font-weight: 500; font-size: 13px; }
    table { width: 100%; border-collapse: collapse; }
    th { background: var(--bg); padding: 12px 20px; text-align: left; font-size: 12px; font-weight: 600; color: var(--muted); text-transform: uppercase; }
    td { padding: 14px 20px; border-top: 1px solid var(--border); }
    .action-btns { display: flex; gap: 8px; }
    .action-btn { width: 32px; height: 32px; border-radius: 6px; border: 1px solid var(--border); background: transparent; cursor: pointer; display: flex; align-items: center; justify-content: center; color: var(--muted); }
    .action-btn:hover { border-color: var(--blue); color: var(--blue); }
    .modal-overlay { display: none; position: fixed; top: 0; left: 0; width: 100%; height: 100%; background: rgba(0,0,0,.5); z-index: 1000; align-items: center; justify-content: center; }
    .modal-overlay.open { display: flex; }
    .modal { background: var(--surface); border-radius: var(--radius); width: 90%; max-width: 600px; max-height: 90vh; overflow-y: auto; }
    .modal-header { display: flex; justify-content: space-between; align-items: center; padding: 20px; border-bottom: 1px solid var(--border); }
    .modal-body { padding: 20px; }
    .form-group { margin-bottom: 16px; }
    .form-label { display: block; font-size: 13px; font-weight: 500; color: var(--navy); margin-bottom: 6px; }
    .form-input { width: 100%; padding: 10px 12px; border: 1px solid var(--border); border-radius: 6px; font-size: 13px; outline: none; }
    .modal-footer { display: flex; gap: 10px; justify-content: flex-end; padding: 20px; border-top: 1px solid var(--border); }
    .btn-cancel { background: var(--bg); color: var(--text); border: 1px solid var(--border); padding: 10px 16px; border-radius: 6px; cursor: pointer; }
  </style>
</head>
<body>
  <aside class="sidebar">
    <div class="sidebar-logo">
      <div class="sidebar-logo-icon">JF</div>
      <div class="sidebar-logo-text">Jobyfind</div>
    </div>
    <div class="sidebar-section">
      <a href="<?= $url('/profile') ?>" class="sidebar-link"><i class="fa fa-users"></i> Utilisateurs</a>
      <a href="<?= $url('/admin/events') ?>" class="sidebar-link"><i class="fa fa-calendar"></i> Événements</a>
      <a href="<?= $url('/admin/formations') ?>" class="sidebar-link active"><i class="fa fa-graduation-cap"></i> Formations</a>
    </div>
  </aside>

  <div class="main">
    <header class="header">
      <div class="header-breadcrumb">
        <span>Admin</span> <span>›</span> <span class="current">Formations</span>
      </div>
    </header>

    <div class="content">
      <div class="table-card">
        <div class="table-header">
          <h3 style="font-size: 16px; font-weight: 600; color: var(--navy);">Liste des Formations</h3>
          <button class="btn-primary" onclick="openAddModal()"><i class="fa fa-plus"></i> Ajouter</button>
        </div>
        <table>
          <thead>
            <tr>
              <th>Titre</th>
              <th>Catégorie</th>
              <th>Prix</th>
              <th>Date</th>
              <th>Durée</th>
              <th>Actions</th>
            </tr>
          </thead>
          <tbody>
            <?php foreach ($formations as $f): ?>
            <tr>
              <td style="font-weight: 500"><?= htmlspecialchars($f->getTitre()) ?></td>
              <td><?= htmlspecialchars($f->getCategorie()) ?></td>
              <td><?= number_format($f->getPrix(), 2) ?> DT</td>
              <td><?= date('d/m/Y', strtotime($f->getDate())) ?></td>
              <td><?= htmlspecialchars($f->getDuree()) ?></td>
              <td>
                <div class="action-btns">
                  <button class="action-btn" onclick='openEditModal(<?= json_encode($f->toArray()) ?>)'><i class="fa fa-pen"></i></button>
                  <form action="<?= $url('/admin/deleteFormation/' . $f->getId()) ?>" method="POST" onsubmit="return confirm('Supprimer cette formation ?')">
                    <button type="submit" class="action-btn text-danger"><i class="fa fa-trash"></i></button>
                  </form>
                </div>
              </td>
            </tr>
            <?php endforeach; ?>
          </tbody>
        </table>
      </div>
    </div>
  </div>

  <div class="modal-overlay" id="modal">
    <div class="modal">
      <div class="modal-header"><div class="modal-title" id="modal-title">Ajouter une formation</div><button onclick="closeModal()" style="background:none; border:none; cursor:pointer;"><i class="fa fa-times"></i></button></div>
      <form id="form" method="POST">
        <div class="modal-body">
          <div class="form-group"><label class="form-label">Titre</label><input type="text" name="titre" class="form-input" required></div>
          <div class="form-group"><label class="form-label">Catégorie</label><input type="text" name="categorie" class="form-input" required></div>
          <div class="form-group"><label class="form-label">Prix (DT)</label><input type="number" step="0.01" name="prix" class="form-input" required></div>
          <div class="form-group"><label class="form-label">Date de début</label><input type="text" name="date" class="form-input" id="date-picker" required></div>
          <div class="form-group"><label class="form-label">Durée</label><input type="text" name="duree" class="form-input" placeholder="ex: 3 mois" required></div>
          <div class="form-group"><label class="form-label">Description</label><textarea name="description" class="form-input" rows="4" required></textarea></div>
        </div>
        <div class="modal-footer">
          <button type="button" class="btn-cancel" onclick="closeModal()">Annuler</button>
          <button type="submit" class="btn-primary">Enregistrer</button>
        </div>
      </form>
    </div>
  </div>

  <script src="https://cdn.jsdelivr.net/npm/flatpickr"></script>
  <script>
    const modal = document.getElementById('modal');
    const form = document.getElementById('form');
    const modalTitle = document.getElementById('modal-title');
    
    flatpickr("#date-picker", { dateFormat: "Y-m-d" });

    function openAddModal() {
      modalTitle.textContent = "Ajouter une formation";
      form.action = "<?= $url('/admin/storeFormation') ?>";
      form.reset();
      modal.classList.add('open');
    }

    function openEditModal(data) {
      modalTitle.textContent = "Modifier la formation";
      form.action = "<?= $url('/admin/updateFormation/') ?>" + data.id;
      form.querySelector('[name="titre"]').value = data.titre;
      form.querySelector('[name="categorie"]').value = data.categorie;
      form.querySelector('[name="prix"]').value = data.prix;
      form.querySelector('[name="date"]').value = data.date;
      form.querySelector('[name="duree"]').value = data.duree;
      form.querySelector('[name="description"]').value = data.description;
      modal.classList.add('open');
    }

    function closeModal() { modal.classList.remove('open'); }
  </script>
</body>
</html>
