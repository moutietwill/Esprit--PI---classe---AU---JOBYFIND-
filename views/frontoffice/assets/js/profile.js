 // ── AVATAR UPLOAD ──
    document.getElementById('avatar-input').addEventListener('change', function(e) {
      const file = e.target.files[0];
      if (!file) return;
      if (!file.type.startsWith('image/')) { showToast('Veuillez sélectionner une image.', 'error'); return; }
      if (file.size > 5 * 1024 * 1024) { showToast('Image trop grande (max 5 Mo).', 'error'); return; }
      const reader = new FileReader();
      reader.onload = function(ev) {
        const img = document.getElementById('avatar-preview');
        img.src = ev.target.result;
        img.classList.add('loaded');
        document.getElementById('avatar-initials').style.display = 'none';
        showToast('Photo de profil mise à jour.', 'success');
      };
      reader.readAsDataURL(file);
    });

    // ── SECTION TOGGLE ──
    function toggleSection(key) {
      const view = document.getElementById('view-' + key);
      const edit = document.getElementById('edit-' + key);
      const btn  = document.getElementById('toggle-' + key);
      if (edit.style.display === 'none') {
        edit.style.display = 'block';
        view.style.display = 'none';
        btn.innerHTML = '<i class="fa fa-times"></i> Annuler';
      } else {
        cancelSection(key);
      }
    }

    function cancelSection(key) {
      document.getElementById('view-' + key).style.display = 'block';
      document.getElementById('edit-' + key).style.display = 'none';
      const btn = document.getElementById('toggle-' + key);
      if (key === 'info') btn.innerHTML = '<i class="fa fa-pen"></i> Modifier';
      if (key === 'pwd') btn.innerHTML = '<i class="fa fa-lock"></i> Changer le mot de passe';
      clearErrors();
    }

    // ── VALIDATION HELPERS ──
    function showError(id, show) {
      const el = document.getElementById(id);
      if (show) { el.classList.add('show'); }
      else { el.classList.remove('show'); }
    }
    function markField(id, error) {
      const el = document.getElementById(id);
      if (error) el.classList.add('error');
      else el.classList.remove('error');
    }
    function clearErrors() {
      document.querySelectorAll('.field-error').forEach(e => e.classList.remove('show'));
      document.querySelectorAll('input, textarea, select').forEach(e => e.classList.remove('error'));
    }

    // ── SAVE INFO ──
    function saveInfo() {
      clearErrors();
      let valid = true;

      const prenom = document.getElementById('f-prenom').value.trim();
      if (!prenom || prenom.length < 2 || !/^[A-Za-zÀ-ÿ\s\-']+$/.test(prenom)) {
        showError('e-prenom', true); markField('f-prenom', true); valid = false;
      }

      const nom = document.getElementById('f-nom').value.trim();
      if (!nom || nom.length < 2 || !/^[A-Za-zÀ-ÿ\s\-']+$/.test(nom)) {
        showError('e-nom', true); markField('f-nom', true); valid = false;
      }

      const email = document.getElementById('f-email').value.trim();
      if (!email || !/^[^\s@]+@[^\s@]+\.[^\s@]+$/.test(email)) {
        showError('e-email', true); markField('f-email', true); valid = false;
      }

      const phone = document.getElementById('f-phone').value.trim();
      if (phone && !/^(\+216\s?)?[0-9\s\-]{8,15}$/.test(phone)) {
        showError('e-phone', true); markField('f-phone', true); valid = false;
      }

      const bio = document.getElementById('f-bio').value.trim();
      if (bio.length > 300) {
        showError('e-bio', true); markField('f-bio', true); valid = false;
      }

      const linkedin = document.getElementById('f-linkedin').value.trim();
      if (linkedin && !/^https?:\/\/(www\.)?linkedin\.com\//.test(linkedin)) {
        showError('e-linkedin', true); markField('f-linkedin', true); valid = false;
      }

      if (!valid) { showToast('Veuillez corriger les erreurs.', 'error'); return; }

      // Update display
      const role = document.getElementById('f-role').value;
      const city = document.getElementById('f-city').value.trim();

      document.getElementById('v-prenom').textContent = prenom;
      document.getElementById('v-nom').textContent = nom;
      document.getElementById('v-email').textContent = email;
      document.getElementById('v-phone').textContent = phone || '—';
      document.getElementById('v-role').textContent = role;
      document.getElementById('v-city').textContent = city || '—';
      document.getElementById('v-bio').textContent = bio || '—';

      document.getElementById('display-name').textContent = prenom + ' ' + nom;
      document.getElementById('display-role').textContent = role;
      document.getElementById('display-email').textContent = email;
      document.getElementById('display-phone').textContent = phone || '—';
      document.getElementById('display-city').textContent = city || '—';
      document.getElementById('display-bio').textContent = bio || '—';
      if (linkedin) document.getElementById('display-linkedin').textContent = linkedin.replace(/^https?:\/\//, '');
      document.getElementById('avatar-initials').textContent = (prenom[0] + (nom[0]||'')).toUpperCase();

      cancelSection('info');
      showToast('Profil mis à jour avec succès.', 'success');
    }

    // ── PASSWORD ──
    function togglePwd(inputId, btn) {
      const inp = document.getElementById(inputId);
      const show = inp.type === 'password';
      inp.type = show ? 'text' : 'password';
      btn.querySelector('i').className = show ? 'fa fa-eye-slash' : 'fa fa-eye';
    }

    function checkStrength(val) {
      const segs = [document.getElementById('s1'),document.getElementById('s2'),
                    document.getElementById('s3'),document.getElementById('s4')];
      const label = document.getElementById('strength-label');
      let score = 0;
      if (val.length >= 8) score++;
      if (/[A-Z]/.test(val)) score++;
      if (/[0-9]/.test(val)) score++;
      if (/[^A-Za-z0-9]/.test(val)) score++;

      const colors = ['#ef4444','#f59e0b','#3b82f6','#22c55e'];
      const labels = ['Très faible','Moyen','Fort','Très fort'];
      segs.forEach((s, i) => { s.style.background = i < score ? colors[score-1] : 'var(--border)'; });
      label.textContent = val.length ? labels[Math.max(0,score-1)] : 'Saisissez un mot de passe';
      label.style.color = val.length ? colors[Math.max(0,score-1)] : 'var(--muted)';
    }

    function savePwd() {
      clearErrors();
      let valid = true;

      const current = document.getElementById('f-pwd-current').value;
      if (!current) { showError('e-pwd-current', true); markField('f-pwd-current', true); valid = false; }

      const newPwd = document.getElementById('f-pwd-new').value;
      if (!newPwd || newPwd.length < 8 || !/[0-9]/.test(newPwd) || !/[A-Za-z]/.test(newPwd)) {
        showError('e-pwd-new', true); markField('f-pwd-new', true); valid = false;
      }

      const confirm = document.getElementById('f-pwd-confirm').value;
      if (!confirm || confirm !== newPwd) {
        showError('e-pwd-confirm', true); markField('f-pwd-confirm', true); valid = false;
      }

      if (!valid) { showToast('Veuillez corriger les erreurs.', 'error'); return; }

      ['f-pwd-current','f-pwd-new','f-pwd-confirm'].forEach(id => document.getElementById(id).value = '');
      checkStrength('');
      cancelSection('pwd');
      showToast('Mot de passe mis à jour.', 'success');
    }

    // ── TOAST ──
    function showToast(msg, type = 'success') {
      const t = document.createElement('div');
      t.className = 'toast ' + type;
      t.innerHTML = `<i class="fa ${type === 'success' ? 'fa-circle-check' : 'fa-circle-exclamation'}"></i> ${msg}`;
      document.getElementById('toast-container').appendChild(t);
      setTimeout(() => t.remove(), 3500);
    }