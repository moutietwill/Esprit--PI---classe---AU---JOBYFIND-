/**
 * ══════════════════════════════════════════════
 *  Jobyfind Panier (Cart) Widget
 *  Save offers and manage candidatures
 * ══════════════════════════════════════════════
 */
(function () {
  'use strict';

  const STORAGE_KEY = 'jobyfind_panier';

  /* ── Cart Data (localStorage) ── */
  function getCart() {
    try {
      return JSON.parse(localStorage.getItem(STORAGE_KEY)) || [];
    } catch (e) {
      return [];
    }
  }

  function saveCart(cart) {
    localStorage.setItem(STORAGE_KEY, JSON.stringify(cart));
  }

  function addToCart(offer) {
    const cart = getCart();
    if (!cart.find(o => o.id === offer.id)) {
      cart.push(offer);
      saveCart(cart);
    }
    return cart;
  }

  function removeFromCart(offerId) {
    let cart = getCart();
    cart = cart.filter(o => o.id !== offerId);
    saveCart(cart);
    return cart;
  }

  function clearCart() {
    saveCart([]);
    return [];
  }

  function isInCart(offerId) {
    return getCart().some(o => o.id === offerId);
  }

  /* ── Get type CSS class ── */
  function getTypeClass(type) {
    const t = (type || '').toLowerCase();
    if (t.includes('cdi'))   return 'type-cdi';
    if (t.includes('cdd'))   return 'type-cdd';
    if (t.includes('stage')) return 'type-stage';
    return 'type-other';
  }

  /* ── Build the Panel DOM ── */
  function buildPanel() {
    const html = `
      <!-- Overlay -->
      <div class="panier-overlay" id="panierOverlay"></div>

      <!-- Floating Cart Button -->
      <button class="panier-toggle" id="panierToggle" aria-label="Ouvrir le panier">
        <i class="fa-solid fa-bookmark"></i>
        <span class="panier-count hidden" id="panierCount">0</span>
      </button>

      <!-- Side Panel -->
      <div class="panier-panel" id="panierPanel">
        <div class="panier-header">
          <div class="panier-header-left">
            <div class="panier-header-icon"><i class="fa-solid fa-bookmark"></i></div>
            <h3>Mes offres sauvegardées<span id="panierSubtitle">0 offre(s)</span></h3>
          </div>
          <button class="panier-close-btn" id="panierClose" aria-label="Fermer"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="panier-body" id="panierBody"></div>
        <div class="panier-footer" id="panierFooter" style="display:none;">
          <div class="panier-footer-info">
            <span>Total sauvegardées</span>
            <strong id="panierTotal">0</strong>
          </div>
          <button class="panier-clear-btn" id="panierClear">
            <i class="fa-solid fa-trash-can"></i> Vider le panier
          </button>
        </div>
      </div>
    `;

    const wrapper = document.createElement('div');
    wrapper.id = 'panier-widget';
    wrapper.innerHTML = html;
    document.body.appendChild(wrapper);
  }

  /* ── Add save buttons to all offer cards ── */
  function injectSaveButtons() {
    const cards = document.querySelectorAll('.offre-card');
    cards.forEach(function (card) {
      // Extract offer data from card
      const link = card.querySelector('.card-btn');
      if (!link) return;

      const href = link.getAttribute('href') || '';
      const idMatch = href.match(/id_offre=(\d+)/);
      const offerId = idMatch ? idMatch[1] : null;
      if (!offerId) return;

      card.dataset.offreId = offerId;

      // Create save button
      const btn = document.createElement('button');
      btn.className = 'card-save-btn';
      btn.title = 'Sauvegarder dans le panier';
      btn.dataset.offreId = offerId;
      btn.innerHTML = '<i class="fa-regular fa-bookmark"></i>';

      // Check if already saved
      if (isInCart(offerId)) {
        btn.classList.add('saved');
        btn.innerHTML = '<i class="fa-solid fa-bookmark"></i>';
        btn.title = 'Retirer du panier';
      }

      btn.addEventListener('click', function (e) {
        e.preventDefault();
        e.stopPropagation();
        toggleCartItem(card, btn);
      });

      card.appendChild(btn);
    });
  }

  /* ── Toggle item in/out of cart ── */
  function toggleCartItem(card, btn) {
    const offerId = card.dataset.offreId;

    if (isInCart(offerId)) {
      // Remove
      removeFromCart(offerId);
      btn.classList.remove('saved');
      btn.innerHTML = '<i class="fa-regular fa-bookmark"></i>';
      btn.title = 'Sauvegarder dans le panier';
    } else {
      // Add
      const offer = {
        id: offerId,
        titre: card.querySelector('.card-title') ? card.querySelector('.card-title').textContent.trim() : '',
        type: card.dataset.type || '',
        date: card.dataset.date || '',
        description: card.querySelector('.card-desc') ? card.querySelector('.card-desc').textContent.trim() : '',
        applyUrl: card.querySelector('.card-btn') ? card.querySelector('.card-btn').getAttribute('href') : '#'
      };
      addToCart(offer);
      btn.classList.add('saved');
      btn.innerHTML = '<i class="fa-solid fa-bookmark"></i>';
      btn.title = 'Retirer du panier';

      // Pop animation
      btn.classList.remove('pop');
      void btn.offsetWidth; // force reflow
      btn.classList.add('pop');
    }

    updateBadge();
    // If panel is open, refresh it
    if (document.getElementById('panierPanel').classList.contains('open')) {
      renderCartItems();
    }
  }

  /* ── Update the floating badge count ── */
  function updateBadge() {
    const count = getCart().length;
    const badge = document.getElementById('panierCount');
    const subtitle = document.getElementById('panierSubtitle');

    badge.textContent = count;
    if (count > 0) {
      badge.classList.remove('hidden');
      badge.classList.remove('bounce');
      void badge.offsetWidth;
      badge.classList.add('bounce');
    } else {
      badge.classList.add('hidden');
    }

    subtitle.textContent = count + ' offre(s)';
  }

  /* ── Render cart items inside the panel ── */
  function renderCartItems() {
    const body = document.getElementById('panierBody');
    const footer = document.getElementById('panierFooter');
    const total = document.getElementById('panierTotal');
    const cart = getCart();

    body.innerHTML = '';

    if (cart.length === 0) {
      footer.style.display = 'none';
      body.innerHTML = `
        <div class="panier-empty">
          <i class="fa-regular fa-bookmark"></i>
          <p>Votre panier est vide.<br><strong>Cliquez sur <i class="fa-regular fa-bookmark"></i> sur une offre</strong> pour la sauvegarder ici.</p>
        </div>
      `;
      return;
    }

    footer.style.display = '';
    total.textContent = cart.length;

    cart.forEach(function (offer) {
      const typeClass = getTypeClass(offer.type);
      const desc = offer.description ? offer.description.substring(0, 80) + (offer.description.length > 80 ? '…' : '') : '';

      const item = document.createElement('div');
      item.className = 'panier-item';
      item.dataset.offreId = offer.id;

      item.innerHTML = `
        <div class="panier-item-top">
          <div class="panier-item-info">
            <span class="panier-item-badge ${typeClass}">${escapeHtml(offer.type)}</span>
            <h4 class="panier-item-title">${escapeHtml(offer.titre)}</h4>
            <div class="panier-item-date"><i class="fa-regular fa-calendar"></i> ${escapeHtml(offer.date)}</div>
          </div>
          <button class="panier-item-remove" title="Retirer" data-id="${offer.id}"><i class="fa-solid fa-xmark"></i></button>
        </div>
        <div class="panier-item-actions">
          <a href="${escapeHtml(offer.applyUrl)}" class="panier-item-apply"><i class="fa-solid fa-paper-plane"></i> Postuler</a>
        </div>
      `;

      // Remove button
      item.querySelector('.panier-item-remove').addEventListener('click', function () {
        const id = this.dataset.id;
        removeFromCart(id);
        updateBadge();
        syncSaveButtons();
        renderCartItems();
      });

      body.appendChild(item);
    });
  }

  /* ── Sync card save buttons with cart state ── */
  function syncSaveButtons() {
    const buttons = document.querySelectorAll('.card-save-btn');
    buttons.forEach(function (btn) {
      const id = btn.dataset.offreId;
      if (isInCart(id)) {
        btn.classList.add('saved');
        btn.innerHTML = '<i class="fa-solid fa-bookmark"></i>';
        btn.title = 'Retirer du panier';
      } else {
        btn.classList.remove('saved');
        btn.innerHTML = '<i class="fa-regular fa-bookmark"></i>';
        btn.title = 'Sauvegarder dans le panier';
      }
    });
  }

  /* ── HTML escape helper ── */
  function escapeHtml(str) {
    const div = document.createElement('div');
    div.textContent = str || '';
    return div.innerHTML;
  }

  /* ── Open / Close panel ── */
  function openPanel() {
    document.getElementById('panierPanel').classList.add('open');
    document.getElementById('panierOverlay').classList.add('open');
    document.getElementById('panierToggle').classList.add('open');
    renderCartItems();
  }

  function closePanel() {
    document.getElementById('panierPanel').classList.remove('open');
    document.getElementById('panierOverlay').classList.remove('open');
    document.getElementById('panierToggle').classList.remove('open');
  }

  /* ── Initialize ── */
  function init() {
    buildPanel();
    injectSaveButtons();
    updateBadge();

    // Toggle button
    document.getElementById('panierToggle').addEventListener('click', function () {
      const panel = document.getElementById('panierPanel');
      if (panel.classList.contains('open')) {
        closePanel();
      } else {
        openPanel();
      }
    });

    // Close button
    document.getElementById('panierClose').addEventListener('click', closePanel);

    // Overlay click to close
    document.getElementById('panierOverlay').addEventListener('click', closePanel);

    // Clear all
    document.getElementById('panierClear').addEventListener('click', function () {
      if (confirm('Êtes-vous sûr de vouloir vider votre panier ?')) {
        clearCart();
        updateBadge();
        syncSaveButtons();
        renderCartItems();
      }
    });

    // Escape to close
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && document.getElementById('panierPanel').classList.contains('open')) {
        closePanel();
      }
    });
  }

  /* ── Start when DOM is ready ── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
