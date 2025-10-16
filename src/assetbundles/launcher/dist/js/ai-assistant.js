(function() {
    window.LauncherAI = {
        modal: null,
        messageInput: null,
        messagesContainer: null,
        config: {
            hotkey: 'cmd+j',
            sendMessageUrl: '',
            startConversationUrl: '',
            validateUrl: '',
        },
        currentThreadId: null,
        isInitialized: false,
        isSending: false,

        init: function(config) {
            if (this.isInitialized) {
                return;
            }

            Object.assign(this.config, config);
            this.createModal();
            this.bindEvents();
            this.isInitialized = true;

            // Validate provider configuration
            this.validateProvider();
        },

        createModal: function() {
            const modalHtml = `
                <div id="launcher-ai-modal" class="launcher-ai-modal" style="display: none;">
                    <div class="launcher-ai-overlay"></div>
                    <div class="launcher-ai-dialog">
                        <div class="launcher-ai-header">
                            <div class="launcher-ai-title">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M12 2C6.48 2 2 6.48 2 12s4.48 10 10 10 10-4.48 10-10S17.52 2 12 2zm1 17h-2v-2h2v2zm0-4h-2V7h2v8z"/>
                                </svg>
                                AI Assistant
                            </div>
                            <div class="launcher-ai-actions">
                                <button type="button" class="launcher-ai-new-chat" title="New conversation">
                                    <svg width="18" height="18" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                        <line x1="12" y1="5" x2="12" y2="19"></line>
                                        <line x1="5" y1="12" x2="19" y2="12"></line>
                                    </svg>
                                </button>
                                <button type="button" class="launcher-ai-close" aria-label="Close" title="${this.config.hotkey.toUpperCase()} or ESC to close">×</button>
                            </div>
                        </div>
                        <div id="launcher-ai-messages" class="launcher-ai-messages">
                            <div class="launcher-ai-welcome">
                                <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                                    <circle cx="12" cy="12" r="10"></circle>
                                    <path d="M9 9h.01M15 9h.01M9 15a3 3 0 0 0 6 0"></path>
                                </svg>
                                <h3>Welcome to Craft AI Assistant</h3>
                                <p>I can help you create content, manage your site, and answer questions about Craft CMS.</p>
                                <div class="launcher-ai-suggestions">
                                    <button class="launcher-ai-suggestion" data-prompt="Create a blog post about our latest product">
                                        Create a blog post
                                    </button>
                                    <button class="launcher-ai-suggestion" data-prompt="Clear all caches">
                                        Clear caches
                                    </button>
                                    <button class="launcher-ai-suggestion" data-prompt="What sections do I have in my site?">
                                        List my sections
                                    </button>
                                </div>
                            </div>
                        </div>
                        <div class="launcher-ai-input-wrapper">
                            <textarea
                                id="launcher-ai-input"
                                class="launcher-ai-input"
                                placeholder="Ask me anything or type a command..."
                                rows="1"
                            ></textarea>
                            <button type="button" id="launcher-ai-send" class="launcher-ai-send" title="Send (Enter)">
                                <svg width="20" height="20" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2">
                                    <line x1="22" y1="2" x2="11" y2="13"></line>
                                    <polygon points="22 2 15 22 11 13 2 9 22 2"></polygon>
                                </svg>
                            </button>
                        </div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            this.modal = document.getElementById('launcher-ai-modal');
            this.messageInput = document.getElementById('launcher-ai-input');
            this.messagesContainer = document.getElementById('launcher-ai-messages');
            this.sendButton = document.getElementById('launcher-ai-send');
        },

        bindEvents: function() {
            const self = this;

            // Hotkey detection - CMD+J
            document.addEventListener('keydown', function(e) {
                if (self.isHotkeyPressed(e)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();
                    self.toggleModal();
                    return false;
                }

                // Handle ESC when modal is open
                if (self.modal && self.modal.style.display !== 'none') {
                    if (e.key === 'Escape') {
                        self.closeModal();
                    }
                }

                // Handle Enter in textarea (Shift+Enter for new line)
                if (e.target === self.messageInput && e.key === 'Enter' && !e.shiftKey) {
                    e.preventDefault();
                    self.sendMessage();
                }
            }, true);

            // Close button
            this.modal.querySelector('.launcher-ai-close').addEventListener('click', function() {
                self.closeModal();
            });

            // New chat button
            this.modal.querySelector('.launcher-ai-new-chat').addEventListener('click', function() {
                self.startNewConversation();
            });

            // Send button
            this.sendButton.addEventListener('click', function() {
                self.sendMessage();
            });

            // Suggestion buttons
            this.modal.addEventListener('click', function(e) {
                if (e.target.classList.contains('launcher-ai-suggestion')) {
                    const prompt = e.target.getAttribute('data-prompt');
                    self.messageInput.value = prompt;
                    self.messageInput.focus();
                    self.sendMessage();
                }
            });

            // Auto-resize textarea
            this.messageInput.addEventListener('input', function() {
                this.style.height = 'auto';
                this.style.height = Math.min(this.scrollHeight, 200) + 'px';
            });

            // Overlay click to close
            this.modal.querySelector('.launcher-ai-overlay').addEventListener('click', function() {
                self.closeModal();
            });
        },

        isHotkeyPressed: function(e) {
            const parts = this.config.hotkey.toLowerCase().split('+');
            const key = parts[parts.length - 1];
            const needsCmd = parts.includes('cmd') || parts.includes('meta');
            const needsCtrl = parts.includes('ctrl');
            const needsAlt = parts.includes('alt');
            const needsShift = parts.includes('shift');

            const keyMatch = e.key.toLowerCase() === key ||
                           (key === 'k' && e.keyCode === 75) ||
                           (key === 'j' && e.keyCode === 74);

            if (!keyMatch) return false;

            if (needsCmd && !(e.metaKey || e.ctrlKey)) return false;
            if (needsCtrl && !e.ctrlKey) return false;
            if (needsAlt && !e.altKey) return false;
            if (needsShift && !e.shiftKey) return false;

            return true;
        },

        toggleModal: function() {
            if (this.modal.style.display === 'none') {
                this.openModal();
            } else {
                this.closeModal();
            }
        },

        openModal: function() {
            const self = this;
            this.modal.style.display = 'flex';
            this.messageInput.focus();

            // Start or resume conversation
            if (!this.currentThreadId) {
                this.startConversation();
            }

            // Fade in animation
            setTimeout(function() {
                self.modal.classList.add('active');
            }, 10);
        },

        closeModal: function() {
            const self = this;
            this.modal.classList.remove('active');
            setTimeout(function() {
                self.modal.style.display = 'none';
            }, 200);
        },

        validateProvider: function() {
            const self = this;

            fetch(this.config.validateUrl, {
                method: 'GET',
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (!data.configured) {
                    console.warn('Launcher AI: Provider not configured -', data.message);
                }
            })
            .catch(error => {
                console.error('Launcher AI: Validation error:', error);
            });
        },

        startConversation: function() {
            const self = this;

            fetch(this.config.startConversationUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.Craft.csrfTokenValue || '',
                }
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    self.currentThreadId = data.conversation.threadId;

                    // Load existing messages if any
                    if (data.messages && data.messages.length > 0) {
                        self.clearWelcome();
                        data.messages.forEach(msg => {
                            self.addMessage(msg.role, msg.content);
                        });
                    }
                } else {
                    self.showError('Failed to start conversation: ' + (data.error || 'Unknown error'));
                }
            })
            .catch(error => {
                console.error('Launcher AI: Start conversation error:', error);
                self.showError('Failed to connect to AI assistant');
            });
        },

        startNewConversation: function() {
            this.currentThreadId = null;
            this.messagesContainer.innerHTML = `
                <div class="launcher-ai-welcome">
                    <svg width="48" height="48" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="1.5">
                        <circle cx="12" cy="12" r="10"></circle>
                        <path d="M9 9h.01M15 9h.01M9 15a3 3 0 0 0 6 0"></path>
                    </svg>
                    <h3>New Conversation</h3>
                    <p>How can I help you today?</p>
                </div>
            `;
            this.startConversation();
        },

        sendMessage: function() {
            if (this.isSending) return;

            const message = this.messageInput.value.trim();
            if (!message) return;

            if (!this.currentThreadId) {
                this.showError('No active conversation. Please wait...');
                return;
            }

            this.isSending = true;
            this.clearWelcome();
            this.addMessage('user', message);
            this.messageInput.value = '';
            this.messageInput.style.height = 'auto';

            // Show typing indicator
            const typingId = this.showTyping();

            const self = this;
            fetch(this.config.sendMessageUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.Craft.csrfTokenValue || '',
                },
                body: JSON.stringify({
                    threadId: this.currentThreadId,
                    message: message,
                })
            })
            .then(response => response.json())
            .then(data => {
                self.isSending = false;
                self.removeTyping(typingId);

                if (data.success && data.message) {
                    self.addMessage('assistant', data.message.content);
                } else {
                    self.showError(data.error || 'Failed to send message');
                }
            })
            .catch(error => {
                self.isSending = false;
                self.removeTyping(typingId);
                console.error('Launcher AI: Send message error:', error);
                self.showError('Failed to send message. Please try again.');
            });
        },

        clearWelcome: function() {
            const welcome = this.messagesContainer.querySelector('.launcher-ai-welcome');
            if (welcome) {
                welcome.remove();
            }
        },

        addMessage: function(role, content) {
            const messageEl = document.createElement('div');
            messageEl.className = `launcher-ai-message launcher-ai-message-${role}`;

            const avatar = document.createElement('div');
            avatar.className = 'launcher-ai-avatar';
            avatar.innerHTML = role === 'user'
                ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>'
                : '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>';

            const bubble = document.createElement('div');
            bubble.className = 'launcher-ai-bubble';

            // Format content with basic markdown support
            if (role === 'assistant') {
                bubble.innerHTML = this.formatMarkdown(content);
            } else {
                bubble.textContent = content;
            }

            messageEl.appendChild(avatar);
            messageEl.appendChild(bubble);
            this.messagesContainer.appendChild(messageEl);

            // Scroll to bottom
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        },

        formatMarkdown: function(text) {
            if (!text) return '';

            // Escape HTML to prevent XSS
            let html = text
                .replace(/&/g, '&amp;')
                .replace(/</g, '&lt;')
                .replace(/>/g, '&gt;');

            // Headers (must be at start of line)
            html = html.replace(/^### (.*?)$/gm, '<h3>$1</h3>');
            html = html.replace(/^## (.*?)$/gm, '<h2>$1</h2>');
            html = html.replace(/^# (.*?)$/gm, '<h1>$1</h1>');

            // Bold
            html = html.replace(/\*\*(.+?)\*\*/g, '<strong>$1</strong>');
            html = html.replace(/__(.+?)__/g, '<strong>$1</strong>');

            // Italic
            html = html.replace(/\*(.+?)\*/g, '<em>$1</em>');
            html = html.replace(/_(.+?)_/g, '<em>$1</em>');

            // Code blocks
            html = html.replace(/```([\s\S]+?)```/g, '<pre><code>$1</code></pre>');

            // Inline code
            html = html.replace(/`(.+?)`/g, '<code>$1</code>');

            // Unordered lists (lines starting with -, *, or +)
            html = html.replace(/^[*+-] (.+)$/gm, '<li>$1</li>');
            html = html.replace(/(<li>.*<\/li>)/s, '<ul>$1</ul>');

            // Ordered lists (lines starting with numbers)
            html = html.replace(/^\d+\. (.+)$/gm, '<li>$1</li>');

            // Links [text](url)
            html = html.replace(/\[([^\]]+)\]\(([^)]+)\)/g, '<a href="$2" target="_blank" rel="noopener">$1</a>');

            // Line breaks - convert double newlines to paragraphs
            const paragraphs = html.split(/\n\n+/);
            html = paragraphs.map(p => {
                // Don't wrap if already wrapped in a block element
                if (p.match(/^<(h[1-6]|ul|ol|pre|blockquote)/)) {
                    return p;
                }
                return '<p>' + p.replace(/\n/g, '<br>') + '</p>';
            }).join('');

            return html;
        },

        showTyping: function() {
            const typingId = 'typing-' + Date.now();
            const typingEl = document.createElement('div');
            typingEl.id = typingId;
            typingEl.className = 'launcher-ai-message launcher-ai-message-assistant';
            typingEl.innerHTML = `
                <div class="launcher-ai-avatar">
                    <svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>
                </div>
                <div class="launcher-ai-bubble launcher-ai-typing">
                    <span></span><span></span><span></span>
                </div>
            `;
            this.messagesContainer.appendChild(typingEl);
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
            return typingId;
        },

        removeTyping: function(typingId) {
            const el = document.getElementById(typingId);
            if (el) {
                el.remove();
            }
        },

        showError: function(message) {
            const errorEl = document.createElement('div');
            errorEl.className = 'launcher-ai-error';
            errorEl.textContent = message;
            this.messagesContainer.appendChild(errorEl);
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;

            // Auto-remove after 5 seconds
            setTimeout(function() {
                errorEl.remove();
            }, 5000);
        },
    };
})();
