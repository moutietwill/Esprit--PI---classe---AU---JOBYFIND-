function setTab(el) {
    document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
    el.classList.add('active');
}

function updateStrength(val) {
    const segs = [s1, s2, s3, s4];
    const label = document.getElementById('strength-label');
    let score = 0;
    if (val.length >= 8) score++;
    if (/[A-Z]/.test(val)) score++;
    if (/[0-9]/.test(val)) score++;
    if (/[^A-Za-z0-9]/.test(val)) score++;

    const colors = ['#ef4444','#f97316','#facc15','#22c55e'];
    const labels = ['Très faible','Faible','Moyen','Fort'];
    segs.forEach((s, i) => s.style.background = i < score ? colors[score - 1] : 'var(--border)');
    label.textContent = val.length === 0 ? 'Entrez un mot de passe' : labels[score - 1] || 'Très faible';
    label.style.color = val.length === 0 ? 'var(--muted)' : colors[score - 1] || colors[0];
}