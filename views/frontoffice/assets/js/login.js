function setTab(el) {
      document.querySelectorAll('.role-tab').forEach(t => t.classList.remove('active'));
      el.classList.add('active');
    }