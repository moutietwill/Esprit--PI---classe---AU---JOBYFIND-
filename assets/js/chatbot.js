/**
 * ══════════════════════════════════════════════
 *  Jobyfind Chatbot Widget
 *  Smart assistant for job seekers
 * ══════════════════════════════════════════════
 */
(function () {
  'use strict';

  /* ── Configuration ── */
  const BOT_NAME = 'JobyBot';
  const TYPING_DELAY_MIN = 600;
  const TYPING_DELAY_MAX = 1400;

  /* ── Knowledge Base ── */
  const knowledgeBase = [
    {
      keywords: ['bonjour', 'salut', 'hello', 'bonsoir', 'hey', 'coucou', 'hi'],
      response: "Bonjour ! 👋 Je suis **JobyBot**, votre assistant Jobyfind. Comment puis-je vous aider aujourd'hui ?",
      quickReplies: ['Voir les offres', 'Types de contrats', 'Comment postuler ?']
    },
    {
      keywords: ['offre', 'offres', 'emploi', 'emplois', 'job', 'jobs', 'voir les offres', 'cherche', 'recherche'],
      response: "📋 Toutes nos offres sont affichées sur cette page ! Vous pouvez :\n\n• Utiliser la **barre de recherche** pour filtrer par mot-clé\n• Cliquer sur les **filtres de type** (CDI, CDD, Stage…)\n• **Trier** par date ou par titre\n\nVoulez-vous que je vous aide à trouver un type d'offre spécifique ?",
      quickReplies: ['Offres CDI', 'Offres CDD', 'Offres Stage']
    },
    {
      keywords: ['cdi', 'offres cdi', 'contrat indéterminé'],
      response: "💼 Les offres **CDI** (Contrat à Durée Indéterminée) représentent des postes permanents. C'est le type de contrat le plus stable !\n\nPour les voir, cliquez sur le filtre **CDI** dans la barre d'outils ci-dessus.",
      quickReplies: ['Comment postuler ?', 'Autres types', 'Merci']
    },
    {
      keywords: ['cdd', 'offres cdd', 'contrat déterminé'],
      response: "📝 Les offres **CDD** (Contrat à Durée Déterminée) sont des contrats temporaires, souvent pour remplacer un salarié ou répondre à un besoin ponctuel.\n\nCliquez sur le filtre **CDD** pour les afficher.",
      quickReplies: ['Comment postuler ?', 'Autres types', 'Merci']
    },
    {
      keywords: ['stage', 'stages', 'offres stage', 'stagiaire', 'internship'],
      response: "🎓 Les **stages** sont parfaits pour les étudiants et jeunes diplômés qui souhaitent acquérir de l'expérience professionnelle.\n\nUtilisez le filtre **Stage** pour les retrouver facilement !",
      quickReplies: ['Comment postuler ?', 'Autres types', 'Merci']
    },
    {
      keywords: ['postuler', 'candidature', 'candidater', 'comment postuler', 'comment candidater', 'envoyer', 'postulation'],
      response: "🚀 Pour postuler à une offre, c'est très simple :\n\n1. Trouvez une offre qui vous intéresse\n2. Cliquez sur le bouton **\"Postuler →\"** en bas de la carte\n3. Remplissez le formulaire de candidature\n4. Validez et c'est envoyé !\n\nBonne chance ! 🍀",
      quickReplies: ['Voir les offres', 'Types de contrats', 'Merci']
    },
    {
      keywords: ['type', 'types', 'contrat', 'contrats', 'types de contrats', 'autres types', 'catégorie', 'catégories'],
      response: "📊 Voici les principaux types de contrats sur Jobyfind :\n\n• **CDI** — Contrat à Durée Indéterminée (stable)\n• **CDD** — Contrat à Durée Déterminée (temporaire)\n• **Stage** — Pour étudiants et jeunes diplômés\n\nChaque offre est identifiée par un badge coloré pour un repérage rapide !",
      quickReplies: ['Offres CDI', 'Offres CDD', 'Offres Stage']
    },
    {
      keywords: ['merci', 'thanks', 'thank', 'super', 'parfait', 'génial', 'cool', 'top'],
      response: "Avec plaisir ! 😊 N'hésitez pas à me poser d'autres questions. Je suis là pour vous aider à trouver l'offre idéale ! 💪",
      quickReplies: ['Voir les offres', 'Comment postuler ?']
    },
    {
      keywords: ['aide', 'help', 'aider', 'assistance', 'support', 'question'],
      response: "🤝 Je peux vous aider avec :\n\n• **Trouver des offres** — par type ou mot-clé\n• **Comprendre les types de contrats** — CDI, CDD, Stage\n• **Postuler** — comment envoyer votre candidature\n• **Naviguer** — utiliser les filtres et la recherche\n\nQue souhaitez-vous faire ?",
      quickReplies: ['Voir les offres', 'Types de contrats', 'Comment postuler ?']
    },
    {
      keywords: ['filtre', 'filtrer', 'rechercher', 'chercher', 'trouver', 'tri', 'trier'],
      response: "🔍 Pour trouver rapidement une offre :\n\n• **Recherche** : tapez un mot-clé dans la barre de recherche en haut\n• **Filtres** : cliquez sur CDI, CDD ou Stage pour filtrer par type\n• **Tri** : utilisez le menu déroulant pour trier par date ou par titre\n\nTous ces outils sont accessibles juste au-dessus de la grille d'offres !",
      quickReplies: ['Voir les offres', 'Comment postuler ?']
    },
    {
      keywords: ['contact', 'contacter', 'email', 'mail', 'téléphone', 'appeler'],
      response: "📧 Pour nous contacter, vous pouvez :\n\n• Envoyer un email à **contact@jobyfind.com**\n• Nous suivre sur nos réseaux sociaux\n\nMais n'hésitez pas à me poser vos questions ici d'abord, je peux sûrement vous aider ! 😊",
      quickReplies: ['Voir les offres', 'Comment postuler ?']
    }
  ];

  /* ── Default response when no match ── */
  const defaultResponse = {
    response: "🤔 Je ne suis pas sûr de comprendre. Voici ce que je peux faire pour vous :\n\n• Vous aider à **trouver des offres**\n• Expliquer les **types de contrats**\n• Vous guider pour **postuler**\n\nEssayez l'un des boutons ci-dessous ! 👇",
    quickReplies: ['Voir les offres', 'Types de contrats', 'Comment postuler ?', 'Aide']
  };

  /* ── Build the DOM ── */
  function buildWidget() {
    const html = `
      <!-- Toggle Button -->
      <button class="chatbot-toggle" id="chatbotToggle" aria-label="Ouvrir le chatbot">
        <span class="icon-chat"><i class="fa-solid fa-comments"></i></span>
        <span class="icon-close"><i class="fa-solid fa-xmark"></i></span>
        <span class="chatbot-badge" id="chatbotBadge">1</span>
      </button>

      <!-- Chat Window -->
      <div class="chatbot-window" id="chatbotWindow" role="dialog" aria-label="Chat assistant Jobyfind">
        <div class="chatbot-header">
          <div class="chatbot-avatar">🤖</div>
          <div class="chatbot-header-info">
            <h4>${BOT_NAME}</h4>
            <p><span class="chatbot-status-dot"></span> En ligne — Prêt à aider</p>
          </div>
        </div>
        <div class="chatbot-messages-wrap" style="position:relative;flex:1;overflow:hidden;display:flex;flex-direction:column;">
          <div class="chatbot-messages" id="chatbotMessages" tabindex="0"></div>
          <button class="chatbot-scroll-btn" id="chatbotScrollBtn" title="Revenir en bas" style="display:none;"><i class="fa-solid fa-arrow-down"></i></button>
        </div>
        <div id="chatbotQuickReplies" class="chatbot-quick-replies" style="display:none;"></div>
        <div class="chatbot-input-area">
          <textarea class="chatbot-input" id="chatbotInput" placeholder="Écrivez votre message…" rows="1"></textarea>
          <button class="chatbot-send-btn" id="chatbotSend" aria-label="Envoyer" disabled>
            <i class="fa-solid fa-paper-plane"></i>
          </button>
        </div>
        <div class="chatbot-footer">Propulsé par <span>Jobyfind</span></div>
      </div>
    `;

    const wrapper = document.createElement('div');
    wrapper.id = 'chatbot-widget';
    wrapper.innerHTML = html;
    document.body.appendChild(wrapper);
  }

  /* ── Utility: get current time string ── */
  function getTime() {
    const now = new Date();
    return now.getHours().toString().padStart(2, '0') + ':' + now.getMinutes().toString().padStart(2, '0');
  }

  /* ── Utility: simple markdown-like formatting ── */
  function formatText(text) {
    return text
      .replace(/\*\*(.*?)\*\*/g, '<strong>$1</strong>')
      .replace(/\n/g, '<br>');
  }

  /* ── Utility: random delay for realism ── */
  function typingDelay() {
    return TYPING_DELAY_MIN + Math.random() * (TYPING_DELAY_MAX - TYPING_DELAY_MIN);
  }

  /* ── Match user input to knowledge base ── */
  function findResponse(input) {
    const lower = input.toLowerCase().trim();

    let bestMatch = null;
    let bestScore = 0;

    for (const entry of knowledgeBase) {
      for (const keyword of entry.keywords) {
        if (lower.includes(keyword)) {
          const score = keyword.length;
          if (score > bestScore) {
            bestScore = score;
            bestMatch = entry;
          }
        }
      }
    }

    return bestMatch || defaultResponse;
  }

  /* ── Scan page for live offer data ── */
  function getOfferStats() {
    const cards = document.querySelectorAll('.offre-card');
    const visible = Array.from(cards).filter(c => c.style.display !== 'none');
    const types = {};
    visible.forEach(c => {
      const t = c.dataset.type || 'Autre';
      types[t] = (types[t] || 0) + 1;
    });
    return { total: visible.length, types };
  }

  /* ── Initialize ── */
  function init() {
    buildWidget();

    const toggle   = document.getElementById('chatbotToggle');
    const chatWin  = document.getElementById('chatbotWindow');
    const messages = document.getElementById('chatbotMessages');
    const input    = document.getElementById('chatbotInput');
    const sendBtn  = document.getElementById('chatbotSend');
    const badge    = document.getElementById('chatbotBadge');
    const quickBox = document.getElementById('chatbotQuickReplies');

    let isOpen = false;
    let firstOpen = true;

    /* ── Toggle open/close ── */
    toggle.addEventListener('click', function () {
      isOpen = !isOpen;
      toggle.classList.toggle('open', isOpen);
      chatWin.classList.toggle('open', isOpen);

      if (isOpen) {
        badge.classList.add('hidden');
        input.focus();

        if (firstOpen) {
          firstOpen = false;
          showWelcome();
        }
      }
    });

    /* ── Scroll-to-bottom button ── */
    const scrollBtn = document.getElementById('chatbotScrollBtn');

    messages.addEventListener('scroll', function () {
      const distFromBottom = messages.scrollHeight - messages.scrollTop - messages.clientHeight;
      scrollBtn.style.display = distFromBottom > 80 ? 'flex' : 'none';
    });

    scrollBtn.addEventListener('click', function () {
      scrollToBottom();
      scrollBtn.style.display = 'none';
    });

    /* ── Arrow key scrolling in messages ── */
    messages.addEventListener('keydown', function (e) {
      if (e.key === 'ArrowUp') {
        e.preventDefault();
        messages.scrollBy({ top: -80, behavior: 'smooth' });
      } else if (e.key === 'ArrowDown') {
        e.preventDefault();
        messages.scrollBy({ top: 80, behavior: 'smooth' });
      }
    });

    /* ── Close on escape ── */
    document.addEventListener('keydown', function (e) {
      if (e.key === 'Escape' && isOpen) {
        toggle.click();
      }
    });

    /* ── Send message ── */
    function sendMessage() {
      const text = input.value.trim();
      if (!text) return;

      addMessage(text, 'user');
      input.value = '';
      input.style.height = 'auto';
      sendBtn.disabled = true;
      hideQuickReplies();

      // Show typing, then respond
      showTyping();
      setTimeout(function () {
        removeTyping();

        // Check for special stats query
        const lower = text.toLowerCase();
        if (lower.includes('combien') && (lower.includes('offre') || lower.includes('offres'))) {
          const stats = getOfferStats();
          let response = `📊 Actuellement, il y a **${stats.total} offre(s)** affichée(s) sur la page.\n\n`;
          const entries = Object.entries(stats.types);
          if (entries.length > 0) {
            response += 'Répartition par type :\n';
            entries.forEach(([type, count]) => {
              response += `• **${type}** : ${count} offre(s)\n`;
            });
          }
          addMessage(response, 'bot');
          showQuickReplies(['Comment postuler ?', 'Types de contrats']);
        } else {
          const match = findResponse(text);
          addMessage(match.response, 'bot');
          if (match.quickReplies) {
            showQuickReplies(match.quickReplies);
          }
        }
      }, typingDelay());
    }

    sendBtn.addEventListener('click', sendMessage);

    input.addEventListener('keydown', function (e) {
      if (e.key === 'Enter' && !e.shiftKey) {
        e.preventDefault();
        sendMessage();
      }
    });

    /* ── Auto-resize textarea ── */
    input.addEventListener('input', function () {
      sendBtn.disabled = !this.value.trim();
      this.style.height = 'auto';
      this.style.height = Math.min(this.scrollHeight, 100) + 'px';
    });

    /* ── Add a message bubble ── */
    function addMessage(text, sender) {
      const msg = document.createElement('div');
      msg.className = 'chatbot-msg ' + sender;

      const avatar = sender === 'bot' ? '🤖' : '👤';

      msg.innerHTML = `
        <div class="chatbot-msg-avatar">${avatar}</div>
        <div>
          <div class="chatbot-msg-bubble">${formatText(text)}</div>
          <span class="chatbot-msg-time">${getTime()}</span>
        </div>
      `;

      messages.appendChild(msg);
      scrollToBottom();
    }

    /* ── Typing indicator ── */
    function showTyping() {
      const typing = document.createElement('div');
      typing.className = 'chatbot-typing';
      typing.id = 'chatbotTyping';
      typing.innerHTML = `
        <div class="chatbot-msg-avatar">🤖</div>
        <div class="chatbot-typing-dots">
          <span></span><span></span><span></span>
        </div>
      `;
      messages.appendChild(typing);
      scrollToBottom();
    }

    function removeTyping() {
      const typing = document.getElementById('chatbotTyping');
      if (typing) typing.remove();
    }

    /* ── Quick replies ── */
    function showQuickReplies(replies) {
      quickBox.innerHTML = '';
      replies.forEach(function (text) {
        const btn = document.createElement('button');
        btn.className = 'chatbot-quick-btn';
        btn.textContent = text;
        btn.addEventListener('click', function () {
          input.value = text;
          sendMessage();
        });
        quickBox.appendChild(btn);
      });
      quickBox.style.display = 'flex';
    }

    function hideQuickReplies() {
      quickBox.style.display = 'none';
      quickBox.innerHTML = '';
    }

    /* ── Scroll to bottom ── */
    function scrollToBottom() {
      requestAnimationFrame(function () {
        messages.scrollTop = messages.scrollHeight;
      });
    }

    /* ── Welcome message ── */
    function showWelcome() {
      const stats = getOfferStats();
      showTyping();
      setTimeout(function () {
        removeTyping();
        addMessage(
          `Bonjour ! 👋 Je suis **${BOT_NAME}**, votre assistant Jobyfind.\n\nActuellement, **${stats.total} offre(s)** sont disponibles. Je peux vous aider à :\n\n• 🔍 Trouver des offres\n• 📋 Comprendre les types de contrats\n• 🚀 Vous guider pour postuler\n\nComment puis-je vous aider ?`,
          'bot'
        );
        showQuickReplies(['Voir les offres', 'Types de contrats', 'Comment postuler ?']);
      }, typingDelay());
    }
  }

  /* ── Start when DOM is ready ── */
  if (document.readyState === 'loading') {
    document.addEventListener('DOMContentLoaded', init);
  } else {
    init();
  }
})();
