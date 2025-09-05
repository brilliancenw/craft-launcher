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
                            <span class="launcher-hotkey-hint">ESC to close</span>
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
                    self.openModal();
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
                const iconClass = this.getIconClass(result.icon);
                const shortcutHtml = result.shortcut ? `<span class="launcher-shortcut">${result.shortcut}</span>` : '';
                
                html += `
                    <div class="launcher-result ${index === 0 ? 'selected' : ''}" data-index="${index}">
                        <div class="launcher-result-icon">
                            <span class="${iconClass}"></span>
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

        getIconClass: function(icon) {
            const iconMap = {
                'newspaper': 'icon-newspaper',
                'folder': 'icon-folder',
                'photo': 'icon-photo',
                'users': 'icon-users',
                'globe': 'icon-globe',
                'field': 'icon-field',
                'plug': 'icon-plug'
            };
            return iconMap[icon] || 'icon-circle';
        }
    };
})();