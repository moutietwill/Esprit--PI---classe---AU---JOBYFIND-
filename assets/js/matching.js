/**
 * JOBYFIND - Intelligent Matching System
 */

(function () {
  // Dictionnaire de compétences techniques
  const TECH_SKILLS = [
    'PHP', 'JavaScript', 'Python', 'Java', 'C++', 'C#', 'Ruby', 'Swift', 'Go', 'Rust',
    'HTML', 'CSS', 'React', 'Angular', 'Vue', 'Next.js', 'Node.js', 'Express', 'Laravel', 'Symfony',
    'SQL', 'MySQL', 'PostgreSQL', 'MongoDB', 'Redis', 'Docker', 'Kubernetes', 'Git', 'GitHub',
    'AWS', 'Azure', 'Firebase', 'TypeScript', 'Tailwind', 'Bootstrap', 'jQuery', 'Spring', 'Django',
    'Flask', 'Android', 'iOS', 'Flutter', 'React Native', 'DevOps', 'Agile', 'Scrum', 'Figma'
  ];

  const matchingToggle = document.createElement('div');
  matchingToggle.className = 'matching-toggle';
  matchingToggle.innerHTML = '<i class="fa-solid fa-wand-magic-sparkles"></i>';
  matchingToggle.title = 'Mon Profil de Compétences';
  document.body.appendChild(matchingToggle);

  const matchingPanel = document.createElement('div');
  matchingPanel.className = 'matching-panel';
  matchingPanel.innerHTML = `
    <div class="matching-header">
      <h4><i class="fa-solid fa-user-gear"></i> Mes Compétences</h4>
      <i class="fa-solid fa-times" style="cursor:pointer;color:var(--text-muted)" id="closeMatching"></i>
    </div>
    <div class="skills-grid" id="skillsGrid"></div>
    <div class="matching-footer">
      Sélectionnez vos compétences pour voir votre <b>compatibilité</b> avec chaque offre en temps réel.
    </div>
  `;
  document.body.appendChild(matchingPanel);

  const skillsGrid = document.getElementById('skillsGrid');
  const userSkills = new Set(JSON.parse(localStorage.getItem('userSkills') || '[]'));

  // Initialisation des tags de compétences
  TECH_SKILLS.forEach(skill => {
    const tag = document.createElement('div');
    tag.className = 'skill-tag' + (userSkills.has(skill) ? ' selected' : '');
    tag.textContent = skill;
    tag.onclick = () => toggleSkill(skill, tag);
    skillsGrid.appendChild(tag);
  });

  function toggleSkill(skill, tag) {
    if (userSkills.has(skill)) {
      userSkills.delete(skill);
      tag.classList.remove('selected');
    } else {
      userSkills.add(skill);
      tag.classList.add('selected');
    }
    localStorage.setItem('userSkills', JSON.stringify(Array.from(userSkills)));
    calculateAllMatches();
  }

  // Analyse et Injection des jauges sur les cartes
  function injectMatchUIs() {
    const cards = document.querySelectorAll('.offre-card');
    cards.forEach(card => {
      if (card.querySelector('.match-score-wrap')) return;

      const title = card.querySelector('.card-title').textContent;
      const desc = card.querySelector('.card-desc').textContent;
      const fullText = (title + ' ' + desc).toLowerCase();
      
      // Trouver les skills requis par l'offre
      const requiredSkills = TECH_SKILLS.filter(s => 
        new RegExp('\\b' + s.toLowerCase().replace(/[.*+?^${}()|[\]\\]/g, '\\$&') + '\\b', 'i').test(fullText)
      );
      
      card.dataset.requiredSkills = JSON.stringify(requiredSkills);

      const ui = document.createElement('div');
      ui.className = 'match-score-wrap';
      ui.innerHTML = `
        <svg class="match-circle" viewBox="0 0 36 36">
          <circle class="bg" cx="18" cy="18" r="15.915"></circle>
          <circle class="progress" cx="18" cy="18" r="15.915"></circle>
        </svg>
        <span class="match-text">0%</span>
      `;
      card.style.position = 'relative';
      card.appendChild(ui);
    });
    calculateAllMatches();
  }

  function calculateAllMatches() {
    const cards = document.querySelectorAll('.offre-card');
    cards.forEach(card => {
      const required = JSON.parse(card.dataset.requiredSkills || '[]');
      const wrap = card.querySelector('.match-score-wrap');
      if (!wrap) return;

      if (required.length === 0) {
        wrap.style.display = 'none';
        return;
      } else {
        wrap.style.display = 'flex';
      }

      const matchCount = required.filter(s => userSkills.has(s)).length;
      const score = Math.round((matchCount / required.length) * 100);
      
      const progressCircle = wrap.querySelector('.progress');
      const text = wrap.querySelector('.match-text');
      
      // Update UI
      text.textContent = score + '%';
      const offset = 100 - score;
      progressCircle.style.strokeDashoffset = offset;
      
      // Change color based on score
      let color = '#cbd5e1'; // Gray if no selection
      if (userSkills.size > 0) {
        if (score >= 70) color = 'var(--match-green)';
        else if (score >= 40) color = 'var(--match-orange)';
        else color = 'var(--match-red)';
      }
      progressCircle.style.stroke = color;

      wrap.title = `Compétences détectées dans l'offre : ${required.join(', ')}`;
    });
  }

  // Fallback for demo: if job has NO skills, suggest adding some
  function handleNoSkills() {
    const cards = document.querySelectorAll('.offre-card');
    cards.forEach(card => {
        const required = JSON.parse(card.dataset.requiredSkills || '[]');
        if (required.length === 0) {
            const wrap = card.querySelector('.match-score-wrap');
            if (wrap) {
                wrap.style.display = 'flex';
                wrap.querySelector('.match-text').textContent = '?';
                wrap.title = "Aucune compétence technique détectée dans la description. Ajoutez des mots-clés (ex: PHP, Java) pour activer le matching.";
            }
        }
    });
  }

  // Handlers
  matchingToggle.onclick = () => matchingPanel.classList.toggle('open');
  document.getElementById('closeMatching').onclick = () => matchingPanel.classList.remove('open');

  // Initialisation
  setTimeout(() => {
    injectMatchUIs();
    handleNoSkills();
  }, 100);

  // Observer pour gérer la recherche dynamique si nécessaire
  const observer = new MutationObserver(injectMatchUIs);
  observer.observe(document.getElementById('offresGrid'), { childList: true });

})();
