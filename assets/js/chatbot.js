(function () {
  'use strict';

  const root = document.getElementById('siteChatbot');
  if (!root) return;

  const panel = document.getElementById('siteChatbotPanel');
  const toggle = document.getElementById('siteChatbotToggle');
  const closeBtn = document.getElementById('siteChatbotClose');
  const form = document.getElementById('siteChatbotForm');
  const input = document.getElementById('siteChatbotInput');
  const messages = document.getElementById('siteChatbotMessages');
  const sendBtn = document.getElementById('siteChatbotSend');

  const history = [];

  function apiUrl() {
    return (window.BASE_URL || '') + '/api/chatbot.php';
  }

  function setOpen(open) {
    panel.classList.toggle('d-none', !open);
    toggle.setAttribute('aria-expanded', open ? 'true' : 'false');
    if (open) {
      input.focus();
    }
  }

  function scrollToBottom() {
    messages.scrollTop = messages.scrollHeight;
  }

  function appendMessage(role, text) {
    const wrap = document.createElement('div');
    wrap.className = 'site-chatbot__message site-chatbot__message--' + (role === 'user' ? 'user' : 'bot');
    const bubble = document.createElement('div');
    bubble.className = 'site-chatbot__bubble';
    bubble.textContent = text;
    wrap.appendChild(bubble);
    messages.appendChild(wrap);
    scrollToBottom();
    return wrap;
  }

  function showTyping() {
    const wrap = document.createElement('div');
    wrap.className = 'site-chatbot__message site-chatbot__message--bot site-chatbot__message--typing';
    wrap.id = 'siteChatbotTyping';
    wrap.innerHTML =
      '<div class="site-chatbot__bubble">' +
        '<span class="site-chatbot__dot"></span>' +
        '<span class="site-chatbot__dot"></span>' +
        '<span class="site-chatbot__dot"></span>' +
      '</div>';
    messages.appendChild(wrap);
    scrollToBottom();
  }

  function hideTyping() {
    document.getElementById('siteChatbotTyping')?.remove();
  }

  function setLoading(loading) {
    input.disabled = loading;
    sendBtn.disabled = loading;
  }

  toggle.addEventListener('click', () => {
    setOpen(panel.classList.contains('d-none'));
  });

  closeBtn.addEventListener('click', () => setOpen(false));

  form.addEventListener('submit', async (e) => {
    e.preventDefault();
    const text = input.value.trim();
    if (!text) return;

    appendMessage('user', text);
    history.push({ role: 'user', content: text });
    input.value = '';
    setLoading(true);
    showTyping();

    try {
      const res = await fetch(apiUrl(), {
        method: 'POST',
        headers: { 'Content-Type': 'application/json' },
        body: JSON.stringify({
          message: text,
          history: history.slice(0, -1),
        }),
      });

      const data = await res.json();
      hideTyping();

      if (!data.ok) {
        const err = data.message || 'Sorry, something went wrong.';
        appendMessage('bot', err);
        window.showToast?.(err, 'danger');
        return;
      }

      const reply = String(data.reply || '').trim();
      appendMessage('bot', reply);
      history.push({ role: 'assistant', content: reply });
    } catch (err) {
      hideTyping();
      const msg = 'Could not reach the assistant. Please check your connection and try again.';
      appendMessage('bot', msg);
      window.showToast?.(msg, 'danger');
    } finally {
      setLoading(false);
      input.focus();
    }
  });
})();
