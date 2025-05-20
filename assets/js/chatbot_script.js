document.addEventListener('DOMContentLoaded', function() {
    console.log("Chatbot script loaded.");

    const chatEmbedWrapper = document.getElementById('gfs-chatbot-wrapper');
    if (!chatEmbedWrapper) {
        console.error("CRITICAL: Chatbot wrapper 'gfs-chatbot-wrapper' not found. Chatbot will not initialize.");
        return;
    }

    const chatToggleButton = chatEmbedWrapper.querySelector('#chat-toggle');
    const chatContainer = chatEmbedWrapper.querySelector('#chat-container');
    const closeChatButton = chatEmbedWrapper.querySelector('#close-chat-btn');
    const clearChatButton = chatEmbedWrapper.querySelector('#clear-chat-btn');
    const chatMessagesContainer = chatEmbedWrapper.querySelector('#chat-messages');
    const chatForm = chatEmbedWrapper.querySelector('#chat-form');
    const userInput = chatEmbedWrapper.querySelector('#user-input');
    const sendButton = chatEmbedWrapper.querySelector('#send-btn');
    const initialBotTimeElement = chatEmbedWrapper.querySelector('#initial-bot-time');


    if (!chatToggleButton || !chatContainer || !closeChatButton || !clearChatButton || !chatMessagesContainer || !chatForm || !userInput || !sendButton) {
        console.error("One or more essential chatbot DOM elements are missing. Check IDs in footer.php and this script.");
        return;
    }

    let isChatOpen = false;

    chatToggleButton.addEventListener('click', toggleChat);
    closeChatButton.addEventListener('click', toggleChat);
    chatForm.addEventListener('submit', handleFormSubmit);
    userInput.addEventListener('keydown', handleKeydown);
    userInput.addEventListener('input', handleInput);
    clearChatButton.addEventListener('click', clearChat);

    if (initialBotTimeElement) {
        initialBotTimeElement.textContent = formatTime(new Date());
    }

    function toggleChat() {
        isChatOpen = !isChatOpen;
        chatEmbedWrapper.classList.toggle('open', isChatOpen);
        if (isChatOpen) {
            setTimeout(() => userInput.focus(), 300);
            scrollToBottom();
        }
    }

    function handleFormSubmit(event) {
        event.preventDefault();
        sendMessage();
    }

    function sendMessage() {
        const messageText = userInput.value.trim();
        if (messageText.length === 0 || sendButton.disabled) return;

        addUserMessage(messageText);
        userInput.value = '';
        handleInput();
        userInput.style.height = '36px';

        showTypingIndicator();
        // scrollToBottom(); // Will be called by addMessageToUI

        sendButton.disabled = true;

        fetch('chatbot_handler.php', {
            method: 'POST',
            headers: { 'Content-Type': 'application/json', 'Accept': 'application/json' },
            body: JSON.stringify({ action: 'send_message', message: messageText })
        })
        .then(response => {
            if (!response.ok) {
                 return response.text().then(text => { throw new Error(`HTTP error ${response.status}: ${text || 'Server error'}`); });
            }
            const contentType = response.headers.get("content-type");
            if (contentType && contentType.indexOf("application/json") !== -1) { return response.json(); }
            else { return response.text().then(text => { throw new Error(`Unexpected response type: ${contentType}. Content: ${text}`); }); }
        })
        .then(data => {
            removeTypingIndicator();
            if (data && data.status === 'success' && (data.message_html || (data.text_html && data.text_html !== 'Error: No message content.'))) { // Check for message_html from PHP
                 addBotMessage(data); // Pass the whole data object
            } else {
                addBotMessage({ message_html: '<p>Sorry, I encountered an issue: ' + (data.message_html || data.message || 'Invalid response.') + '</p>'});
            }
        })
        .catch(error => {
            console.error('Error sending/receiving message:', error);
            removeTypingIndicator();
            addBotMessage({ message_html: `<p>Sorry, there was a problem connecting. ${error.message}</p>`});
        })
        .finally(() => {
             handleInput();
             // scrollToBottom(); // Called by addMessageToUI
             userInput.focus();
        });
    }

    function handleKeydown(event) {
        if (event.key === 'Enter' && !event.shiftKey) {
            event.preventDefault();
            sendMessage();
        }
    }

    function handleInput() {
        userInput.style.height = 'auto';
        let newHeight = userInput.scrollHeight;
        const maxHeight = 70;
        if (newHeight > maxHeight) { newHeight = maxHeight; userInput.style.overflowY = 'auto'; }
        else { userInput.style.overflowY = 'hidden'; }
        userInput.style.height = newHeight + 'px';
        sendButton.disabled = (userInput.value.trim().length === 0);
    }

    function clearChat() {
         if (confirm("Are you sure you want to refresh the chat history?")) {
            const botName = chatEmbedWrapper.querySelector('.chat-header .logo h1')?.textContent || 'GFS Help Desk';
            const welcomeMessageHtml = `<p>Hi there! I'm the ${botName}. How can I assist you today?</p>`;
            
            const welcomeMessageDiv = document.createElement('div'); // Reconstruct full message for UI
            welcomeMessageDiv.classList.add('message', 'bot-message');
            let avatarHtml = `<div class="message-avatar"><img src="assets/images/bot-avatar.jpg" alt="Bot"></div>`;
            const contentHtml = `
                <div class="message-content">
                    <div class="message-text">
                        ${welcomeMessageHtml}
                    </div>
                    <div class="message-time">${formatTime(new Date())}</div>
                </div>`;
            welcomeMessageDiv.innerHTML = avatarHtml + contentHtml;

            if (chatMessagesContainer) {
                chatMessagesContainer.innerHTML = '';
                chatMessagesContainer.appendChild(welcomeMessageDiv);
            }
            scrollToBottom();

            fetch('chatbot_handler.php', {
                method: 'POST',
                headers: { 'Content-Type': 'application/json' },
                body: JSON.stringify({ action: 'clear_history' })
            })
            .then(response => response.json())
            .then(data => { if (data.status !== 'success') { console.warn("Failed to clear server history:", data.message); } })
            .catch(error => console.error('Error clearing history:', error));
        }
    }
    
    function escapeHtml(unsafe) {
        if (typeof unsafe !== 'string') return '';
        return unsafe
             .replace(/&/g, "&")
             .replace(/</g, "<")
             .replace(/>/g, ">")
             .replace(/"/g, "")
             .replace(/'/g, "'");
    }

    function addMessageToUI(sender, finalHtmlContentForBubble) {
        if (!chatMessagesContainer) return;
        const messageDiv = document.createElement('div');
        messageDiv.classList.add('message', sender === 'user' ? 'user-message' : 'bot-message');
        let avatarHtml = '';
        if (sender === 'bot') {
            avatarHtml = `<div class="message-avatar"><img src="assets/images/bot-avatar.jpg" alt="Bot"></div>`;
        }
        const contentHtml = `
            <div class="message-content">
                <div class="message-text">
                    ${finalHtmlContentForBubble}
                </div>
                <div class="message-time">${formatTime(new Date())}</div>
            </div>`;
        messageDiv.innerHTML = (sender === 'bot') ? (avatarHtml + contentHtml) : (contentHtml + avatarHtml);
        chatMessagesContainer.appendChild(messageDiv);
        scrollToBottom();
    }

    function addUserMessage(text) {
        const escapedText = text.replace(/</g, "<").replace(/>/g, ">");
        const userMessageHtml = `<p>${escapedText.replace(/\n/g, '<br />')}</p>`;
        addMessageToUI('user', userMessageHtml);
    }

    function addBotMessage(botData) { // botData is the object from PHP
        let displayHtml = "<p>Error: No message content received.</p>";

        if (botData && typeof botData.message_html === 'string') {
            displayHtml = `<p>${botData.message_html}</p>`; // message_html from PHP is already formatted
        } else if (botData && typeof botData.text === 'string') { // Fallback if only raw text is there
            displayHtml = `<p>${botData.text.replace(/\n/g, '<br />')}</p>`;
        }


        if (botData.page_link) {
            const linkText = botData.link_text || 'Click for more details';
            let href = botData.page_link;

            // If your PHP sends page names like "offices" and you need "index.php?page=offices"
            // This check assumes internal links won't start with http and don't already contain index.php?page=
            if (!href.startsWith('http') && !href.includes('index.php?page=')) {
               href = `index.php?page=${escapeHtml(href)}`;
            }

            displayHtml += `<a href="${escapeHtml(href)}" target="_blank" class="chatbot-info-link">${escapeHtml(linkText)}</a>`;
        }
        addMessageToUI('bot', displayHtml);
    }

    function showTypingIndicator() {
        if (chatMessagesContainer.querySelector('.typing-indicator-container')) return;
        const typingDiv = document.createElement('div');
        typingDiv.classList.add('message', 'bot-message', 'typing-indicator-container');
        typingDiv.innerHTML = `<div class="message-avatar"><img src="assets/images/bot-avatar.jpg" alt="Bot"></div><div class="typing-indicator"><span></span><span></span><span></span></div>`;
        chatMessagesContainer.appendChild(typingDiv);
        scrollToBottom();
    }

    function removeTypingIndicator() {
        const indicator = chatMessagesContainer?.querySelector('.typing-indicator-container');
        if (indicator) indicator.remove();
    }

    function scrollToBottom() {
        setTimeout(() => { if(chatMessagesContainer) { chatMessagesContainer.scrollTop = chatMessagesContainer.scrollHeight; } }, 50);
    }

    function formatTime(date) {
        if (!(date instanceof Date)) date = new Date();
        let hours = date.getHours(); const minutes = date.getMinutes().toString().padStart(2, '0');
        const ampm = hours >= 12 ? 'PM' : 'AM'; hours = hours % 12; hours = hours ? hours : 12;
        return `${hours}:${minutes} ${ampm}`;
    }

    if (userInput && sendButton) handleInput();
    scrollToBottom();
    console.log("Chatbot script initialized and event listeners attached.");
});