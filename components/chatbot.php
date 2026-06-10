<div id="siteChatbot" class="site-chatbot" aria-live="polite">
  <div id="siteChatbotPanel" class="site-chatbot__panel d-none" role="dialog" aria-label="Digital Tutor Assistant">
    <div class="site-chatbot__header">
      <div class="d-flex align-items-center gap-2">
        <span class="site-chatbot__avatar"><i class="bi bi-stars"></i></span>
        <div>
          <div class="site-chatbot__title">Digital Tutor Assistant</div>
          <div class="site-chatbot__subtitle">Ask about courses, enrollment &amp; learning</div>
        </div>
      </div>
      <button type="button" class="btn btn-sm btn-link text-white site-chatbot__close" id="siteChatbotClose" aria-label="Close chat">
        <i class="bi bi-x-lg"></i>
      </button>
    </div>
    <div class="site-chatbot__messages" id="siteChatbotMessages">
      <div class="site-chatbot__message site-chatbot__message--bot">
        <div class="site-chatbot__bubble">
          Hi! I can help with <?= htmlspecialchars(SITE_NAME) ?> — finding courses, enrolling, your account, and how to use the platform. What would you like to know?
        </div>
      </div>
    </div>
    <form class="site-chatbot__form" id="siteChatbotForm">
      <div class="input-group">
        <input type="text" class="form-control" id="siteChatbotInput" placeholder="Ask a question..." maxlength="2000" autocomplete="off" required>
        <button class="btn btn-primary" type="submit" id="siteChatbotSend" aria-label="Send message">
          <i class="bi bi-send-fill"></i>
        </button>
      </div>
    </form>
  </div>
  <button type="button" class="site-chatbot__toggle" id="siteChatbotToggle" aria-label="Open chat assistant" aria-expanded="false">
    <i class="bi bi-chat-dots-fill"></i>
    <span class="site-chatbot__toggle-label">Help</span>
  </button>
</div>
