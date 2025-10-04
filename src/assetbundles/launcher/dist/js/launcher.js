(function() {
    window.LauncherPlugin = {
        modal: null,
        searchInput: null,
        resultsContainer: null,
        config: {
            hotkey: 'cmd+k',
            searchUrl: '',
            debounceDelay: 300,
            selectResultModifier: 'cmd'
        },
        searchTimeout: null,
        currentResults: [],
        selectedIndex: 0,
        browseMode: false,
        currentContentType: null,
        isInitialized: false,
        isFrontEnd: false,
        frontEndContext: null,

        init: function(config) {
            // Prevent multiple initializations
            if (this.isInitialized) {
                return;
            }

            Object.assign(this.config, config);
            this.isFrontEnd = config.isFrontEnd || false;
            this.frontEndContext = config.frontEndContext || null;
            this.createModal();
            this.bindEvents();
            this.isInitialized = true;
        },

        createModal: function() {
            const modalHtml = `
                <div id="launcher-modal" class="launcher-modal" style="display: none;">
                    <canvas id="launcher-game-canvas" style="position: fixed; top: 0; left: 0; width: 100%; height: 100%; z-index: 99999; pointer-events: none; opacity: 0; background: rgba(10, 10, 10, 0.9); transition: opacity 0.3s ease;"></canvas>
                    <div class="launcher-overlay"></div>
                    <div class="launcher-dialog">
                        <div class="launcher-search-wrapper">
                            <input type="text" id="launcher-search" class="launcher-search" placeholder="Search for anything..." autocomplete="off">
                            <button type="button" class="launcher-close" aria-label="Close" title="${this.config.hotkey.toUpperCase()} or ESC to close">×</button>
                        </div>
                        <div id="launcher-loading-bar" class="launcher-loading-bar" style="display: none;">
                            <div class="launcher-loading-dots">
                                <div class="launcher-loading-dot"></div>
                                <div class="launcher-loading-dot"></div>
                                <div class="launcher-loading-dot"></div>
                            </div>
                        </div>
                        <div id="launcher-results" class="launcher-results"></div>
                    </div>
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            this.modal = document.getElementById('launcher-modal');
            this.searchInput = document.getElementById('launcher-search');
            this.loadingBar = document.getElementById('launcher-loading-bar');
            this.resultsContainer = document.getElementById('launcher-results');
            this.gameCanvas = document.getElementById('launcher-game-canvas');
            this.initGame();
        },

        bindEvents: function() {
            const self = this;

            // Hotkey detection - use capture phase to get event first
            document.addEventListener('keydown', function(e) {
                if (self.isHotkeyPressed(e)) {
                    e.preventDefault();
                    e.stopImmediatePropagation(); // Stop other handlers
                    self.toggleModal();
                    return false;
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
                    } else if (e.key >= '1' && e.key <= '9' && self.isModifierPressed(e, self.config.selectResultModifier)) {
                        let index;
                        if (self.browseMode && !self.currentContentType) {
                            // In browse mode, key "1" should select index 1 (since index 0 is Return)
                            index = parseInt(e.key);
                        } else {
                            // In regular search results, key "1" should select index 1
                            index = parseInt(e.key);
                        }

                        if (index < self.currentResults.length) {
                            e.preventDefault();

                            // In browse mode, use selectContentType, otherwise navigateToResult
                            if (self.browseMode && !self.currentContentType) {
                                self.selectContentType(index);
                            } else {
                                self.navigateToResult(index);
                            }
                        }
                    }
                }
            }, true); // Add true for capture phase

            // Search input
            this.searchInput.addEventListener('input', function(e) {
                clearTimeout(self.searchTimeout);
                const query = e.target.value;

                // Check for special trigger
                if (query === '*' + String.fromCharCode(97,115,116,101,114,111,105,100,115) + '*') {
                    self.showGame();
                    return;
                }

                // Check for browse mode (asterisk)
                if (query === '*') {
                    self.showBrowseMode();
                    return;
                }

                // Reset browse mode if not using asterisk
                if (self.browseMode) {
                    self.exitBrowseMode();
                }

                self.searchTimeout = setTimeout(function() {
                    self.performSearch(query);
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

        isModifierPressed: function(e, modifier) {
            switch(modifier.toLowerCase()) {
                case 'cmd':
                case 'meta':
                    return e.metaKey || e.ctrlKey;
                case 'ctrl':
                    return e.ctrlKey;
                case 'alt':
                    return e.altKey;
                case 'shift':
                    return e.shiftKey;
                default:
                    return false;
            }
        },

        getModifierSymbol: function(modifier) {
            switch(modifier.toLowerCase()) {
                case 'cmd':
                case 'meta':
                    return '⌘';
                case 'ctrl':
                    return 'Ctrl+';
                case 'alt':
                    return '⌥';
                case 'shift':
                    return '⇧';
                default:
                    return modifier.toUpperCase() + '+';
            }
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
            this.hideLoadingIndicator();
            this.resultsContainer.innerHTML = '<div class="launcher-loading">Type to search...</div>';
            this.performSearch('');
        },

        closeModal: function() {
            this.modal.style.display = 'none';
            this.searchInput.value = '';
            this.hideLoadingIndicator();
            this.resultsContainer.innerHTML = '';
            this.currentResults = [];
            this.selectedIndex = 0;
            this.exitBrowseMode();
        },

        showLoadingIndicator: function() {
            this.loadingBar.style.display = 'flex';
            this.resultsContainer.style.display = 'none';
        },

        hideLoadingIndicator: function() {
            this.loadingBar.style.display = 'none';
            this.resultsContainer.style.display = 'block';
        },

        performSearch: function(query) {
            const self = this;

            // Show loading indicator if query is not empty
            if (query.trim() !== '') {
                this.showLoadingIndicator();
            }

            // Prepare request body
            const requestBody = {
                query: query
            };

            // Add CSRF token - use config values if available (front-end), otherwise fall back to Craft object (CP)
            const csrfTokenName = this.config.csrfTokenName || (typeof Craft !== 'undefined' ? Craft.csrfTokenName : null);
            const csrfTokenValue = this.config.csrfTokenValue || (typeof Craft !== 'undefined' ? Craft.csrfTokenValue : null);

            if (csrfTokenName && csrfTokenValue) {
                requestBody[csrfTokenName] = csrfTokenValue;
            }

            // Add context if we're on front-end and have context
            if (this.config.isFrontEnd && this.frontEndContext) {
                requestBody.context = this.frontEndContext;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            // Add CSRF token to headers if available
            if (csrfTokenValue) {
                headers['X-CSRF-Token'] = csrfTokenValue;
            }

            fetch(this.config.searchUrl, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(requestBody)
            })
            .then(response => response.json())
            .then(data => {
                self.hideLoadingIndicator();
                if (data.success) {
                    self.displayResults(data.results, data.isRecent, data);
                }
            })
            .catch(error => {
                console.error('Search error:', error);
                self.hideLoadingIndicator();
                self.resultsContainer.innerHTML = '<div class="launcher-error">Search failed. Please try again.</div>';
            });
        },

        displayResults: function(results, isRecent, data) {
            // Add "Edit this page" option for front-end context
            if (this.isFrontEnd && this.frontEndContext && this.frontEndContext.currentElement && !this.browseMode) {
                const currentElement = this.frontEndContext.currentElement;
                const currentEditUrl = currentElement.editUrl;

                // Filter out any existing history items that match the current page's edit URL
                results = results.filter(result => result.url !== currentEditUrl);

                const contextResult = {
                    id: currentElement.id,
                    title: currentElement.title, // Store clean title
                    url: currentElement.editUrl,
                    type: currentElement.type,
                    section: currentElement.section || currentElement.group,
                    icon: currentElement.type.toLowerCase()
                };
                results = [contextResult, ...results];
            }

            this.currentResults = results;
            this.selectedIndex = 0;

            if (results.length === 0) {
                this.resultsContainer.innerHTML = '<div class="launcher-no-results">No results found</div>';
                return;
            }

            let html = '';
            if (isRecent) {
                html += '<div class="launcher-section-title">Recent Items</div>';
            } else if (data.isPopular) {
                html += '<div class="launcher-section-title">Popular Items</div>';
            } else if (this.isFrontEnd && !this.browseMode) {
                html += '<div class="launcher-section-title">Search Results</div>';
            }

            results.forEach((result, index) => {
                const iconType = result.type || result.icon;
                const iconSvg = this.getIconSvg(iconType);
                // Generate shortcut display based on index and settings
                let shortcutHtml = '';
                if (index === 0) {
                    // First result uses Return key
                    shortcutHtml = '<span class="launcher-shortcut">⏎</span>';
                } else if (index <= 9) {
                    // Results 1-9 use modifier + number (index starts at 0, so index 1 = position 2 = shortcut "1")
                    const shortcutNumber = index;
                    const modifierSymbol = this.getModifierSymbol(this.config.selectResultModifier);
                    shortcutHtml = `<span class="launcher-shortcut">${modifierSymbol}${shortcutNumber}</span>`;
                }

                // Add remove button for popular items
                const removeButtonHtml = result.isPopular && result.itemHash ?
                    `<button class="launcher-remove-btn" data-item-hash="${result.itemHash}" title="Remove from history" aria-label="Remove from history">×</button>` : '';

                html += `
                    <div class="launcher-result ${index === 0 ? 'selected' : ''}" data-index="${index}">
                        <div class="launcher-result-icon">
                            ${iconSvg}
                        </div>
                        <div class="launcher-result-content">
                            <div class="launcher-result-title">${result.type === 'Context Action' ? '✏️ ' + result.title + (result.entry ? ': ' + result.entry : '') : result.title}</div>
                            <div class="launcher-result-meta">
                                <span class="launcher-result-type">${result.type}</span>
                                ${result.section ? `<span class="launcher-result-section">${result.section}</span>` : ''}
                                ${result.group ? `<span class="launcher-result-section">${result.group}</span>` : ''}
                                ${result.handle ? `<span class="launcher-result-handle">${result.handle}</span>` : ''}
                                ${result.author ? `<span class="launcher-result-handle">by ${result.author}</span>` : ''}
                                ${result.email ? `<span class="launcher-result-handle">${result.email}</span>` : ''}
                                ${result.customer ? `<span class="launcher-result-handle">${result.customer}</span>` : ''}
                                ${result.status ? `<span class="launcher-result-section">${result.status}</span>` : ''}
                                ${result.product ? `<span class="launcher-result-section">${result.product}</span>` : ''}
                                ${result.launchCount ? `<span class="launcher-result-count launcher-result-count-hidden">${result.launchCount} launches</span>` : ''}
                            </div>
                        </div>
                        <div class="launcher-result-actions">
                            ${shortcutHtml}
                            ${removeButtonHtml}
                        </div>
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
                result.addEventListener('click', function(e) {
                    // Check if the click was on the remove button
                    if (e.target.classList.contains('launcher-remove-btn')) {
                        e.stopPropagation();
                        self.removeHistoryItem(e.target.dataset.itemHash, index);
                        return;
                    }

                    // In browse mode, use different logic
                    if (self.browseMode && !self.currentContentType) {
                        self.selectContentType(index);
                    } else {
                        self.navigateToResult(index);
                    }
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
                // Handle browse mode navigation differently
                if (this.browseMode && !this.currentContentType) {
                    this.selectContentType(this.selectedIndex);
                } else {
                    this.navigateToResult(this.selectedIndex);
                }
            }
        },

        navigateToResult: function(index) {
            const result = this.currentResults[index];
            if (!result || !result.url) return;

            console.log('Navigating to result:', result.title, result.url);

            // Get action URL and CSRF token
            const actionUrl = this.config.navigateUrl || (typeof Craft !== 'undefined' ? Craft.getActionUrl('launcher/search/navigate') : null);
            const csrfTokenName = this.config.csrfTokenName || (typeof Craft !== 'undefined' ? Craft.csrfTokenName : null);
            const csrfTokenValue = this.config.csrfTokenValue || (typeof Craft !== 'undefined' ? Craft.csrfTokenValue : null);

            // Note: Context items (edit this page) are now tracked in history since they're valuable navigation actions

            if (!actionUrl) {
                // If no navigate URL available, just navigate directly
                this.navigateToUrl(result.url);
                this.closeModal();
                return;
            }

            console.log('Making request to:', actionUrl, 'with item:', result);

            const requestBody = { item: result };
            if (csrfTokenName && csrfTokenValue) {
                requestBody[csrfTokenName] = csrfTokenValue;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            if (csrfTokenValue) {
                headers['X-CSRF-Token'] = csrfTokenValue;
            }

            fetch(actionUrl, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(requestBody)
            })
            .then(response => {
                console.log('Navigation tracking response status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('Navigation tracking response:', data);
                // Navigation tracking complete, now navigate
                this.navigateToUrl(result.url);
                this.closeModal();
            })
            .catch(error => {
                // Even if tracking fails, still navigate
                console.warn('Failed to track navigation:', error);
                this.navigateToUrl(result.url);
                this.closeModal();
            });
        },

        removeHistoryItem: function(itemHash, index) {
            console.log('Removing history item:', itemHash);

            const self = this;
            const actionUrl = this.config.removeHistoryUrl || (typeof Craft !== 'undefined' ? Craft.getActionUrl('launcher/search/remove-history-item') : null);
            const csrfTokenName = this.config.csrfTokenName || (typeof Craft !== 'undefined' ? Craft.csrfTokenName : null);
            const csrfTokenValue = this.config.csrfTokenValue || (typeof Craft !== 'undefined' ? Craft.csrfTokenValue : null);

            if (!actionUrl) {
                console.warn('No remove history URL available');
                return;
            }

            const requestBody = { itemHash: itemHash };
            if (csrfTokenName && csrfTokenValue) {
                requestBody[csrfTokenName] = csrfTokenValue;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            if (csrfTokenValue) {
                headers['X-CSRF-Token'] = csrfTokenValue;
            }

            fetch(actionUrl, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(requestBody)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Remove history item response:', data);
                if (data.success) {
                    // Remove the item from current results array
                    self.currentResults.splice(index, 1);

                    // Update selection index if needed
                    if (self.selectedIndex >= self.currentResults.length) {
                        self.selectedIndex = Math.max(0, self.currentResults.length - 1);
                    }

                    // Re-render the results
                    self.displayResults(self.currentResults, false, { isPopular: true });

                    // Show a subtle success message
                    console.log('Item removed from history');
                } else {
                    console.warn('Failed to remove item from history:', data.error);
                }
            })
            .catch(error => {
                console.error('Failed to remove history item:', error);
            });
        },

        showBrowseMode: function() {
            this.browseMode = true;
            this.currentContentType = null;

            // Get available content types from settings
            const contentTypes = [
                {type: 'entries', label: 'Entries', description: 'All entry content'},
                {type: 'categories', label: 'Categories', description: 'Category items'},
                {type: 'assets', label: 'Assets', description: 'Media and files'},
                {type: 'users', label: 'Users', description: 'User accounts'},
                {type: 'globals', label: 'Global Sets', description: 'Global content'},
                {type: 'sections', label: 'Sections', description: 'Entry section settings'},
                {type: 'entryTypes', label: 'Entry Types', description: 'Entry type definitions'},
                {type: 'groups', label: 'Category Groups', description: 'Category group settings'},
                {type: 'volumes', label: 'Asset Volumes', description: 'Asset volume settings'},
                {type: 'fields', label: 'Fields', description: 'Field definitions'},
                {type: 'plugins', label: 'Plugins', description: 'Plugin settings'},
                {type: 'settings', label: 'Settings', description: 'System settings'}
            ];

            this.displayBrowseResults(contentTypes);
        },

        exitBrowseMode: function() {
            this.browseMode = false;
            this.currentContentType = null;
        },

        selectContentType: function(index) {
            const contentType = this.currentResults[index];

            if (!contentType) {
                return;
            }

            this.currentContentType = contentType.type;
            this.searchInput.value = `Browse ${contentType.label}`;

            // Perform search for all items of this type
            this.performBrowseSearch(contentType.type);
        },

        displayBrowseResults: function(contentTypes) {
            this.currentResults = contentTypes;
            this.selectedIndex = 0;

            let html = '<div class="launcher-section-title">Browse Content Types</div>';

            contentTypes.forEach((contentType, index) => {
                const iconSvg = this.getIconSvg(contentType.type);
                // Generate shortcut display for browse mode
                let shortcutHtml = '';
                if (index === 0) {
                    shortcutHtml = '<span class="launcher-shortcut">⏎</span>';
                } else if (index <= 9) {
                    // For browse mode, shortcut number should be index (1-9), not index (since index 1 = key "1")
                    const shortcutNumber = index;
                    const modifierSymbol = this.getModifierSymbol(this.config.selectResultModifier);
                    shortcutHtml = `<span class="launcher-shortcut">${modifierSymbol}${shortcutNumber}</span>`;
                }

                html += `
                    <div class="launcher-result ${index === 0 ? 'selected' : ''}" data-index="${index}">
                        <div class="launcher-result-icon">
                            ${iconSvg}
                        </div>
                        <div class="launcher-result-content">
                            <div class="launcher-result-title">${contentType.label}</div>
                            <div class="launcher-result-meta">
                                <span class="launcher-result-type">Browse</span>
                                <span class="launcher-result-section">${contentType.description}</span>
                            </div>
                        </div>
                        <div class="launcher-result-actions">
                            ${shortcutHtml}
                        </div>
                    </div>
                `;
            });

            this.resultsContainer.innerHTML = html;
            this.bindResultEvents();
        },

        performBrowseSearch: function(contentType) {
            const self = this;

            // Show loading indicator for browse search
            this.showLoadingIndicator();

            // Get CSRF token
            const csrfTokenName = this.config.csrfTokenName || (typeof Craft !== 'undefined' ? Craft.csrfTokenName : null);
            const csrfTokenValue = this.config.csrfTokenValue || (typeof Craft !== 'undefined' ? Craft.csrfTokenValue : null);

            const requestBody = {
                query: '', // Empty query to get all
                browseType: contentType
            };

            if (csrfTokenName && csrfTokenValue) {
                requestBody[csrfTokenName] = csrfTokenValue;
            }

            const headers = {
                'Content-Type': 'application/json',
                'Accept': 'application/json'
            };

            if (csrfTokenValue) {
                headers['X-CSRF-Token'] = csrfTokenValue;
            }

            // Send request to get all items of this content type
            fetch(this.config.searchUrl, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(requestBody)
            })
            .then(response => response.json())
            .then(data => {
                self.hideLoadingIndicator();
                if (data.success) {
                    self.displayResults(data.results, false, data);
                } else {
                    self.resultsContainer.innerHTML = '<div class="launcher-error">Browse failed: ' + (data.error || 'Unknown error') + '</div>';
                }
            })
            .catch(error => {
                console.error('Browse search error:', error);
                self.hideLoadingIndicator();
                self.resultsContainer.innerHTML = '<div class="launcher-error">Browse failed. Please try again.</div>';
            });
        },

        setFrontEndContext: function(context) {
            this.frontEndContext = context;
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
                    'Settings': 'settings',
                    'Category Group': 'categories',
                    'Field Group': 'groups',
                    'User Group': 'groups',
                    'Asset Volume': 'volumes',
                    // Commerce types
                    'Commerce Customer': 'commerce-customer',
                    'Commerce Product': 'commerce-product',
                    'Commerce Variant': 'commerce-variant',
                    'Commerce Order': 'commerce-order',
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
                'settings': '<svg xmlns="http://www.w3.org/2000/svg" viewBox="0 0 512 512" focusable="false" aria-hidden="true"><path d="M495.9 166.6c3.2 8.7 .5 18.4-6.4 24.6l-43.3 39.4c1.1 8.3 1.7 16.8 1.7 25.4s-.6 17.1-1.7 25.4l43.3 39.4c6.9 6.2 9.6 15.9 6.4 24.6c-4.4 11.9-9.7 23.3-15.8 34.3l-4.7 8.1c-6.6 11-14 21.4-22.1 31.2c-5.9 7.2-15.7 9.6-24.5 6.8l-55.7-17.7c-13.4 10.3-28.2 18.9-44 25.4l-12.5 57.1c-2 9.1-9 16.3-18.2 17.8c-13.8 2.3-28 3.5-42.5 3.5s-28.7-1.2-42.5-3.5c-9.2-1.5-16.2-8.7-18.2-17.8l-12.5-57.1c-15.8-6.5-30.6-15.1-44-25.4L83.1 425.9c-8.8 2.8-18.6 .3-24.5-6.8c-8.1-9.8-15.5-20.2-22.1-31.2l-4.7-8.1c-6.1-11-11.4-22.4-15.8-34.3c-3.2-8.7-.5-18.4 6.4-24.6l43.3-39.4C64.6 273.1 64 264.6 64 256s.6-17.1 1.7-25.4L22.4 191.2c-6.9-6.2-9.6-15.9-6.4-24.6c4.4-11.9 9.7-23.3 15.8-34.3l4.7-8.1c6.6-11 14-21.4 22.1-31.2c5.9-7.2 15.7-9.6 24.5-6.8l55.7 17.7c13.4-10.3 28.2-18.9 44-25.4l12.5-57.1c2-9.1 9-16.3 18.2-17.8C227.3 1.2 241.5 0 256 0s28.7 1.2 42.5 3.5c9.2 1.5 16.2 8.7 18.2 17.8l12.5 57.1c15.8 6.5 30.6 15.1 44 25.4l55.7-17.7c8.8-2.8 18.6-.3 24.5 6.8c8.1 9.8 15.5 20.2 22.1 31.2l4.7 8.1c6.1 11 11.4 22.4 15.8 34.3zM256 336a80 80 0 1 0 0-160 80 80 0 1 0 0 160z"></path></svg>',
                // Commerce icons
                'commerce-customer': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="5" r="3" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M2 14c0-3.31 2.69-6 6-6s6 2.69 6 6" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="12" cy="4" r="2" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
                'commerce-product': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="2" y="3" width="12" height="10" rx="1" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M5 7h6M5 9h6M5 11h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="13" cy="2" r="1.5" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
                'commerce-variant': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><rect x="1" y="2" width="12" height="10" rx="1" stroke="currentColor" stroke-width="1.5" fill="none"/><rect x="3" y="4" width="12" height="10" rx="1" stroke="currentColor" stroke-width="1.5" fill="none"/></svg>',
                'commerce-order': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><path d="M3 2h10a1 1 0 0 1 1 1v10a1 1 0 0 1-1 1H3a1 1 0 0 1-1-1V3a1 1 0 0 1 1-1z" stroke="currentColor" stroke-width="1.5" fill="none"/><path d="M5 6h6M5 8h6M5 10h4" stroke="currentColor" stroke-width="1.5" stroke-linecap="round"/><circle cx="11" cy="5" r="1.5" fill="currentColor"/></svg>',
                'default': '<svg width="16" height="16" viewBox="0 0 16 16" fill="none"><circle cx="8" cy="8" r="6" stroke="currentColor" stroke-width="1.5" fill="none"/><circle cx="8" cy="8" r="1.5" fill="currentColor"/></svg>'
            };

            const normalizedType = normalizeType(iconType);
            return iconMap[normalizedType] || iconMap['default'];
        },

        /**
         * Navigate to a URL, respecting the new tab preference for front-end usage
         */
        navigateToUrl: function(url) {
            // Check if we should open in new tab (only for front-end usage)
            if (this.config.isFrontEnd && this.config.openInNewTab) {
                window.open(url, '_blank');
            } else {
                window.location.href = url;
            }
        },

        // Special features
        initGame: function() {
            const self = this;
            const canvas = this.gameCanvas;
            const ctx = canvas.getContext('2d');

            // Game state variables
            this.gameKeys = {};
            this.gameParticles = [];
            this.gameBullets = [];
            this.gameAsteroids = [];
            this.gameState = String.fromCharCode(112,108,97,121,105,110,103); // 'playing'
            this.gameRespawnTimer = 0;
            this.gameScore = 0;
            this.gameVisible = false;
            this.gameRunning = false;
            this.gameLastFire = 0;

            // Ship configuration
            this.gameShip = {
                x: 100,
                y: 100,
                angle: 0,
                velocity: { x: 0, y: 0 },
                thrust: 0.3,
                rotSpeed: 0.1,
                maxSpeed: 8,
                size: 8
            };

            // Color and shape definitions (obfuscated)
            this.gameColors = ['#ff007f', '#8a2be2', '#00ffff', '#39ff14', '#ff4500', '#ffd700'];
            this.gameShapes = [
                String.fromCharCode(99,105,114,99,108,101),
                String.fromCharCode(116,114,105,97,110,103,108,101),
                String.fromCharCode(100,105,97,109,111,110,100),
                String.fromCharCode(111,99,116,97,103,111,110),
                String.fromCharCode(115,116,97,114)
            ];

            // Resize canvas
            const resizeCanvas = () => {
                canvas.width = window.innerWidth;
                canvas.height = window.innerHeight;
            };
            resizeCanvas();
            window.addEventListener('resize', resizeCanvas);

            // Key handlers for game
            document.addEventListener('keydown', (e) => {
                if (self.gameVisible) {
                    self.gameKeys[e.code] = true;
                    if (e.code !== 'Escape') {
                        e.preventDefault();
                    }
                }
            });

            document.addEventListener('keyup', (e) => {
                if (self.gameVisible) {
                    self.gameKeys[e.code] = false;
                    if (e.code !== 'Escape') {
                        e.preventDefault();
                    }
                }
            });

            // Start game loop
            this.runGameLoop();
        },

        showGame: function() {
            this.gameVisible = true;
            this.gameRunning = true;
            this.gameCanvas.style.opacity = '1';
            this.gameCanvas.style.pointerEvents = 'auto';

            // Initialize asteroids if empty
            if (this.gameAsteroids.length === 0) {
                this.initAsteroids();
            }

            // Reset ship position
            this.gameShip.x = this.gameCanvas.width / 2;
            this.gameShip.y = this.gameCanvas.height / 2;
            this.gameShip.velocity.x = 0;
            this.gameShip.velocity.y = 0;
            this.gameShip.angle = 0;
        },

        hideGame: function() {
            this.gameVisible = false;
            this.gameCanvas.style.opacity = '0';
            this.gameCanvas.style.pointerEvents = 'none';

            // Clear search input
            this.searchInput.value = '';
            this.searchInput.focus();
        },

        createThrustParticle: function() {
            const ship = this.gameShip;
            const thrustAngle = ship.angle + Math.PI;
            const spread = 0.4;
            const particleAngle = thrustAngle + (Math.random() - 0.5) * spread;

            this.gameParticles.push({
                x: ship.x - Math.cos(ship.angle) * ship.size,
                y: ship.y - Math.sin(ship.angle) * ship.size,
                vx: Math.cos(particleAngle) * (2 + Math.random() * 3),
                vy: Math.sin(particleAngle) * (2 + Math.random() * 3),
                life: 1,
                decay: 0.02 + Math.random() * 0.02
            });
        },

        hyperspace: function() {
            const ship = this.gameShip;
            const canvas = this.gameCanvas;

            // Create explosion effect
            for (let i = 0; i < 20; i++) {
                this.gameParticles.push({
                    x: ship.x,
                    y: ship.y,
                    vx: (Math.random() - 0.5) * 10,
                    vy: (Math.random() - 0.5) * 10,
                    life: 1,
                    decay: 0.05
                });
            }

            // Teleport
            ship.x = Math.random() * canvas.width;
            ship.y = Math.random() * canvas.height;
            ship.velocity.x = 0;
            ship.velocity.y = 0;

            // Appearance effect
            setTimeout(() => {
                for (let i = 0; i < 15; i++) {
                    this.gameParticles.push({
                        x: ship.x + (Math.random() - 0.5) * 40,
                        y: ship.y + (Math.random() - 0.5) * 40,
                        vx: (Math.random() - 0.5) * 5,
                        vy: (Math.random() - 0.5) * 5,
                        life: 1,
                        decay: 0.03
                    });
                }
            }, 100);
        },

        fire: function() {
            const ship = this.gameShip;
            this.gameBullets.push({
                x: ship.x + Math.cos(ship.angle) * ship.size,
                y: ship.y + Math.sin(ship.angle) * ship.size,
                vx: Math.cos(ship.angle) * 12 + ship.velocity.x,
                vy: Math.sin(ship.angle) * 12 + ship.velocity.y,
                life: 60
            });
        },

        createAsteroid: function(x, y, size, shape, color) {
            const canvas = this.gameCanvas;
            return {
                x: x || Math.random() * canvas.width,
                y: y || Math.random() * canvas.height,
                vx: (Math.random() - 0.5) * 4,
                vy: (Math.random() - 0.5) * 4,
                rotation: 0,
                rotationSpeed: (Math.random() - 0.5) * 0.1,
                size: size || (20 + Math.random() * 30),
                shape: shape || this.gameShapes[Math.floor(Math.random() * this.gameShapes.length)],
                color: color || this.gameColors[Math.floor(Math.random() * this.gameColors.length)],
                pulsePhase: Math.random() * Math.PI * 2
            };
        },

        drawAsteroid: function(asteroid) {
            const canvas = this.gameCanvas;
            const ctx = canvas.getContext('2d');

            ctx.save();
            ctx.translate(asteroid.x, asteroid.y);
            ctx.rotate(asteroid.rotation);

            const pulse = Math.sin(asteroid.pulsePhase) * 0.3 + 0.7;
            ctx.strokeStyle = asteroid.color;
            ctx.lineWidth = 3;
            ctx.shadowBlur = 15 * pulse;
            ctx.shadowColor = asteroid.color;

            ctx.beginPath();

            switch(asteroid.shape) {
                case String.fromCharCode(99,105,114,99,108,101): // circle
                    ctx.arc(0, 0, asteroid.size, 0, Math.PI * 2);
                    break;

                case String.fromCharCode(116,114,105,97,110,103,108,101): // triangle
                    ctx.moveTo(0, -asteroid.size);
                    ctx.lineTo(-asteroid.size * 0.866, asteroid.size * 0.5);
                    ctx.lineTo(asteroid.size * 0.866, asteroid.size * 0.5);
                    ctx.closePath();
                    break;

                case String.fromCharCode(100,105,97,109,111,110,100): // diamond
                    ctx.moveTo(0, -asteroid.size);
                    ctx.lineTo(asteroid.size, 0);
                    ctx.lineTo(0, asteroid.size);
                    ctx.lineTo(-asteroid.size, 0);
                    ctx.closePath();
                    break;

                case String.fromCharCode(111,99,116,97,103,111,110): // octagon
                    for (let i = 0; i < 8; i++) {
                        const angle = (i / 8) * Math.PI * 2;
                        const x = Math.cos(angle) * asteroid.size;
                        const y = Math.sin(angle) * asteroid.size;
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    }
                    ctx.closePath();
                    break;

                case String.fromCharCode(115,116,97,114): // star
                    for (let i = 0; i < 10; i++) {
                        const angle = (i / 10) * Math.PI * 2;
                        const radius = i % 2 === 0 ? asteroid.size : asteroid.size * 0.5;
                        const x = Math.cos(angle) * radius;
                        const y = Math.sin(angle) * radius;
                        if (i === 0) ctx.moveTo(x, y);
                        else ctx.lineTo(x, y);
                    }
                    ctx.closePath();
                    break;
            }

            ctx.stroke();
            ctx.restore();
        },

        checkCollision: function(obj1, obj2, radius1, radius2) {
            const dx = obj1.x - obj2.x;
            const dy = obj1.y - obj2.y;
            const distance = Math.sqrt(dx * dx + dy * dy);
            return distance < radius1 + radius2;
        },

        createExplosion: function(x, y, color, count) {
            count = count || 15;
            for (let i = 0; i < count; i++) {
                this.gameParticles.push({
                    x: x,
                    y: y,
                    vx: (Math.random() - 0.5) * 12,
                    vy: (Math.random() - 0.5) * 12,
                    life: 1,
                    decay: 0.02 + Math.random() * 0.02,
                    color: color,
                    size: 2 + Math.random() * 3
                });
            }
        },

        splitAsteroid: function(asteroid) {
            if (asteroid.size > 15) {
                const newSize = asteroid.size * 0.6;
                for (let i = 0; i < 2; i++) {
                    this.gameAsteroids.push(this.createAsteroid(
                        asteroid.x + (Math.random() - 0.5) * 20,
                        asteroid.y + (Math.random() - 0.5) * 20,
                        newSize,
                        asteroid.shape,
                        asteroid.color
                    ));
                }
            }
        },

        initAsteroids: function() {
            const canvas = this.gameCanvas;
            const ship = this.gameShip;

            for (let i = 0; i < 6; i++) {
                let x, y;
                do {
                    x = Math.random() * canvas.width;
                    y = Math.random() * canvas.height;
                } while (this.checkCollision({x, y}, {x: ship.x, y: ship.y}, 150, 8));

                this.gameAsteroids.push(this.createAsteroid(x, y));
            }
        },

        killShip: function() {
            this.gameState = String.fromCharCode(100,101,97,100); // 'dead'
            this.gameRespawnTimer = 120;
            this.createExplosion(this.gameShip.x, this.gameShip.y, '#00ffff', 25);
        },

        respawnShip: function() {
            const canvas = this.gameCanvas;
            this.gameShip.x = canvas.width / 2;
            this.gameShip.y = canvas.height / 2;
            this.gameShip.velocity.x = 0;
            this.gameShip.velocity.y = 0;
            this.gameShip.angle = 0;
            this.gameState = String.fromCharCode(112,108,97,121,105,110,103); // 'playing'
        },

        runGameLoop: function() {
            const self = this;
            const canvas = this.gameCanvas;
            const ctx = canvas.getContext('2d');

            function gameLoop() {
                if (!self.gameVisible || !self.gameRunning) {
                    requestAnimationFrame(gameLoop);
                    return;
                }

                // Clear with trail effect
                ctx.fillStyle = 'rgba(10, 10, 10, 0.1)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Handle respawning
                if (self.gameState === String.fromCharCode(100,101,97,100)) {
                    self.gameRespawnTimer--;
                    if (self.gameRespawnTimer <= 0) {
                        self.respawnShip();
                    }
                }

                // Handle input
                if (self.gameState === String.fromCharCode(112,108,97,121,105,110,103)) {
                    const ship = self.gameShip;

                    // ESC to close game
                    if (self.gameKeys['Escape']) {
                        self.hideGame();
                        self.gameKeys['Escape'] = false;
                    }

                    if (self.gameKeys['ArrowLeft']) {
                        ship.angle -= ship.rotSpeed;
                    }
                    if (self.gameKeys['ArrowRight']) {
                        ship.angle += ship.rotSpeed;
                    }
                    if (self.gameKeys['ArrowUp']) {
                        ship.velocity.x += Math.cos(ship.angle) * ship.thrust;
                        ship.velocity.y += Math.sin(ship.angle) * ship.thrust;

                        if (Math.random() < 0.7) {
                            self.createThrustParticle();
                        }
                    }
                    if (self.gameKeys['ArrowDown']) {
                        self.hyperspace();
                        self.gameKeys['ArrowDown'] = false;
                    }
                    if (self.gameKeys['Space'] && Date.now() - self.gameLastFire > 150) {
                        self.fire();
                        self.gameLastFire = Date.now();
                    }

                    // Apply speed limit
                    const speed = Math.sqrt(ship.velocity.x ** 2 + ship.velocity.y ** 2);
                    if (speed > ship.maxSpeed) {
                        ship.velocity.x = (ship.velocity.x / speed) * ship.maxSpeed;
                        ship.velocity.y = (ship.velocity.y / speed) * ship.maxSpeed;
                    }

                    // Update ship position
                    ship.x += ship.velocity.x;
                    ship.y += ship.velocity.y;

                    // Screen wrapping
                    if (ship.x < 0) ship.x = canvas.width;
                    if (ship.x > canvas.width) ship.x = 0;
                    if (ship.y < 0) ship.y = canvas.height;
                    if (ship.y > canvas.height) ship.y = 0;

                    // Friction
                    ship.velocity.x *= 0.99;
                    ship.velocity.y *= 0.99;
                }

                // Update asteroids
                self.gameAsteroids.forEach(asteroid => {
                    asteroid.x += asteroid.vx;
                    asteroid.y += asteroid.vy;
                    asteroid.rotation += asteroid.rotationSpeed;
                    asteroid.pulsePhase += 0.05;

                    if (asteroid.x < -asteroid.size) asteroid.x = canvas.width + asteroid.size;
                    if (asteroid.x > canvas.width + asteroid.size) asteroid.x = -asteroid.size;
                    if (asteroid.y < -asteroid.size) asteroid.y = canvas.height + asteroid.size;
                    if (asteroid.y > canvas.height + asteroid.size) asteroid.y = -asteroid.size;
                });

                // Update particles
                for (let i = self.gameParticles.length - 1; i >= 0; i--) {
                    const p = self.gameParticles[i];
                    p.x += p.vx;
                    p.y += p.vy;
                    p.life -= p.decay;
                    p.vx *= 0.98;
                    p.vy *= 0.98;

                    if (p.life <= 0) {
                        self.gameParticles.splice(i, 1);
                    }
                }

                // Update bullets
                for (let i = self.gameBullets.length - 1; i >= 0; i--) {
                    const bullet = self.gameBullets[i];
                    bullet.x += bullet.vx;
                    bullet.y += bullet.vy;
                    bullet.life--;

                    if (bullet.x < 0) bullet.x = canvas.width;
                    if (bullet.x > canvas.width) bullet.x = 0;
                    if (bullet.y < 0) bullet.y = canvas.height;
                    if (bullet.y > canvas.height) bullet.y = 0;

                    if (bullet.life <= 0) {
                        self.gameBullets.splice(i, 1);
                    }
                }

                // Collision detection
                if (self.gameState === String.fromCharCode(112,108,97,121,105,110,103)) {
                    const ship = self.gameShip;

                    // Ship vs Asteroids
                    self.gameAsteroids.forEach(asteroid => {
                        if (self.checkCollision(ship, asteroid, ship.size, asteroid.size)) {
                            self.killShip();
                        }
                    });

                    // Bullets vs Asteroids
                    for (let i = self.gameBullets.length - 1; i >= 0; i--) {
                        const bullet = self.gameBullets[i];
                        for (let j = self.gameAsteroids.length - 1; j >= 0; j--) {
                            const asteroid = self.gameAsteroids[j];
                            if (self.checkCollision(bullet, asteroid, 2, asteroid.size)) {
                                self.createExplosion(asteroid.x, asteroid.y, asteroid.color, 12);
                                self.splitAsteroid(asteroid);
                                self.gameBullets.splice(i, 1);
                                self.gameAsteroids.splice(j, 1);
                                self.gameScore += 10;
                                break;
                            }
                        }
                    }
                }

                // Draw particles
                self.gameParticles.forEach(p => {
                    ctx.save();
                    ctx.globalAlpha = p.life;
                    const color = p.color || '#ff007f';
                    ctx.fillStyle = color;
                    ctx.shadowBlur = 10;
                    ctx.shadowColor = color;
                    ctx.beginPath();
                    ctx.arc(p.x, p.y, p.size || 2, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.restore();
                });

                // Draw asteroids
                self.gameAsteroids.forEach(asteroid => {
                    self.drawAsteroid(asteroid);
                });

                // Draw bullets
                self.gameBullets.forEach(bullet => {
                    ctx.save();
                    ctx.fillStyle = '#00ffff';
                    ctx.shadowBlur = 8;
                    ctx.shadowColor = '#00ffff';
                    ctx.beginPath();
                    ctx.arc(bullet.x, bullet.y, 2, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.restore();
                });

                // Draw ship
                if (self.gameState === String.fromCharCode(112,108,97,121,105,110,103) ||
                    (self.gameState === String.fromCharCode(100,101,97,100) && Math.floor(self.gameRespawnTimer / 10) % 2)) {
                    const ship = self.gameShip;
                    ctx.save();
                    ctx.translate(ship.x, ship.y);
                    ctx.rotate(ship.angle);
                    ctx.strokeStyle = '#00ffff';
                    ctx.lineWidth = 2;
                    ctx.shadowBlur = 10;
                    ctx.shadowColor = '#00ffff';
                    ctx.beginPath();
                    ctx.moveTo(ship.size, 0);
                    ctx.lineTo(-ship.size, -ship.size/2);
                    ctx.lineTo(-ship.size/2, 0);
                    ctx.lineTo(-ship.size, ship.size/2);
                    ctx.closePath();
                    ctx.stroke();
                    ctx.restore();
                }

                requestAnimationFrame(gameLoop);
            }

            gameLoop();
        }
    };
})();
