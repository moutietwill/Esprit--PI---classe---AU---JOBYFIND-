let activeVoiceRecognition = null;
let activeVoiceButton = null;

function toggleVoiceComment(btn, postId) {
    // alert("Microphone cliqué ! ID: " + postId);
    const Recognition = window.SpeechRecognition || window.webkitSpeechRecognition || null;
    const inputWrapper = btn.closest('.cmt-input-wrapper');
    const inputElement = inputWrapper ? inputWrapper.querySelector('.comment-input') : null;

    if (!inputElement) {
        alert("Erreur: Champ de texte introuvable.");
        return;
    }

    if (!Recognition) {
        alert("La reconnaissance vocale n'est pas supportée par votre navigateur (Utilisez Chrome).");
        return;
    }

    if (activeVoiceRecognition && activeVoiceButton === btn) {
        stopActiveVoiceComment();
        return;
    }

    // Microphone access check
    navigator.mediaDevices.getUserMedia({ audio: true })
        .then(function(stream) {
            // Analyser pour retour visuel
            const audioContext = new (window.AudioContext || window.webkitAudioContext)();
            const analyser = audioContext.createAnalyser();
            const source = audioContext.createMediaStreamSource(stream);
            source.connect(analyser);
            analyser.fftSize = 256;
            const bufferLength = analyser.frequencyBinCount;
            const dataArray = new Uint8Array(bufferLength);

            const checkAudio = () => {
                if (!activeVoiceRecognition) {
                    stream.getTracks().forEach(t => t.stop());
                    audioContext.close();
                    return;
                }
                analyser.getByteFrequencyData(dataArray);
                let sum = 0;
                for (let i = 0; i < bufferLength; i++) sum += dataArray[i];
                let average = sum / bufferLength;
                if (average > 10) {
                    updateVoiceStatus(postId, 'Volume détecté (' + Math.round(average) + ')...');
                    btn.style.transform = 'translateY(-50%) scale(' + (1 + average/100) + ')';
                }
                requestAnimationFrame(checkAudio);
            };
            checkAudio();
        })
        .catch(function(err) {
            alert("ERREUR MICRO: " + err.name + " - " + err.message);
        });

    stopActiveVoiceComment();

    const recognition = new Recognition();
    recognition.lang = 'fr-FR';
    recognition.continuous = false; // Restarting manually is more reliable
    recognition.interimResults = true;
    recognition.maxAlternatives = 1;

    activeVoiceRecognition = recognition;
    activeVoiceButton = btn;

    let restartTimer = setTimeout(() => {
        if (activeVoiceRecognition === recognition) {
            recognition.stop();
        }
    }, 5000);

    recognition.onstart = () => {
        btn.classList.add('listening');
        const icon = btn.querySelector('i');
        if (icon) icon.className = 'fas fa-stop';
        updateVoiceStatus(postId, 'Écoute active (5s cycles)...');
    };

    recognition.onaudiostart = () => {
        updateVoiceStatus(postId, 'Audio détecté ! Reconnaissance en cours...');
    };

    recognition.onsoundstart = () => {
        updateVoiceStatus(postId, 'Son capturé !');
    };

    recognition.onresult = (event) => {
        let transcript = '';
        for (let i = event.resultIndex; i < event.results.length; i++) {
            transcript += event.results[i][0].transcript;
        }
        inputElement.value = transcript;
        updateVoiceStatus(postId, 'J\'entends: ' + transcript);
        btn.style.boxShadow = '0 0 15px rgba(45,121,255,0.8)';
        setTimeout(() => { btn.style.boxShadow = '0 2px 8px rgba(0,0,0,0.2)'; }, 200);
    };

    recognition.onerror = (event) => {
        if (event.error !== 'no-speech') {
            updateVoiceStatus(postId, 'Erreur: ' + event.error, true);
        }
    };

    recognition.onend = () => {
        clearTimeout(restartTimer);
        if (activeVoiceRecognition === recognition) {
            // Auto-restart if we didn't stop manually
            try {
                recognition.start();
                restartTimer = setTimeout(() => {
                    if (activeVoiceRecognition === recognition) recognition.stop();
                }, 5000);
            } catch (e) {
                resetVoiceButton(btn);
                activeVoiceRecognition = null;
                activeVoiceButton = null;
            }
        } else {
            resetVoiceButton(btn);
        }
    };

    try {
        recognition.start();
    } catch (e) {
        alert("Erreur au démarrage: " + e.message);
    }
}

