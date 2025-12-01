/**
 * PharmaVault AI Chatbot
 * Intelligent customer support assistant
 */

class PharmaChatbot {
    constructor() {
        this.messages = [];
        this.isTyping = false;
        this.userName = this.getUserName();
        this.knowledgeBase = this.initializeKnowledgeBase();
        this.init();
    }

    getUserName() {
        // Try to get user name from page or session
        const nameElement = document.querySelector('.user-name, .customer-name');
        return nameElement ? nameElement.textContent.trim() : 'there';
    }

    initializeKnowledgeBase() {
        return {
            greetings: ['hello', 'hi', 'hey', 'good morning', 'good afternoon', 'good evening'],

            faqs: {
                'prescription': {
                    keywords: ['prescription', 'rx', 'doctor note', 'medical prescription'],
                    answer: "📋 To upload a prescription:\n\n1. Go to 'My Prescriptions' in your dashboard\n2. Click 'Upload Prescription'\n3. Add images of your prescription (up to 3)\n4. Fill in doctor details\n5. Submit for verification\n\nVerification usually takes 24-48 hours. You'll be notified once approved!"
                },
                'order_status': {
                    keywords: ['order', 'track', 'delivery', 'shipping', 'where is my order'],
                    answer: "📦 To check your order status:\n\n1. Visit 'My Orders' in your dashboard\n2. Click on the order you want to track\n3. View delivery status and estimated arrival\n\nYou can also contact the pharmacy directly for updates!"
                },
                'payment': {
                    keywords: ['payment', 'pay', 'paystack', 'credit card', 'mobile money'],
                    answer: "💳 We accept payments via Paystack:\n\n✓ Credit/Debit Cards (Visa, Mastercard)\n✓ Mobile Money\n✓ Bank Transfer\n\nAll payments are secure and encrypted. You'll receive instant confirmation!"
                },
                'delivery': {
                    keywords: ['delivery fee', 'shipping', 'delivery time', 'when will i receive'],
                    answer: "🚚 Delivery Information:\n\n• Pharmacy delivery: Varies by pharmacy\n• Rider delivery: Available for most areas\n• Pickup: Free at pharmacy location\n\nDelivery fees and times depend on your location and selected pharmacy."
                },
                'returns': {
                    keywords: ['return', 'refund', 'cancel order', 'wrong item'],
                    answer: "↩️ Returns & Refunds:\n\nMedications cannot be returned once delivered due to safety regulations. However:\n\n• Wrong items: Contact pharmacy immediately\n• Damaged products: Request replacement\n• Canceled orders: Refund processed in 5-7 days\n\nContact the pharmacy or our support team for assistance!"
                },
                'account': {
                    keywords: ['account', 'profile', 'password', 'email', 'update profile'],
                    answer: "👤 Account Management:\n\n• Update Profile: Go to 'My Profile'\n• Change Password: Profile > Security Settings\n• Update Email: Contact support\n• Delete Account: Contact support\n\nKeep your account secure by using a strong password!"
                },
                'search': {
                    keywords: ['find product', 'search', 'looking for', 'where can i find'],
                    answer: "🔍 Finding Products:\n\n1. Use the search bar at the top\n2. Browse by categories\n3. Filter by pharmacy, price, or brand\n4. Check product reviews\n\nNeed a specific medication? Try searching by generic name!"
                },
                'privacy': {
                    keywords: ['privacy', 'data', 'secure', 'confidential', 'prescription privacy'],
                    answer: "🔒 Your Privacy Matters:\n\n• All data is encrypted\n• Prescriptions are confidential\n• Control who can view your prescriptions\n• We never share your medical information\n\nYou can manage prescription privacy in 'My Prescriptions'!"
                },
                'contact': {
                    keywords: ['contact', 'support', 'help', 'customer service', 'phone number'],
                    answer: "📞 Contact Support:\n\n📧 Email: support@pharmavault.com\n📱 Phone: +233 XX XXX XXXX\n⏰ Hours: Mon-Sat, 8AM-6PM\n\nOr use the contact form on our website. We typically respond within 24 hours!"
                }
            },

            quickReplies: [
                { text: '📋 Upload Prescription', action: 'prescription' },
                { text: '📦 Track Order', action: 'order_status' },
                { text: '💳 Payment Methods', action: 'payment' },
                { text: '🚚 Delivery Info', action: 'delivery' }
            ]
        };
    }

