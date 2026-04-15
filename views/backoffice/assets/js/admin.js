
    const COLORS = [
      {bg:'#dbeafe',color:'#1d4ed8'},{bg:'#dcfce7',color:'#15803d'},
      {bg:'#ede9fe',color:'#6d28d9'},{bg:'#fef3c7',color:'#92400e'},
      {bg:'#fee2e2',color:'#b91c1c'},{bg:'#f0fdf4',color:'#166534'}
    ];

    function initials(name) {
      return name.split(' ').map(w=>w[0]).join('').slice(0,2).toUpperCase();
    }

    const RAW_USERS = [
      {id:1,prenom:'Amine',nom:'Trabelsi',email:'amine.t@gmail.com',role:'Entrepreneur',status:'Actif',date:'12 Jan 2025',last:'Aujourd\'hui'},
      {id:2,prenom:'Sarra',nom:'Boughanmi',email:'sarra.b@outlook.com',role:'Mentor',status:'Actif',date:'3 Fév 2025',last:'Hier'},
      {id:3,prenom:'Karim',nom:'Mansouri',email:'k.mansouri@entreprise.tn',role:'Entreprise',status:'En attente',date:'20 Fév 2025',last:'Il y a 5 jours'},
      {id:4,prenom:'Lina',nom:'Hamdi',email:'lina.h@gmail.com',role:'Entrepreneur',status:'Actif',date:'1 Mar 2025',last:'Il y a 2 jours'},
      {id:5,prenom:'Yassine',nom:'Karoui',email:'y.karoui@gmail.com',role:'Mentor',status:'Suspendu',date:'15 Mar 2025',last:'Il y a 12 jours'},
      {id:6,prenom:'Rania',nom:'Zouari',email:'rania.z@startup.tn',role:'Entreprise',status:'Actif',date:'22 Mar 2025',last:'Aujourd\'hui'},
      {id:7,prenom:'Bilel',nom:'Ferchichi',email:'bilel.f@gmail.com',role:'Entrepreneur',status:'En attente',date:'5 Avr 2025',last:'Jamais'},
      {id:8,prenom:'Maha',nom:'Sfar',email:'maha.s@mentor.tn',role:'Mentor',status:'Actif',date:'18 Avr 2025',last:'Hier'},
      {id:9,prenom:'Omar',nom:'Jouini',email:'omar.j@corp.tn',role:'Entreprise',status:'Actif',date:'2 Mai 2025',last:'Il y a 3 jours'},
      {id:10,prenom:'Nour',nom:'Chaabane',email:'nour.c@gmail.com',role:'Entrepreneur',status:'Suspendu',date:'10 Mai 2025',last:'Il y a 20 jours'},
    ];

    let users = [...RAW_USERS];
    let currentPage = 1;
    const perPage = 10;
    let deleteTargetId = null;
    let editTargetId = null;

    function getRoleBadge(role) {
      const map = {Entrepreneur:'badge-blue',Mentor:'badge-purple',Entreprise:'badge-amber'};
      return `<span class="badge ${map[role]||'badge-gray'}">${role}</span>`;
    }

    function getStatusBadge(s) {
      const map = {Actif:'badge-green',Suspendu:'badge-red','En attente':'badge-amber'};
      const dot = {Actif:'dot-green',Suspendu:'dot-gray','En attente':'dot-amber'};
      return `<span class="badge ${map[s]||'badge-gray'}"><span class="status-dot ${dot[s]||'dot-gray'}"></span>${s}</span>`;
    }

    function renderTable() {
      const search = document.getElementById('search-input').value.toLowerCase();
      const roleF = document.getElementById('role-filter').value;
      const statusF = document.getElementById('status-filter').value;

      let filtered = users.filter(u => {
        const fullName = `${u.prenom} ${u.nom}`.toLowerCase();
        const matchSearch = !search || fullName.includes(search) || u.email.includes(search);
        const matchRole = !roleF || u.role === roleF;
        const matchStatus = !statusF || u.status === statusF;
        return matchSearch && matchRole && matchStatus;
      });

      document.getElementById('table-count').textContent = `${filtered.length} utilisateur${filtered.length!==1?'s':''} trouvé${filtered.length!==1?'s':''}`;

      const start = (currentPage-1)*perPage;
      const page = filtered.slice(start, start+perPage);

      const tbody = document.getElementById('table-body');
      tbody.innerHTML = page.map((u, idx) => {
        const c = COLORS[u.id % COLORS.length];
        return `<tr>
          <td><input type="checkbox" class="row-check"></td>
          <td>
            <div class="user-cell">
              <div class="user-avatar" style="background:${c.bg};color:${c.color}">${initials(u.prenom+' '+u.nom)}</div>
              <div>
                <p class="user-name">${u.prenom} ${u.nom}</p>
                <p class="user-email">${u.email}</p>
              </div>
            </div>
          </td>
          <td>${getRoleBadge(u.role)}</td>
          <td>${getStatusBadge(u.status)}</td>
          <td style="color:var(--muted);font-size:12px">${u.date}</td>
          <td style="color:var(--muted);font-size:12px">${u.last}</td>
          <td>
            <div class="action-btns">
              <div class="action-btn view" title="Voir profil" onclick="viewUser(${u.id})"><i class="fa fa-eye"></i></div>
              <div class="action-btn edit" title="Modifier" onclick="openEditModal(${u.id})"><i class="fa fa-pen"></i></div>
              <div class="action-btn del" title="Supprimer" onclick="openDeleteModal(${u.id})"><i class="fa fa-trash"></i></div>
            </div>
          </td>
        </tr>`;
      }).join('');

      const total = filtered.length;
      const end = Math.min(start+perPage, total);
      document.getElementById('pagination-info').textContent =
        total === 0 ? 'Aucun résultat' : `Affichage ${start+1}–${end} sur ${total}`;
    }

    function filterTable() { currentPage = 1; renderTable(); }
    function changePage(dir) { currentPage = Math.max(1, currentPage+dir); renderTable(); }
    function goPage(p) { currentPage=p; renderTable(); }
    function toggleAll(cb) { document.querySelectorAll('.row-check').forEach(c=>c.checked=cb.checked); }

    function openAddModal() {
      editTargetId = null;
      document.getElementById('modal-title').textContent = 'Ajouter un utilisateur';
      ['f-prenom','f-nom','f-email'].forEach(id=>document.getElementById(id).value='');
      document.getElementById('f-role').value='Entrepreneur';
      document.getElementById('f-status').value='Actif';
      document.getElementById('user-modal').classList.add('open');
    }

    function openEditModal(id) {
      editTargetId = id;
      const u = users.find(u=>u.id===id);
      document.getElementById('modal-title').textContent = 'Modifier l\'utilisateur';
      document.getElementById('f-prenom').value = u.prenom;
      document.getElementById('f-nom').value = u.nom;
      document.getElementById('f-email').value = u.email;
      document.getElementById('f-role').value = u.role;
      document.getElementById('f-status').value = u.status;
      document.getElementById('user-modal').classList.add('open');
    }

    function saveUser() {
      const prenom = document.getElementById('f-prenom').value.trim();
      const nom = document.getElementById('f-nom').value.trim();
      const email = document.getElementById('f-email').value.trim();
      const role = document.getElementById('f-role').value;
      const status = document.getElementById('f-status').value;
      if (!prenom || !nom || !email) { showToast('Tous les champs sont requis.','error','fa-circle-exclamation'); return; }

      if (editTargetId) {
        const u = users.find(u=>u.id===editTargetId);
        Object.assign(u, {prenom,nom,email,role,status});
        showToast(`${prenom} ${nom} modifié avec succès.`,'success','fa-circle-check');
      } else {
        users.unshift({id:Date.now(),prenom,nom,email,role,status,date:'Aujourd\'hui',last:'Jamais'});
        document.getElementById('user-count-badge').textContent = users.length;
        showToast(`${prenom} ${nom} ajouté avec succès.`,'success','fa-circle-check');
      }
      closeModal('user-modal');
      renderTable();
    }

    function openDeleteModal(id) {
      deleteTargetId = id;
      const u = users.find(u=>u.id===id);
      document.getElementById('delete-name').textContent = `${u.prenom} ${u.nom}`;
      document.getElementById('delete-modal').classList.add('open');
    }

    function confirmDelete() {
      const u = users.find(u=>u.id===deleteTargetId);
      users = users.filter(u=>u.id!==deleteTargetId);
      document.getElementById('user-count-badge').textContent = users.length;
      closeModal('delete-modal');
      showToast(`${u.prenom} ${u.nom} supprimé.`,'error','fa-trash');
      renderTable();
    }

    function viewUser(id) {
      const u = users.find(u=>u.id===id);
      showToast(`Profil : ${u.prenom} ${u.nom} — ${u.email}`,'success','fa-eye');
    }

    function closeModal(id) { document.getElementById(id).classList.remove('open'); }

    function showToast(msg, type='success', icon='fa-circle-check') {
      const t = document.createElement('div');
      t.className = `toast ${type}`;
      t.innerHTML = `<i class="fa ${icon}"></i> ${msg}`;
      document.getElementById('toast-container').appendChild(t);
      setTimeout(()=>t.remove(), 3500);
    }

    function exportCSV() {
      const rows = [['Prénom','Nom','Email','Rôle','Statut','Inscription','Dernière connexion']];
      users.forEach(u=>rows.push([u.prenom,u.nom,u.email,u.role,u.status,u.date,u.last]));
      const csv = rows.map(r=>r.join(',')).join('\n');
      const a = document.createElement('a');
      a.href = URL.createObjectURL(new Blob([csv],{type:'text/csv'}));
      a.download = 'jobyfind_utilisateurs.csv';
      a.click();
      showToast('Export CSV téléchargé.','success','fa-download');
    }

    function showPage(page) {
      document.querySelectorAll('.sidebar-link').forEach(l=>l.classList.remove('active'));
      event.currentTarget.classList.add('active');
      const titles = {users:'Utilisateurs',stats:'Statistiques',roles:'Rôles & Accès',courses:'Formations',reports:'Signalements',settings:'Paramètres'};
      document.getElementById('page-title').textContent = titles[page]||page;
      if (page !== 'users') showToast('Section en cours de développement.','success','fa-wrench');
    }

    document.querySelectorAll('.modal-overlay').forEach(m=>{
      m.addEventListener('click', e=>{ if(e.target===m) m.classList.remove('open'); });
    });

    renderTable();