function stopActiveVoiceComment() {
    const recognition = activeVoiceRecognition;
    const button = activeVoiceButton;
    
    activeVoiceRecognition = null;
    activeVoiceButton = null;

    if (recognition) {
        recognition.stop();
    }
    if (button) {
        resetVoiceButton(button);
    }
}

function resetVoiceButton(btn) {
    if (!btn) return;
    btn.classList.remove('listening');
    btn.setAttribute('aria-pressed', 'false');
    const icon = btn.querySelector('i');
    if (icon) {
        icon.classList.remove('fa-stop');
        icon.classList.add('fa-microphone');
    }
}

function updateVoiceStatus(postId, message, isError = false) {
    const status = document.getElementById(`voiceStatus-${postId}`);
    if (!status) return;
    status.textContent = message;
    status.style.color = isError ? '#ef4444' : 'var(--primary)';
}

function mergeVoiceText(initialText, voiceText) {
    const cleanVoiceText = (voiceText || '').trim();
    if (!cleanVoiceText) return initialText;
    if (!initialText) return cleanVoiceText;
    return `${initialText} ${cleanVoiceText}`;
}

function normalizeVoiceText(text) {
    return (text || '').replace(/\s+/g, ' ').trim();
}

function parseVoiceCommand(text) {
    const cleanText = normalizeVoiceText(text);
    const clearPattern = /^(effacer|efface|supprimer|supprime|vider|vide)( le commentaire| mon commentaire)?$/i;
    const correctPattern = /\b(corriger|corrige|corrigez)( le commentaire| mon commentaire)?$/i;
    const submitPattern = /\b(envoyer|envoie|publier|publie|poster|poste|valider|valide)( le commentaire| mon commentaire)?$/i;

    if (clearPattern.test(cleanText)) {
        return { text: '', clear: true, correct: false, submit: false };
    }

    const shouldCorrect = correctPattern.test(cleanText);
    const shouldSubmit = submitPattern.test(cleanText);
    const cleanedText = normalizeVoiceText(
        cleanText
            .replace(correctPattern, '')
            .replace(submitPattern, '')
    );

    return { text: cleanedText, clear: false, correct: shouldCorrect, submit: shouldSubmit };
}

function applyCommentCommand(commandName, inputElement, postId, voiceText = '') {
    if (commandName === 'clear') {
        inputElement.value = '';
    } else if (commandName === 'correct') {
        inputElement.value = voiceText;
    }
}

function runCommentCommand(btn, postId, command) {
    const container = btn.closest('.cmt-input-container');
    const inputElement = container.querySelector('.comment-input');
    if (command === 'send') {
        sendComment(inputElement, postId);
    } else if (command === 'clear') {
        inputElement.value = '';
    }
}

function submitComment(event, inputElement, postId) {
    if (event.key === 'Enter') {
        sendComment(inputElement, postId);
    }
}

function sendComment(inputElement, postId) {
    const errDiv = document.getElementById(`commentError-${postId}`);
    const content = inputElement.value.trim();
    if (errDiv) errDiv.style.display = 'none';

    if (content.length === 0) {
        if (errDiv) {
            errDiv.textContent = "Le commentaire ne peut pas être vide.";
            errDiv.style.display = 'block';
        } else { alert("Le commentaire ne peut pas être vide."); }
        return;
    }

    inputElement.disabled = true;
    const formData = new FormData();
    formData.append('post_id', postId);
    formData.append('content', content);

    fetch('../ajax_add_comment.php', {
        method: 'POST',
        body: formData
    })
    .then(response => response.json())
    .then(data => {
        inputElement.disabled = false;
        if (data.success) {
            inputElement.value = '';
            location.reload();
        } else {
            if (errDiv) {
                errDiv.textContent = "Erreur: " + data.message;
                errDiv.style.display = 'block';
            } else { alert("Erreur: " + data.message); }
        }
    })
    .catch(error => {
        inputElement.disabled = false;
        alert("Erreur de connexion au serveur.");
    });
}

// ============================================
// FORM VALIDATIONS (RECONSTRUCTED)
// ============================================