    init() {
        this.createChatbotUI();
        this.attachEventListeners();
        this.showWelcomeMessage();
    }

    createChatbotUI() {
        const chatbotHTML = `
            <!-- Chatbot Toggle Button -->
            <button class="chatbot-toggle" id="chatbotToggle" aria-label="Open chat">
                <i class="fas fa-comments"></i>
                <span class="chatbot-badge" id="chatbotBadge" style="display: none;">1</span>
            </button>

            <!-- Chatbot Window -->
            <div class="chatbot-window" id="chatbotWindow">
                <!-- Header -->
                <div class="chatbot-header">
                    <div class="chatbot-header-info">
                        <div class="chatbot-avatar">💊</div>
                        <div class="chatbot-title">
                            <h3>PharmaVault Assistant</h3>
                            <div class="chatbot-status">
                                <span class="status-dot"></span>
                                Online
                            </div>
                        </div>
                    </div>
                    <button class="chatbot-close" id="chatbotClose" aria-label="Close chat">
                        <i class="fas fa-times"></i>
                    </button>
                </div>

                <!-- Messages Area -->
                <div class="chatbot-messages" id="chatbotMessages"></div>

                <!-- Input Area -->
                <div class="chatbot-input-area">
                    <input
                        type="text"
                        class="chatbot-input"
                        id="chatbotInput"
                        placeholder="Type your message..."
                        autocomplete="off"
                    >
                    <button class="chatbot-send-btn" id="chatbotSend" aria-label="Send message">
                        <i class="fas fa-paper-plane"></i>
                    </button>
                </div>

                <!-- Footer -->
                <div class="chatbot-footer">
                    Powered by PharmaVault AI
                </div>
            </div>
        `;

        document.body.insertAdjacentHTML('beforeend', chatbotHTML);
    }

    attachEventListeners() {
        const toggle = document.getElementById('chatbotToggle');
        const close = document.getElementById('chatbotClose');
        const send = document.getElementById('chatbotSend');
        const input = document.getElementById('chatbotInput');

        toggle.addEventListener('click', () => this.toggleChat());
        close.addEventListener('click', () => this.toggleChat());
        send.addEventListener('click', () => this.sendMessage());
        input.addEventListener('keypress', (e) => {
            if (e.key === 'Enter') this.sendMessage();
        });
    }

    toggleChat() {
        const window = document.getElementById('chatbotWindow');
        const toggle = document.getElementById('chatbotToggle');
        const badge = document.getElementById('chatbotBadge');

        window.classList.toggle('active');
        toggle.classList.toggle('active');

        if (window.classList.contains('active')) {
            badge.style.display = 'none';
            document.getElementById('chatbotInput').focus();
            this.scrollToBottom();
        }
    }

    showWelcomeMessage() {
        setTimeout(() => {
            this.addBotMessage(`Hello ${this.userName}! 👋 Welcome to PharmaVault!\n\nI'm your virtual assistant. How can I help you today?`);
            this.showQuickReplies();
            this.showNotificationBadge();
        }, 500);
    }

    showNotificationBadge() {
        const badge = document.getElementById('chatbotBadge');
        const window = document.getElementById('chatbotWindow');

        if (!window.classList.contains('active')) {
            badge.style.display = 'flex';
        }
    }

    sendMessage() {
        const input = document.getElementById('chatbotInput');
        const message = input.value.trim();

        if (!message || this.isTyping) return;

        this.addUserMessage(message);
        input.value = '';

        // Process message and generate response
        setTimeout(() => {
            this.processMessage(message);
        }, 800);
    }

