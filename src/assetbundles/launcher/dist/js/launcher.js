(function() {
    window.LauncherPlugin = {
        modal: null,
        searchInput: null,
        resultsContainer: null,
        config: {
            hotkey: 'cmd+k',
            searchUrl: '',
            debounceDelay: 300
        },
        searchTimeout: null,
        currentResults: [],
        selectedIndex: 0,

        init: function(config) {
            Object.assign(this.config, config);
            this.createModal();
            this.bindEvents();
        },

        createModal: function() {
            const modalHtml = `
                <div id="launcher-modal" class="launcher-modal" style="display: none;">
                    <div class="launcher-overlay"></div>
                    <div class="launcher-dialog">
                        <div class="launcher-search-wrapper">
                            <input type="text" id="launcher-search" class="launcher-search" placeholder="Search for anything..." autocomplete="off">
                            <button type="button" class="launcher-close" aria-label="Close" title="${this.config.hotkey.toUpperCase()} or ESC to close">×</button>
                        </div>
                        <div id="launcher-results" class="launcher-results"></div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            this.modal = document.getElementById('launcher-modal');
            this.searchInput = document.getElementById('launcher-search');
            this.resultsContainer = document.getElementById('launcher-results');
        },

        bindEvents: function() {
            const self = this;

            // Hotkey detection
            document.addEventListener('keydown', function(e) {
                if (self.isHotkeyPressed(e)) {
                    e.preventDefault();
                    self.toggleModal();
                }

                if (self.modal && self.modal.style.display !== 'none') {
                    if (e.key === 'Escape') {
                        self.closeModal();
                    } else if (e.key === 'ArrowDown') {
                        e.preventDefault();
                        self.selectNext();
                    } else if (e.key === 'ArrowUp') {
                        e.preventDefault();
                        self.selectPrevious();
                    } else if (e.key === 'Enter') {
                        e.preventDefault();
                        self.navigateToSelected();
                    } else if (e.key >= '1' && e.key <= '9') {
                        const index = parseInt(e.key) - 1;
                        if (index < self.currentResults.length) {
                            e.preventDefault();
                            self.navigateToResult(index);
                        }
                    }
                }
            });

            // Search input
            this.searchInput.addEventListener('input', function(e) {
                clearTimeout(self.searchTimeout);
                self.searchTimeout = setTimeout(function() {
                    self.performSearch(e.target.value);
                }, self.config.debounceDelay);
            });

            // Click outside to close
            this.modal.querySelector('.launcher-overlay').addEventListener('click', function() {
                self.closeModal();
            });

            // Close button
            this.modal.querySelector('.launcher-close').addEventListener('click', function() {
                self.closeModal();
            });
        },

        isHotkeyPressed: function(e) {
            const keys = this.config.hotkey.toLowerCase().split('+');
            let pressed = true;

            keys.forEach(function(key) {
                switch(key) {
                    case 'cmd':
                    case 'meta':
                        pressed = pressed && (e.metaKey || e.ctrlKey);
                        break;
                    case 'ctrl':
                        pressed = pressed && e.ctrlKey;
                        break;
                    case 'alt':
                        pressed = pressed && e.altKey;
                        break;
                    case 'shift':
                        pressed = pressed && e.shiftKey;
                        break;
                    default:
                        pressed = pressed && (e.key.toLowerCase() === key);
                }
            });

            return pressed;
        },

        toggleModal: function() {
            if (this.modal && this.modal.style.display !== 'none') {
                this.closeModal();
            } else {
                this.openModal();
            }
        },

        openModal: function() {
            this.modal.style.display = 'block';
            this.searchInput.value = '';
            this.searchInput.focus();
            this.resultsContainer.innerHTML = '<div class="launcher-loading">Type to search...</div>';
            this.performSearch('');
        },

        closeModal: function() {
            this.modal.style.display = 'none';
            this.searchInput.value = '';
            this.resultsContainer.innerHTML = '';
            this.currentResults = [];
            this.selectedIndex = 0;
        },

        performSearch: function(query) {
            const self = this;

            fetch(this.config.searchUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-CSRF-Token': Craft.csrfTokenValue
                },
                body: JSON.stringify({ query: query })
            })
            .then(response => response.json())
            .then(data => {
                if (data.success) {
                    self.displayResults(data.results, data.isRecent);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                self.resultsContainer.innerHTML = '<div class="launcher-error">Search failed. Please try again.</div>';
            });
        },

        displayResults: function(results, isRecent) {
            this.currentResults = results;
            this.selectedIndex = 0;

            if (results.length === 0) {
                this.resultsContainer.innerHTML = '<div class="launcher-no-results">No results found</div>';
                return;
            }

            let html = '';
            if (isRecent) {
                html += '<div class="launcher-section-title">Recent Items</div>';
            }

            results.forEach((result, index) => {
                const iconType = result.type || result.icon;
                const iconSvg = this.getIconSvg(iconType);
                const shortcutHtml = result.shortcut ? `<span class="launcher-shortcut">${result.shortcut}</span>` : '';
                
                html += `
                    <div class="launcher-result ${index === 0 ? 'selected' : ''}" data-index="${index}">
                        <div class="launcher-result-icon">
                            ${iconSvg}
                        </div>
                        <div class="launcher-result-content">
                            <div class="launcher-result-title">${result.title}</div>
                            <div class="launcher-result-meta">
                                <span class="launcher-result-type">${result.type}</span>
                                ${result.section ? `<span class="launcher-result-section">${result.section}</span>` : ''}
                                ${result.group ? `<span class="launcher-result-section">${result.group}</span>` : ''}
                                ${result.handle ? `<span class="launcher-result-handle">${result.handle}</span>` : ''}
                            </div>
                        </div>
                        ${shortcutHtml}
                    </div>
                `;
            });

            this.resultsContainer.innerHTML = html;
            this.bindResultEvents();
        },

        bindResultEvents: function() {
            const self = this;
            const results = this.resultsContainer.querySelectorAll('.launcher-result');

            results.forEach(function(result, index) {
                result.addEventListener('click', function() {
                    self.navigateToResult(index);
                });

                result.addEventListener('mouseenter', function() {
                    self.selectedIndex = index;
                    self.updateSelection();
                });
            });
        },

        selectNext: function() {
            if (this.selectedIndex < this.currentResults.length - 1) {
                this.selectedIndex++;
                this.updateSelection();
            }
        },

        selectPrevious: function() {
            if (this.selectedIndex > 0) {
                this.selectedIndex--;
                this.updateSelection();
            }
        },

        updateSelection: function() {
            const results = this.resultsContainer.querySelectorAll('.launcher-result');
            results.forEach((result, index) => {
                if (index === this.selectedIndex) {
                    result.classList.add('selected');
                    result.scrollIntoView({ block: 'nearest' });
                } else {
                    result.classList.remove('selected');
                }
            });
        },

        navigateToSelected: function() {
            if (this.currentResults.length > 0) {
                this.navigateToResult(this.selectedIndex);
            }
        },

        navigateToResult: function(index) {
            const result = this.currentResults[index];
            if (!result || !result.url) return;

            // Track recent item
            fetch(Craft.getActionUrl('launcher/search/navigate'), {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'X-CSRF-Token': Craft.csrfTokenValue
                },
                body: JSON.stringify({ item: result })
            });

            // Navigate to URL
            window.location.href = result.url;
            this.closeModal();
        },

        getIconSvg: function(iconType) {
            // Normalize the icon type and create a mapping
            const normalizeType = (type) => {
                if (!type) return 'default';
                
                const typeMap = {
                    // Handle capitalized versions
                    'Entry': 'entries',
                    'Category': 'categories', 
                    'Asset': 'assets',
                    'User': 'users',
                    'Global': 'globals',
                    'Section': 'sections',
                    'Field': 'fields',
                    'Plugin': 'plugins',
                    'Route': 'routes',
                    'Volume': 'volumes',
                    'Group': 'groups',
                    'Category Group': 'categories',
                    'Field Group': 'groups',
                    'User Group': 'groups',
                    'Asset Volume': 'volumes',
                    // Handle lowercase versions
                    'entries': 'entries',
                    'categories': 'categories',
                    'assets': 'assets',
                    'users': 'users',
                    'globals': 'globals',
                    'sections': 'sections',
                    'fields': 'fields',
                    'plugins': 'plugins',
                    'routes': 'routes',
                    'volumes': 'volumes',
                    'groups': 'groups',
                    'settings': 'settings',
                    // Handle icon names
                    'folder': 'categories',
                    'newspaper': 'entries',
                    'photo': 'assets',
                    'users': 'users',
                    'globe': 'globals'
                };
                
                return typeMap[type] || 'default';
            };

            const iconMap = {
                'entries': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 2h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M5 6h6M5 8h6M5 10h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                'categories': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 3.5v9c0 .83.67 1.5 1.5 1.5h9c.83 0 1.5-.67 1.5-1.5v-7L11 3H3.5c-.83 0-1.5.67-1.5 1.5z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M11 3v3h3" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/></svg>',
                'assets': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="1" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="6" cy="6" r="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M14 10l-3-3-2 2-3-3-4 4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round"/></svg>',
                'users': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M2 14c0-3.31 2.69-6 6-6s6 2.69 6 6" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
                'globals': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M2 8h12M8 2c2.21 0 4 2.69 4 6s-1.79 6-4 6-4-2.69-4-6 1.79-6 4-6z" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
                'sections': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="12" height="12" rx="1" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M2 6h12M6 2v12" stroke="currentColor" stroke-width="1.5"/><circle cx="4" cy="4" r=".5" fill="currentColor"/></svg>',
                'fields': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="3" width="12" height="10" rx="1" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M4 6h8M4 8h8M4 10h5" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><path d="M12 1v4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                'plugins': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M6.5 1v4.5H2v3h4.5V14l3-3h4.5V2H6.5z" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linejoin="round"/><circle cx="8" cy="6" r="1" fill="currentColor"/><circle cx="11" cy="6" r="1" fill="currentColor"/></svg>',
                'routes': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M2 8h4l2-4h4l2 4" stroke="currentColor" stroke-width="1.5" fill="none" stroke-linecap="round" stroke-linejoin="round"/><circle cx="3" cy="8" r="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="13" cy="8" r="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
                'volumes': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="4" width="12" height="8" rx="1" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M4 4V3a1 1 0 0 1 1-1h6a1 1 0 0 1 1 1v1" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M6 7v2M8 7v2M10 7v2" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/></svg>',
                'groups': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="2" width="5" height="5" rx=".5" stroke="currentColor" stroke-width="1.5" fill="none"/><rect x="9" y="2" width="5" height="5" rx=".5" stroke="currentColor" stroke-width="1.5" fill="none"/><rect x="2" y="9" width="5" height="5" rx=".5" stroke="currentColor" stroke-width="1.5" fill="none"/><rect x="9" y="9" width="5" height="5" rx=".5" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
                'settings': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M8 10a2 2 0 1 0 0-4 2 2 0 0 0 0 4z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M6.7 2h2.6l.4 1.5c.3.1.6.3.9.5l1.4-.8 1.8 1.8-.8 1.4c.2.3.4.6.5.9L14 7.7v2.6l-1.5.4c-.1.3-.3.6-.5.9l.8 1.4-1.8 1.8-1.4-.8c-.3.2-.6.4-.9.5L9.3 14H6.7l-.4-1.5c-.3-.1-.6-.3-.9-.5l-1.4.8L2.2 11l.8-1.4c-.2-.3-.4-.6-.5-.9L1 8.3V5.7l1.5-.4c.1-.3.3-.6.5-.9L2.2 3L4 1.2l1.4.8c.3-.2.6-.4.9-.5L6.7 2z" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
                'default': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="8" cy="8" r="1.5" fill="currentColor"/></svg>'
            };
            
            const normalizedType = normalizeType(iconType);
            return iconMap[normalizedType] || iconMap['default'];
        }
    };
})();