document.addEventListener('DOMContentLoaded', function() {
    // 1. Formation / Blog Form
    const formationForm = document.getElementById('formationForm') || document.getElementById('postForm');
    if (formationForm) {
        formationForm.addEventListener('submit', function(e) {
            let isValid = true;
            const title = document.getElementById('titleInput') || document.getElementById('postTitleInput');
            const category = document.getElementById('categoryInput') || document.getElementById('postCategoryInput');
            const content = document.getElementById('contentInput') || document.getElementById('postContentInput');
            
            const titleErr = document.getElementById('titleError') || document.getElementById('postTitleError');
            const catErr = document.getElementById('categoryError') || document.getElementById('postCategoryError');
            const contErr = document.getElementById('contentError') || document.getElementById('postContentError');

            if (title && title.value.trim().length < 3) {
                showError(titleErr.id, "Le titre doit faire au moins 3 caractères.", title);
                isValid = false;
            } else if(title) { hideError(titleErr.id, title); }

            if (category && category.value === "") {
                showError(catErr.id, "Veuillez choisir une catégorie.", category);
                isValid = false;
            } else if(category) { hideError(catErr.id, category); }

            if (content && content.value.trim().length < 10) {
                showError(contErr.id, "La description doit faire au moins 10 caractères.", content);
                isValid = false;
            } else if(content) { hideError(contErr.id, content); }

            if (!isValid) e.preventDefault();
        });
    }

    // 2. Story Form
    const storyForm = document.getElementById('storyForm');
    if (storyForm) {
        storyForm.addEventListener('submit', function(e) {
            let isValid = true;
            const title = document.getElementById('storyTitleInput');
            const content = document.getElementById('storyContentInput');
            const starts = document.getElementById('storyStartsInput');
            const expires = document.getElementById('storyExpiresInput');

            if (title && title.value.trim().length < 3) {
                showError('storyTitleError', "Le titre doit faire au moins 3 caractères.", title);
                isValid = false;
            } else if(title) { hideError('storyTitleError', title); }

            if (content && content.value.trim().length < 10) {
                showError('storyContentError', "Le texte doit faire au moins 10 caractères.", content);
                isValid = false;
            } else if(content) { hideError('storyContentError', content); }

            if (starts && !starts.value) {
                showError('storyDateError', "Veuillez choisir une date de début.", starts);
                isValid = false;
            } else if (expires && !expires.value) {
                showError('storyDateError', "Veuillez choisir une date de fin.", expires);
                isValid = false;
            } else if (starts && expires && new Date(expires.value) <= new Date(starts.value)) {
                showError('storyDateError', "La fin doit être après le début.", expires);
                isValid = false;
            } else if(starts) { hideError('storyDateError', starts); }

            if (!isValid) e.preventDefault();
        });
    }

    // 3. Category Form
    const categoryForm = document.getElementById('categoryForm');
    if (categoryForm) {
        categoryForm.addEventListener('submit', function(e) {
            const name = document.getElementById('catNameInput');
            if (name.value.trim().length < 3) {
                showError('catNameError', "Le nom doit faire au moins 3 caractères.", name);
                e.preventDefault();
            } else { hideError('catNameError', name); }
        });
    }
});

function showError(id, msg, input) {
    const err = document.getElementById(id);
    if (err) {
        err.textContent = msg;
        err.style.display = 'block';
    }
    if (input) input.style.borderColor = '#ef4444';
}

function hideError(id, input) {
    const err = document.getElementById(id);
    if (err) err.style.display = 'none';
    if (input) input.style.borderColor = '#e5e7eb';
}

// 4. Like/Rate Logic
function toggleLike(btn, postId) {
    fetch('../ajax_toggle_like.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({ post_id: postId })
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            const countSpan = btn.querySelector('.engagement-count');
            if (countSpan) countSpan.textContent = data.count;
            if (data.liked) {
                btn.classList.remove('unliked');
            } else {
                btn.classList.add('unliked');
            }
        }
    });
}

function ratePost(postId, rating) {
    fetch('../ajax_rate_post.php', {
        method: 'POST',
        headers: { 'Content-Type': 'application/x-www-form-urlencoded' },
        body: `post_id=${postId}&rating=${rating}`
    })
    .then(r => r.json())
    .then(data => {
        if (data.success) {
            alert(data.message);
            location.reload();
        }
    });
}

function toggleComments(btn, postId) {
    const cmtSection = document.getElementById(`comments-${postId}`);
    if (cmtSection) {
        if (cmtSection.style.display === 'none' || cmtSection.style.display === '') {
            cmtSection.style.display = 'block';
        } else {
            cmtSection.style.display = 'none';
        }
    }
}