    addUserMessage(text) {
        const time = this.getCurrentTime();
        const messageHTML = `
            <div class="chat-message user">
                <div class="message-avatar user">
                    <i class="fas fa-user"></i>
                </div>
                <div class="message-content">
                    <div class="message-bubble user">${this.escapeHtml(text)}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;

        this.appendMessage(messageHTML);
        this.messages.push({ type: 'user', text, time });
    }

    addBotMessage(text) {
        const time = this.getCurrentTime();
        const formattedText = text.replace(/\n/g, '<br>');
        const messageHTML = `
            <div class="chat-message bot">
                <div class="message-avatar bot">💊</div>
                <div class="message-content">
                    <div class="message-bubble bot">${formattedText}</div>
                    <div class="message-time">${time}</div>
                </div>
            </div>
        `;

        this.appendMessage(messageHTML);
        this.messages.push({ type: 'bot', text, time });
    }

    showTypingIndicator() {
        this.isTyping = true;
        const typingHTML = `
            <div class="chat-message bot" id="typingIndicator">
                <div class="message-avatar bot">💊</div>
                <div class="typing-indicator">
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                    <div class="typing-dot"></div>
                </div>
            </div>
        `;
        this.appendMessage(typingHTML);
    }

    hideTypingIndicator() {
        const indicator = document.getElementById('typingIndicator');
        if (indicator) indicator.remove();
        this.isTyping = false;
    }

    showQuickReplies() {
        const quickRepliesHTML = `
            <div class="quick-replies" id="quickReplies">
                ${this.knowledgeBase.quickReplies.map(reply =>
                    `<button class="quick-reply-btn" onclick="chatbot.handleQuickReply('${reply.action}')">${reply.text}</button>`
                ).join('')}
            </div>
        `;

        const lastMessage = document.querySelector('.chat-message.bot:last-child .message-content');
        if (lastMessage && !document.getElementById('quickReplies')) {
            lastMessage.insertAdjacentHTML('beforeend', quickRepliesHTML);
        }
    }

    handleQuickReply(action) {
        // Remove quick replies
        const quickReplies = document.getElementById('quickReplies');
        if (quickReplies) quickReplies.remove();

        // Find and show answer
        const faq = this.knowledgeBase.faqs[action];
        if (faq) {
            this.showTypingIndicator();
            setTimeout(() => {
                this.hideTypingIndicator();
                this.addBotMessage(faq.answer);
                this.scrollToBottom();
            }, 1000);
        }
    }

    processMessage(message) {
        const lowerMessage = message.toLowerCase();

        // Check for greetings
        if (this.knowledgeBase.greetings.some(greeting => lowerMessage.includes(greeting))) {
            this.showTypingIndicator();
            setTimeout(() => {
                this.hideTypingIndicator();
                this.addBotMessage(`Hello! 😊 How can I assist you with your pharmacy needs today?`);
                this.showQuickReplies();
                this.scrollToBottom();
            }, 1000);
            return;
        }

        // Check FAQs
        for (const [key, faq] of Object.entries(this.knowledgeBase.faqs)) {
            if (faq.keywords.some(keyword => lowerMessage.includes(keyword))) {
                this.showTypingIndicator();
                setTimeout(() => {
                    this.hideTypingIndicator();
                    this.addBotMessage(faq.answer);
                    this.scrollToBottom();
                }, 1200);
                return;
            }
        }

        // Default response
        this.showTypingIndicator();
        setTimeout(() => {
            this.hideTypingIndicator();
            this.addBotMessage(`I understand you're asking about "${message}".\n\nI'm still learning! For specific inquiries, please:\n\n📧 Email: support@pharmavault.com\n📱 Call: +233 XX XXX XXXX\n\nOr try one of these common topics:`);
            this.showQuickReplies();
            this.scrollToBottom();
        }, 1000);
    }

    appendMessage(html) {
        const messagesContainer = document.getElementById('chatbotMessages');
        messagesContainer.insertAdjacentHTML('beforeend', html);
        this.scrollToBottom();
    }

    scrollToBottom() {
        const messagesContainer = document.getElementById('chatbotMessages');
        setTimeout(() => {
            messagesContainer.scrollTop = messagesContainer.scrollHeight;
        }, 100);
    }

    getCurrentTime() {
        const now = new Date();
        return now.toLocaleTimeString('en-US', { hour: '2-digit', minute: '2-digit' });
    }

    escapeHtml(text) {
        const map = {
            '&': '&amp;',
            '<': '&lt;',
            '>': '&gt;',
            '"': '&quot;',
            "'": '&#039;'
        };
        return text.replace(/[&<>"']/g, m => map[m]);
    }
}

// Initialize chatbot when DOM is ready
let chatbot;
document.addEventListener('DOMContentLoaded', function() {
    chatbot = new PharmaChatbot();
});
