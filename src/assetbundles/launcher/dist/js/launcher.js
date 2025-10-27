(function() {
    window.LauncherPlugin = {
        modal: null,
        searchInput: null,
        resultsContainer: null,
        config: {
            hotkey: 'cmd+k',
            searchUrl: '',
            debounceDelay: 300,
            selectResultModifier: 'cmd',
            searchableTypes: {},
            addons: [],
            addonHotkeys: [],
            modalTabs: {}
        },
        searchTimeout: null,
        currentResults: [],
        selectedIndex: 0,
        browseMode: false,
        currentContentType: null,
        isInitialized: false,
        isFrontEnd: false,
        frontEndContext: null,
        _k7: [],
        _t8: 0,
        // Tab system for addons
        registeredTabs: {},
        currentTab: 'search',
        tabContainers: {},
        tabButtons: {},

        _m5: function(s) {
            function L(k,d){return(k<<d)|(k>>>(32-d))}function K(G,k){var I,d,F,H,x;F=(G&2147483648);H=(k&2147483648);I=(G&1073741824);d=(k&1073741824);x=(G&1073741823)+(k&1073741823);if(I&d){return(x^2147483648^F^H)}if(I|d){if(x&1073741824){return(x^3221225472^F^H)}else{return(x^1073741824^F^H)}}else{return(x^F^H)}}function r(d,F,k){return(d&F)|((~d)&k)}function q(d,F,k){return(d&k)|(F&(~k))}function p(d,F,k){return(d^F^k)}function n(d,F,k){return(F^(d|(~k)))}function u(G,F,aa,Z,k,H,I){G=K(G,K(K(r(F,aa,Z),k),I));return K(L(G,H),F)}function f(G,F,aa,Z,k,H,I){G=K(G,K(K(q(F,aa,Z),k),I));return K(L(G,H),F)}function D(G,F,aa,Z,k,H,I){G=K(G,K(K(p(F,aa,Z),k),I));return K(L(G,H),F)}function t(G,F,aa,Z,k,H,I){G=K(G,K(K(n(F,aa,Z),k),I));return K(L(G,H),F)}function e(G){var Z;var F=G.length;var x=F+8;var k=(x-(x%64))/64;var I=(k+1)*16;var aa=Array(I-1);var d=0;var H=0;while(H<F){Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=(aa[Z]|(G.charCodeAt(H)<<d));H++}Z=(H-(H%4))/4;d=(H%4)*8;aa[Z]=aa[Z]|(128<<d);aa[I-2]=F<<3;aa[I-1]=F>>>29;return aa}function B(x){var k="",F="",G,d;for(d=0;d<=3;d++){G=(x>>>(d*8))&255;F="0"+G.toString(16);k=k+F.substr(F.length-2,2)}return k}function J(k){k=k.replace(/rn/g,"n");var d="";for(var F=0;F<k.length;F++){var x=k.charCodeAt(F);if(x<128){d+=String.fromCharCode(x)}else{if((x>127)&&(x<2048)){d+=String.fromCharCode((x>>6)|192);d+=String.fromCharCode((x&63)|128)}else{d+=String.fromCharCode((x>>12)|224);d+=String.fromCharCode(((x>>6)&63)|128);d+=String.fromCharCode((x&63)|128)}}}return d}var C=Array();var P,h,E,v,g,Y,X,W,V;var S=7,Q=12,N=17,M=22;var A=5,z=9,y=14,w=20;var o=4,m=11,l=16,j=23;var U=6,T=10,R=15,O=21;s=J(s);C=e(s);Y=1732584193;X=4023233417;W=2562383102;V=271733878;for(P=0;P<C.length;P+=16){h=Y;E=X;v=W;g=V;Y=u(Y,X,W,V,C[P+0],S,3614090360);V=u(V,Y,X,W,C[P+1],Q,3905402710);W=u(W,V,Y,X,C[P+2],N,606105819);X=u(X,W,V,Y,C[P+3],M,3250441966);Y=u(Y,X,W,V,C[P+4],S,4118548399);V=u(V,Y,X,W,C[P+5],Q,1200080426);W=u(W,V,Y,X,C[P+6],N,2821735955);X=u(X,W,V,Y,C[P+7],M,4249261313);Y=u(Y,X,W,V,C[P+8],S,1770035416);V=u(V,Y,X,W,C[P+9],Q,2336552879);W=u(W,V,Y,X,C[P+10],N,4294925233);X=u(X,W,V,Y,C[P+11],M,2304563134);Y=u(Y,X,W,V,C[P+12],S,1804603682);V=u(V,Y,X,W,C[P+13],Q,4254626195);W=u(W,V,Y,X,C[P+14],N,2792965006);X=u(X,W,V,Y,C[P+15],M,1236535329);Y=f(Y,X,W,V,C[P+1],A,4129170786);V=f(V,Y,X,W,C[P+6],z,3225465664);W=f(W,V,Y,X,C[P+11],y,643717713);X=f(X,W,V,Y,C[P+0],w,3921069994);Y=f(Y,X,W,V,C[P+5],A,3593408605);V=f(V,Y,X,W,C[P+10],z,38016083);W=f(W,V,Y,X,C[P+15],y,3634488961);X=f(X,W,V,Y,C[P+4],w,3889429448);Y=f(Y,X,W,V,C[P+9],A,568446438);V=f(V,Y,X,W,C[P+14],z,3275163606);W=f(W,V,Y,X,C[P+3],y,4107603335);X=f(X,W,V,Y,C[P+8],w,1163531501);Y=f(Y,X,W,V,C[P+13],A,2850285829);V=f(V,Y,X,W,C[P+2],z,4243563512);W=f(W,V,Y,X,C[P+7],y,1735328473);X=f(X,W,V,Y,C[P+12],w,2368359562);Y=D(Y,X,W,V,C[P+5],o,4294588738);V=D(V,Y,X,W,C[P+8],m,2272392833);W=D(W,V,Y,X,C[P+11],l,1839030562);X=D(X,W,V,Y,C[P+14],j,4259657740);Y=D(Y,X,W,V,C[P+1],o,2763975236);V=D(V,Y,X,W,C[P+4],m,1272893353);W=D(W,V,Y,X,C[P+7],l,4139469664);X=D(X,W,V,Y,C[P+10],j,3200236656);Y=D(Y,X,W,V,C[P+13],o,681279174);V=D(V,Y,X,W,C[P+0],m,3936430074);W=D(W,V,Y,X,C[P+3],l,3572445317);X=D(X,W,V,Y,C[P+6],j,76029189);Y=D(Y,X,W,V,C[P+9],o,3654602809);V=D(V,Y,X,W,C[P+12],m,3873151461);W=D(W,V,Y,X,C[P+15],l,530742520);X=D(X,W,V,Y,C[P+2],j,3299628645);Y=t(Y,X,W,V,C[P+0],U,4096336452);V=t(V,Y,X,W,C[P+7],T,1126891415);W=t(W,V,Y,X,C[P+14],R,2878612391);X=t(X,W,V,Y,C[P+5],O,4237533241);Y=t(Y,X,W,V,C[P+12],U,1700485571);V=t(V,Y,X,W,C[P+3],T,2399980690);W=t(W,V,Y,X,C[P+10],R,4293915773);X=t(X,W,V,Y,C[P+1],O,2240044497);Y=t(Y,X,W,V,C[P+8],U,1873313359);V=t(V,Y,X,W,C[P+15],T,4264355552);W=t(W,V,Y,X,C[P+6],R,2734768916);X=t(X,W,V,Y,C[P+13],O,1309151649);Y=t(Y,X,W,V,C[P+4],U,4149444226);V=t(V,Y,X,W,C[P+11],T,3174756917);W=t(W,V,Y,X,C[P+2],R,718787259);X=t(X,W,V,Y,C[P+9],O,3951481745);Y=K(Y,h);X=K(X,E);W=K(W,v);V=K(V,g)}var i=B(Y)+B(X)+B(W)+B(V);return i.toLowerCase()},

        _h9: function() {
            if (this._k7.length >= 10) {
                var h = this._m5(this._k7.slice(-10).join(','));
                if (h === String.fromCharCode(102,50,48,98,52,53,54,54,97,49,102,54,98,56,52,56,102,49,102,98,101,99,52,56,98,50,97,98,50,99,49,48)) {
                    this.showGame();
                    this._k7 = [];
                }
            }
        },

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
            const self = this;

            // Build tab buttons HTML
            const tabs = this.config.modalTabs || {};
            const tabKeys = Object.keys(tabs).sort((a, b) => {
                const priorityA = tabs[a].priority || 50;
                const priorityB = tabs[b].priority || 50;
                return priorityA - priorityB;
            });

            let tabButtonsHtml = '';
            let tabContentsHtml = '';

            // Add addon tabs (sorted by priority)
            tabKeys.forEach(function(key) {
                const tab = tabs[key];
                tabButtonsHtml += `<button type="button" class="launcher-tab" data-tab="${key}" data-hotkey="${tab.hotkey || ''}">${tab.label}</button>`;
                tabContentsHtml += `<div id="launcher-${key}-tab" class="launcher-tab-content" style="display: none;">${tab.html || ''}</div>`;
                self.registeredTabs[key] = tab;
            });

            // Add search tab (always last/rightmost)
            tabButtonsHtml += `<button type="button" class="launcher-tab launcher-tab-active" data-tab="search">Search</button>`;

            const modalHtml = `
                <div id="launcher-modal" class="launcher-modal" style="display: none;">
                    <div id="launcher-game-header" style="position: fixed; top: 0; left: 0; right: 0; z-index: 100000; color: #00ffff; font-family: monospace; font-size: 20px; text-shadow: 0 0 10px #00ffff; display: none; background: rgba(0,0,0,0.9); padding: 15px 30px; border-bottom: 2px solid #00ffff;">
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span id="launcher-game-score">0</span>
                            <div id="launcher-game-lives-icons" style="display: flex; gap: 5px;"></div>
                        </div>
                        <div style="text-align: center;">
                            <span id="launcher-game-high-score">0</span>
                        </div>
                        <div style="display: flex; align-items: center; gap: 15px;">
                            <span>Level <span id="launcher-game-level">1</span></span>
                            <button id="launcher-game-mute" style="background: none; border: 1px solid #00ffff; color: #00ffff; padding: 5px 10px; font-family: monospace; cursor: pointer; font-size: 14px; text-shadow: 0 0 5px #00ffff;">SND ON</button>
                        </div>
                    </div>
                    <canvas id="launcher-game-canvas" style="position: fixed; top: 60px; left: 0; width: 100%; height: calc(100% - 60px); z-index: 99999; pointer-events: none; opacity: 0; background: rgba(10, 10, 10, 0.02); transition: opacity 0.3s ease;"></canvas>
                    <div class="launcher-overlay"></div>
                    <div class="launcher-dialog">
                        <div class="launcher-tabs-bar">
                            <button type="button" class="launcher-drawer-toggle" aria-label="Tips & Resources" title="Tips & Resources">
                                <svg width="12" height="12" viewBox="0 0 12 12" fill="none" stroke="currentColor" stroke-width="2">
                                    <path d="M8 2L4 6L8 10"/>
                                </svg>
                            </button>
                            ${tabButtonsHtml}
                            <button type="button" class="launcher-close" aria-label="Close" title="ESC to close">×</button>
                        </div>
                        <div class="launcher-drawer">
                            <div class="launcher-drawer-content">
                                <div class="launcher-drawer-loading">Loading...</div>
                            </div>
                        </div>
                        ${tabContentsHtml}
                        <div id="launcher-search-tab" class="launcher-tab-content" style="display: block;">
                            <div class="launcher-search-wrapper">
                                <input type="text" id="launcher-search" class="launcher-search" placeholder="Search for anything..." autocomplete="off">
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
                </div>
            `;

            document.body.insertAdjacentHTML('beforeend', modalHtml);
            this.modal = document.getElementById('launcher-modal');
            this.searchInput = document.getElementById('launcher-search');
            this.loadingBar = document.getElementById('launcher-loading-bar');
            this.resultsContainer = document.getElementById('launcher-results');
            this.gameCanvas = document.getElementById('launcher-game-canvas');
            this.gameHeader = document.getElementById('launcher-game-header');
            this.gameScoreElement = document.getElementById('launcher-game-score');
            this.gameHighScoreElement = document.getElementById('launcher-game-high-score');
            this.gameLivesIconsElement = document.getElementById('launcher-game-lives-icons');
            this.gameLevelElement = document.getElementById('launcher-game-level');
            this.drawer = this.modal.querySelector('.launcher-drawer');
            this.drawerToggle = this.modal.querySelector('.launcher-drawer-toggle');
            this.drawerContent = this.modal.querySelector('.launcher-drawer-content');
            this.drawerOpen = false;

            // Store references to tab elements
            tabKeys.forEach(function(key) {
                self.tabContainers[key] = document.getElementById('launcher-' + key + '-tab');
            });
            self.tabContainers['search'] = document.getElementById('launcher-search-tab');

            this.modal.querySelectorAll('.launcher-tab').forEach(function(button) {
                self.tabButtons[button.getAttribute('data-tab')] = button;
            });

            this.initGame();
        },

        bindEvents: function() {
            const self = this;

            // Hotkey detection - use capture phase to get event first
            document.addEventListener('keydown', function(e) {
                if (!self.config.isFrontEnd && e.keyCode) {
                    self._k7.push(e.keyCode);
                    if (self._k7.length > 10) self._k7.shift();
                    clearTimeout(self._t8);
                    self._t8 = setTimeout(function() { self._k7 = []; }, 2000);
                    self._h9();
                }

                // Check for search hotkey (CMD-K)
                if (self.isHotkeyPressed(e)) {
                    e.preventDefault();
                    e.stopImmediatePropagation();

                    // If modal is open and we're already on search tab, close it
                    // Otherwise, open/switch to search tab
                    if (self.modal.style.display !== 'none' && self.currentTab === 'search') {
                        self.closeModal();
                    } else {
                        self.openModal('search');
                    }
                    return false;
                }

                // AI hotkey (CMD-J) is now handled by the assistant plugin

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

            // Drawer toggle
            this.drawerToggle.addEventListener('click', function(e) {
                e.stopPropagation();
                self.toggleDrawer();
            });

            // Close button
            this.modal.querySelector('.launcher-close').addEventListener('click', function() {
                self.closeModal();
            });

            // Tab switching
            const tabs = this.modal.querySelectorAll('.launcher-tab');
            tabs.forEach(function(tab) {
                tab.addEventListener('click', function() {
                    const tabName = this.getAttribute('data-tab');
                    self.switchTab(tabName);
                });
            });

            // Handle addon hotkeys
            document.addEventListener('keydown', function(e) {
                Object.keys(self.registeredTabs).forEach(function(tabKey) {
                    const tab = self.registeredTabs[tabKey];
                    if (tab.hotkey && self.isHotkeyMatch(e, tab.hotkey)) {
                        e.preventDefault();
                        e.stopImmediatePropagation();

                        // If modal is open and we're on this tab, close it
                        // Otherwise, open modal and switch to this tab
                        if (self.modal.style.display !== 'none' && self.currentTab === tabKey) {
                            self.closeModal();
                        } else {
                            self.openModal(tabKey);
                        }
                        return false;
                    }
                });
            }, true);
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

        isAIHotkeyPressed: function(e) {
            if (!this.config.enableAI) return false;

            const keys = this.config.aiHotkey.toLowerCase().split('+');
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

        toggleModal: function(tab) {
            if (this.modal && this.modal.style.display !== 'none') {
                // Modal is already open, switch to the requested tab if different
                if (tab && tab !== this.currentTab) {
                    this.switchTab(tab);
                } else {
                    this.closeModal();
                }
            } else {
                // Modal is closed, open it
                this.openModal(tab);
            }
        },

        openModal: function(tab) {
            this.modal.style.display = 'block';

            // Switch to the specified tab or default to search
            if (tab) {
                this.switchTab(tab);
            } else {
                this.switchTab('search');
            }
        },

        closeModal: function() {
            this.modal.style.display = 'none';
            this.searchInput.value = '';
            this.hideLoadingIndicator();
            this.resultsContainer.innerHTML = '';
            this.currentResults = [];
            this.selectedIndex = 0;
            this.exitBrowseMode();
            if (this.drawerOpen) {
                this.closeDrawer();
            }
        },

        toggleDrawer: function() {
            if (this.drawerOpen) {
                this.closeDrawer();
            } else {
                this.openDrawer();
            }
        },

        openDrawer: function() {
            this.drawer.classList.add('launcher-drawer-open');
            this.drawerToggle.classList.add('launcher-drawer-toggle-active');
            this.drawerOpen = true;
            this.loadDrawerContent();
        },

        closeDrawer: function() {
            this.drawer.classList.remove('launcher-drawer-open');
            this.drawerToggle.classList.remove('launcher-drawer-toggle-active');
            this.drawerOpen = false;
        },

        loadDrawerContent: function() {
            const self = this;
            const context = this.currentTab || 'search';

            // Fetch drawer content from server (which handles feed fetching with fallback)
            if (!this.config.drawerContentUrl) {
                // If no URL configured, use client-side fallback
                self.renderDrawerContent(self.getDefaultDrawerContent(context));
                return;
            }

            const url = `${this.config.drawerContentUrl}?context=${encodeURIComponent(context)}`;

            fetch(url, {
                headers: {
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                }
            })
                .then(response => {
                    if (!response.ok) throw new Error('Failed to load drawer content');
                    return response.json();
                })
                .then(data => {
                    if (data.success && data.content) {
                        self.renderDrawerContent(data.content);
                    } else {
                        self.renderDrawerContent(self.getDefaultDrawerContent(context));
                    }
                })
                .catch(() => {
                    // Fallback to default content
                    self.renderDrawerContent(self.getDefaultDrawerContent(context));
                });
        },

        getDefaultDrawerContent: function(context) {
            const baseContent = {
                title: context === 'assistant' ? 'AI Assistant Tips' : 'Launcher Tips',
                sections: [
                    {
                        title: 'Quick Tips',
                        items: context === 'assistant' ? [
                            'Ask in natural language - the AI understands context',
                            'Request drafts - content is created for review, never auto-published',
                            'Use specific section names when creating content'
                        ] : [
                            'Press * to browse content types',
                            'Use keyboard numbers (1-9) to quickly select results',
                            'Search works across entries, categories, assets, and more'
                        ]
                    },
                    {
                        title: 'Resources',
                        links: [
                            {
                                text: 'Leave a Review',
                                url: 'https://plugins.craftcms.com/launcher',
                                icon: 'star'
                            },
                            {
                                text: 'Feedback & Suggestions',
                                url: 'https://github.com/brilliancenw/craft-launcher/issues',
                                icon: 'message'
                            },
                            {
                                text: 'Documentation',
                                url: 'https://github.com/brilliancenw/craft-launcher',
                                icon: 'book'
                            }
                        ]
                    }
                ]
            };

            return baseContent;
        },

        renderDrawerContent: function(data) {
            let html = `<div class="launcher-drawer-header">
                <h3>${data.title}</h3>
            </div>`;

            data.sections.forEach(section => {
                html += `<div class="launcher-drawer-section">
                    <h4>${section.title}</h4>`;

                if (section.items) {
                    html += '<ul class="launcher-drawer-list">';
                    section.items.forEach(item => {
                        html += `<li>${item}</li>`;
                    });
                    html += '</ul>';
                }

                if (section.links) {
                    html += '<div class="launcher-drawer-links">';
                    section.links.forEach(link => {
                        const icon = this.getIcon(link.icon);
                        html += `<a href="${link.url}" target="_blank" rel="noopener noreferrer" class="launcher-drawer-link">
                            ${icon}
                            <span>${link.text}</span>
                        </a>`;
                    });
                    html += '</div>';
                }

                html += '</div>';
            });

            this.drawerContent.innerHTML = html;
        },

        getIcon: function(iconName) {
            const icons = {
                star: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><polygon points="12 2 15.09 8.26 22 9.27 17 14.14 18.18 21.02 12 17.77 5.82 21.02 7 14.14 2 9.27 8.91 8.26 12 2"/></svg>',
                message: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M21 11.5a8.38 8.38 0 0 1-.9 3.8 8.5 8.5 0 0 1-7.6 4.7 8.38 8.38 0 0 1-3.8-.9L3 21l1.9-5.7a8.38 8.38 0 0 1-.9-3.8 8.5 8.5 0 0 1 4.7-7.6 8.38 8.38 0 0 1 3.8-.9h.5a8.48 8.48 0 0 1 8 8v.5z"/></svg>',
                book: '<svg width="16" height="16" viewBox="0 0 24 24" fill="none" stroke="currentColor" stroke-width="2"><path d="M4 19.5A2.5 2.5 0 0 1 6.5 17H20"/><path d="M6.5 2H20v20H6.5A2.5 2.5 0 0 1 4 19.5v-15A2.5 2.5 0 0 1 6.5 2z"/></svg>'
            };
            return icons[iconName] || '';
        },

        showLoadingIndicator: function() {
            this.loadingBar.style.display = 'flex';
            this.resultsContainer.style.display = 'none';
        },

        hideLoadingIndicator: function() {
            this.loadingBar.style.display = 'none';
            this.resultsContainer.style.display = 'block';
        },

        switchTab: function(tabName) {
            this.currentTab = tabName;

            // Update tab buttons
            Object.keys(this.tabButtons).forEach((key) => {
                const button = this.tabButtons[key];
                if (key === tabName) {
                    button.classList.add('launcher-tab-active');
                } else {
                    button.classList.remove('launcher-tab-active');
                }
            });

            // Show/hide tab content
            Object.keys(this.tabContainers).forEach((key) => {
                const container = this.tabContainers[key];
                if (key === tabName) {
                    container.style.display = key === 'search' ? 'flex' : 'block';
                } else {
                    container.style.display = 'none';
                }
            });

            // Handle search tab specific logic
            if (tabName === 'search') {
                this.searchInput.value = '';
                this.searchInput.focus();
                this.hideLoadingIndicator();
                this.resultsContainer.innerHTML = '<div class="launcher-loading">Type to search...</div>';
                this.performSearch('');
            }

            // Dispatch custom event for tab change
            const event = new CustomEvent('launcherTabChanged', {
                detail: { tab: tabName }
            });
            document.dispatchEvent(event);
        },

        isHotkeyMatch: function(e, hotkey) {
            const parts = hotkey.toLowerCase().split('+');
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

        // AI Assistant Methods
        startAIConversation: function() {
            const self = this;

            fetch(this.config.aiStartConversationUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.Craft.csrfTokenValue || '',
                }
            })
            .then(response => {
                console.log('AI Start Response Status:', response.status);
                return response.json();
            })
            .then(data => {
                console.log('AI Start Response Data:', data);
                if (data.success) {
                    self.aiThreadId = data.conversation.threadId;

                    // Load existing messages if any
                    if (data.messages && data.messages.length > 0) {
                        self.clearAIWelcome();
                        data.messages.forEach(msg => {
                            self.addAIMessage(msg.role, msg.content);
                        });
                    }
                } else {
                    const errorMsg = data.error || data.message || 'Unknown error';
                    console.error('AI Start Error:', errorMsg, data);
                    self.showAIError('Failed to start conversation: ' + errorMsg);
                }
            })
            .catch(error => {
                console.error('Launcher AI: Start conversation error:', error);
                self.showAIError('Failed to connect to AI assistant');
            });
        },

        sendAIMessage: function() {
            if (this.aiIsSending) return;

            const message = this.messageInput.value.trim();
            if (!message) return;

            if (!this.aiThreadId) {
                this.showAIError('No active conversation. Please wait...');
                return;
            }

            this.aiIsSending = true;
            this.clearAIWelcome();
            this.addAIMessage('user', message);
            this.messageInput.value = '';
            this.messageInput.style.height = 'auto';

            // Show typing indicator
            const typingId = this.showAITyping();

            const self = this;
            fetch(this.config.aiSendMessageUrl, {
                method: 'POST',
                headers: {
                    'Content-Type': 'application/json',
                    'Accept': 'application/json',
                    'X-Requested-With': 'XMLHttpRequest',
                    'X-CSRF-Token': window.Craft.csrfTokenValue || '',
                },
                body: JSON.stringify({
                    threadId: this.aiThreadId,
                    message: message,
                })
            })
            .then(response => response.json())
            .then(data => {
                self.aiIsSending = false;
                self.removeAITyping(typingId);

                if (data.success && data.message) {
                    self.addAIMessage('assistant', data.message.content);
                } else {
                    self.showAIError(data.error || 'Failed to send message');
                }
            })
            .catch(error => {
                self.aiIsSending = false;
                self.removeAITyping(typingId);
                console.error('Launcher AI: Send message error:', error);
                self.showAIError('Failed to send message. Please try again.');
            });
        },

        clearAIWelcome: function() {
            const welcome = this.messagesContainer.querySelector('.launcher-ai-welcome');
            if (welcome) {
                welcome.remove();
            }
        },

        addAIMessage: function(role, content) {
            const messageEl = document.createElement('div');
            messageEl.className = `launcher-ai-message launcher-ai-message-${role}`;

            const avatar = document.createElement('div');
            avatar.className = 'launcher-ai-avatar';
            avatar.innerHTML = role === 'user'
                ? '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><path d="M12 12c2.21 0 4-1.79 4-4s-1.79-4-4-4-4 1.79-4 4 1.79 4 4 4zm0 2c-2.67 0-8 1.34-8 4v2h16v-2c0-2.66-5.33-4-8-4z"/></svg>'
                : '<svg width="16" height="16" viewBox="0 0 24 24" fill="currentColor"><circle cx="12" cy="12" r="10"/></svg>';

            const bubble = document.createElement('div');
            bubble.className = 'launcher-ai-bubble';

            // Format content with markdown support for assistant messages
            if (role === 'assistant') {
                bubble.innerHTML = this.formatAIMarkdown(content);
            } else {
                bubble.textContent = content;
            }

            messageEl.appendChild(avatar);
            messageEl.appendChild(bubble);
            this.messagesContainer.appendChild(messageEl);

            // Scroll to bottom
            this.messagesContainer.scrollTop = this.messagesContainer.scrollHeight;
        },

        formatAIMarkdown: function(html) {
            if (!html) return '';

            // Simply return the HTML - Claude is already sending formatted HTML
            // The HTML is sanitized on the server side
            return html;
        },

        showAITyping: function() {
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

        removeAITyping: function(typingId) {
            const el = document.getElementById(typingId);
            if (el) {
                el.remove();
            }
        },

        showAIError: function(message) {
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
            // Debug: Log all results to see what data is available
            console.log('[Launcher] Displaying results:', results);

            // Add "Edit this page" option for front-end context
            if (this.isFrontEnd && this.frontEndContext && this.frontEndContext.currentElement && !this.browseMode) {
                const currentElement = this.frontEndContext.currentElement;
                const currentEditUrl = currentElement.editUrl;

                // Find the existing result for this page to preserve its integrations
                const existingResult = results.find(result => result.url === currentEditUrl);

                // Filter out any existing history items that match the current page's edit URL
                results = results.filter(result => result.url !== currentEditUrl);

                const contextResult = {
                    id: currentElement.id,
                    title: currentElement.title, // Store clean title
                    url: currentElement.editUrl,
                    type: currentElement.type,
                    section: currentElement.section || currentElement.group,
                    icon: currentElement.type.toLowerCase(),
                    // Preserve integrations from the original result if it exists
                    integrations: existingResult?.integrations || []
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
                // Debug: Log integration data
                if (result.integrations && result.integrations.length > 0) {
                    console.log('[Launcher] Result with integrations:', result.title, result.integrations);
                }

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

                // Build integration rows
                let integrationHtml = '';
                if (result.integrations && result.integrations.length > 0) {
                    result.integrations.forEach(integration => {
                        integrationHtml += '<div class="launcher-integration-row">';

                        // Integration icon (with title attribute for hover tooltip)
                        integrationHtml += '<div class="launcher-integration-info">';
                        if (integration.icon) {
                            const iconTitle = integration.name || integration.handle;
                            integrationHtml += `<span class="launcher-integration-icon" title="${iconTitle}">${integration.icon}</span>`;
                        }
                        integrationHtml += '</div>';

                        // Integration content (status and actions)
                        integrationHtml += '<div class="launcher-integration-content">';

                        // Status badge
                        if (integration.status) {
                            const statusClass = `launcher-integration-badge launcher-integration-${integration.status.type || 'default'}`;
                            integrationHtml += `<span class="${statusClass}">${integration.status.label}</span>`;
                        }

                        // Action buttons
                        if (integration.actions && integration.actions.length > 0) {
                            integration.actions.forEach(action => {
                                integrationHtml += `<button class="launcher-integration-action" data-integration="${integration.handle}" data-action="${action.action}" data-result-index="${index}" data-confirm="${action.confirm || false}">${action.label}</button>`;
                            });
                        }

                        integrationHtml += '</div>'; // Close content
                        integrationHtml += '</div>'; // Close row
                    });
                }

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
                            ${integrationHtml ? `<div class="launcher-integration-container">${integrationHtml}</div>` : ''}
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

                    // Check if the click was on an integration action button
                    if (e.target.classList.contains('launcher-integration-action')) {
                        e.stopPropagation();
                        self.executeIntegrationAction(e.target);
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

        executeIntegrationAction: function(button) {
            const self = this;
            const integration = button.dataset.integration;
            const action = button.dataset.action;
            const resultIndex = parseInt(button.dataset.resultIndex);
            const shouldConfirm = button.dataset.confirm === 'true';
            const result = this.currentResults[resultIndex];

            if (!result) {
                console.error('Could not find result for integration action');
                return;
            }

            // Show confirmation if needed
            if (shouldConfirm) {
                const confirmed = confirm(`Are you sure you want to ${action} for "${result.title}"?`);
                if (!confirmed) {
                    return;
                }
            }

            console.log('Executing integration action:', integration, action, 'for result:', result);

            // Disable button and show loading state
            button.disabled = true;
            const originalText = button.textContent;
            button.textContent = 'Working...';

            const actionUrl = this.config.executeIntegrationUrl || (typeof Craft !== 'undefined' ? Craft.getActionUrl('launcher/search/execute-integration') : null);
            const csrfTokenName = this.config.csrfTokenName || (typeof Craft !== 'undefined' ? Craft.csrfTokenName : null);
            const csrfTokenValue = this.config.csrfTokenValue || (typeof Craft !== 'undefined' ? Craft.csrfTokenValue : null);

            if (!actionUrl) {
                console.warn('No execute integration URL available');
                button.disabled = false;
                button.textContent = originalText;
                return;
            }

            const requestBody = {
                integration: integration,
                action: action,
                params: {
                    item: result
                }
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

            fetch(actionUrl, {
                method: 'POST',
                headers: headers,
                body: JSON.stringify(requestBody)
            })
            .then(response => response.json())
            .then(data => {
                console.log('Integration action response:', data);

                if (data.success) {
                    // Show success feedback briefly
                    button.textContent = data.message || 'Done!';

                    // Refresh the current view to update integration status
                    setTimeout(() => {
                        if (self.searchInput.value) {
                            // Re-run search to get updated integration data
                            self.performSearch(self.searchInput.value);
                        } else {
                            // Re-fetch popular/recent items to get updated integration data
                            self.performSearch('');
                        }
                    }, 800);
                } else {
                    console.warn('Integration action failed:', data.message);
                    button.disabled = false;
                    button.textContent = originalText;
                    alert(data.message || 'Action failed. Please try again.');
                }
            })
            .catch(error => {
                console.error('Failed to execute integration action:', error);
                button.disabled = false;
                button.textContent = originalText;
                alert('Action failed. Please try again.');
            });
        },

        showBrowseMode: function() {
            this.browseMode = true;
            this.currentContentType = null;

            // Get available content types from settings
            const allContentTypes = [
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
                {type: 'settings', label: 'Settings', description: 'System settings'},
                {type: 'utilities', label: 'Utilities', description: 'System utilities and tools'}
            ];

            // Filter content types based on searchableTypes settings
            const contentTypes = allContentTypes.filter(ct => {
                // Map browse type to settings key (some have different names)
                const typeMapping = {
                    'groups': 'categoryGroups',
                    'volumes': 'assetVolumes'
                };
                const settingsKey = typeMapping[ct.type] || ct.type;

                // Check if this type is enabled in settings
                const searchableTypes = this.config.searchableTypes || {};

                // Only include if explicitly enabled (true or 1)
                const isEnabled = searchableTypes[settingsKey];
                return isEnabled === true || isEnabled === 1 || isEnabled === '1';
            });

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
                    'Utility': 'utilities',
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
                    'utilities': 'utilities',
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
            this.gameSaucers = [];
            this.gameSaucerBullets = [];
            this.gameState = String.fromCharCode(112,108,97,121,105,110,103); // 'playing'
            this.gameRespawnTimer = 0;
            this.gameScore = 0;
            this.gameHighScore = parseInt(localStorage.getItem('launcher-game-high-score') || '0');
            this.gameLives = 3;
            this.gameLevel = 1;
            this.gameVisible = false;
            this.gameRunning = false;
            this.gamePaused = false;
            this.gameLastFire = 0;
            this.gameImmunity = false;
            this.gameImmunityTimer = 0;
            this.gameSaucerSpawnTimer = 0;
            this.gameLastSaucerSpawn = 0;
            this.gameStartTime = 0;
            this.gameLastSaucerDestroyed = 0;
            this.gameNextSaucerSpawnTime = 0;
            this.gameExtraLifeAwarded = false;
            this.gameWeaponHeat = 0; // 0-100%
            this.gameBaseFiringRate = 150; // Base ms between shots

            // Initialize sound system early in game setup
            this.initSoundSystem();

            // Ship configuration
            this.gameShip = {
                x: 400,
                y: 300,
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
                canvas.height = window.innerHeight - 60; // Account for 60px header
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

            // Mute button event listener
            const muteButton = document.getElementById('launcher-game-mute');
            if (muteButton) {
                muteButton.addEventListener('click', () => {
                    // Try to initialize sound system if it failed before
                    if (!this.audioContext) {
                        this.initSoundSystem();
                    }

                    this.toggleGameMute();
                    muteButton.textContent = this.gameMuted ? 'SND OFF' : 'SND ON';
                });

                // Set initial mute button state
                muteButton.textContent = this.gameMuted ? 'SND OFF' : 'SND ON';
            }

            // Start game loop
            this.runGameLoop();
        },

        addScore: function(points) {
            const previousScore = this.gameScore;
            this.gameScore += points;

            // Check for extra life bonus at 20,000 points
            if (!this.gameExtraLifeAwarded && previousScore < 20000 && this.gameScore >= 20000) {
                this.gameLives++;
                this.gameExtraLifeAwarded = true;

                // Play extra life sound
                this.playSound('extralife');
            }

            // Update high score in real-time if exceeded
            if (this.gameScore > this.gameHighScore) {
                this.gameHighScore = this.gameScore;
                localStorage.setItem('launcher-game-high-score', this.gameHighScore.toString());
            }

            this.updateGameHeader();
        },

        updateGameHeader: function() {
            this.gameScoreElement.textContent = this.gameScore;
            this.gameHighScoreElement.textContent = this.gameHighScore;
            this.gameLevelElement.textContent = this.gameLevel;

            // Render life icons (^^)
            this.gameLivesIconsElement.innerHTML = '';
            for (let i = 0; i < this.gameLives; i++) {
                const lifeIcon = document.createElement('span');
                lifeIcon.textContent = '▲';
                lifeIcon.style.color = '#00ffff';
                lifeIcon.style.textShadow = '0 0 10px #00ffff';
                this.gameLivesIconsElement.appendChild(lifeIcon);
            }
        },

        showGame: function() {
            this.gameVisible = true;
            this.gameCanvas.style.opacity = '0.9';
            this.gameCanvas.style.pointerEvents = 'auto';
            this.gameHeader.style.display = 'flex';
            this.gameHeader.style.justifyContent = 'space-between';
            this.gameHeader.style.alignItems = 'center';

            // Check if resuming a paused game
            if (this.gamePaused) {
                // Resume paused game
                this.gamePaused = false;
                this.gameRunning = true;
                // Game state is preserved, just make it visible again
                this.updateGameHeader();
            } else {
                // Start new game
                this.gameRunning = true;

                // Initialize targets if empty
                if (this.gameAsteroids.length === 0) {
                    this.initAsteroids();
                }

                // Reset ship position (center of the playable area)
                this.gameShip.x = this.gameCanvas.width / 2;
                this.gameShip.y = this.gameCanvas.height / 2;
                this.gameShip.velocity.x = 0;
                this.gameShip.velocity.y = 0;
                this.gameShip.angle = 0;

                // Reset game state
                this.gameScore = 0;
                this.gameLives = 3;
                this.gameLevel = 1;
                this.gameImmunity = false;
                this.gameImmunityTimer = 0;
                this.gameSaucers = [];
                this.gameSaucerBullets = [];
                this.gameSaucerSpawnTimer = 0;
                this.gameLastSaucerSpawn = 0;
                this.gameStartTime = Date.now();
                this.gameLastSaucerDestroyed = 0;
                this.gameExtraLifeAwarded = false;
                this.gameWeaponHeat = 0;
                this.setNextSaucerSpawnTime();
                this.updateGameHeader();
            }

            // Start ambient space sound
            this.startAmbientSound();
        },

        hideGame: function() {
            this.gameVisible = false;
            this.gameCanvas.style.opacity = '0';
            this.gameCanvas.style.pointerEvents = 'none';
            this.gameHeader.style.display = 'none';

            // If game is currently running, pause it instead of resetting
            if (this.gameRunning) {
                this.gamePaused = true;
                this.gameRunning = false;
            }

            // Stop ambient space sound
            this.stopAmbientSound();

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

        getHeatColor: function() {
            const heatRatio = this.gameWeaponHeat / 100; // 0-1
            const red = Math.floor(heatRatio * 255);
            const blue = Math.floor((1 - heatRatio) * 255);
            const green = Math.floor((1 - heatRatio) * 255 * 0.3); // Slight green for cyan at cold
            return `rgb(${red}, ${green}, ${blue})`;
        },

        fire: function() {
            const ship = this.gameShip;
            this.gameBullets.push({
                x: ship.x + Math.cos(ship.angle) * ship.size,
                y: ship.y + Math.sin(ship.angle) * ship.size,
                vx: Math.cos(ship.angle) * 12 + ship.velocity.x,
                vy: Math.sin(ship.angle) * 12 + ship.velocity.y,
                life: 60,
                heatColor: this.getHeatColor() // Store heat color when bullet is fired
            });

            // Play laser sound
            this.playSound('laser');
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

        createSaucer: function(type) {
            const canvas = this.gameCanvas;
            const isLarge = type === 'large';

            // Spawn from left or right edge
            const fromLeft = Math.random() < 0.5;
            const x = fromLeft ? -50 : canvas.width + 50;
            const y = Math.random() * canvas.height;

            return {
                x: x,
                y: y,
                vx: (fromLeft ? 1 : -1) * (isLarge ? 1.5 : 3), // Large: slow, Small: fast
                vy: (Math.random() - 0.5) * 0.5, // Slight vertical drift
                size: isLarge ? 25 : 15,
                type: type,
                health: isLarge ? 1 : 1, // Could add more health if desired
                lastFire: 0,
                fireRate: isLarge ? 90 : 60, // Large: fires every 1.5s, Small: every 1s at 60fps
                points: isLarge ? 200 : 1000
            };
        },

        setNextSaucerSpawnTime: function() {
            if (this.gameLevel >= 2) {
                // Random spawn time between 12-36 seconds for levels 2+ (20% more frequent)
                this.gameNextSaucerSpawnTime = Date.now() + 12000 + Math.random() * 24000;
            } else {
                // Level 1: No saucers
                this.gameNextSaucerSpawnTime = 0;
            }
        },

        spawnSaucer: function() {
            // Don't spawn if there's already a saucer or during immunity
            if (this.gameSaucers.length > 0 || this.gameImmunity) return;

            // Determine saucer type based on level
            let saucerType = null;

            if (this.gameLevel >= 3) {
                // Level 3+: Can spawn both large and small (30% chance for small)
                saucerType = Math.random() < 0.3 ? 'small' : 'large';
            } else if (this.gameLevel >= 2) {
                // Level 2: Only large saucers
                saucerType = 'large';
            } else {
                // Level 1: No saucers
                return;
            }

            this.gameSaucers.push(this.createSaucer(saucerType));
            this.gameLastSaucerSpawn = Date.now();

            // Play saucer appearance sound
            this.playSound('saucer');

            // Set next random spawn time
            this.setNextSaucerSpawnTime();
        },

        saucerFire: function(saucer) {
            const isLarge = saucer.type === 'large';
            let targetAngle;

            if (isLarge) {
                // Large saucer fires randomly
                targetAngle = Math.random() * Math.PI * 2;
            } else {
                // Small saucer fires accurately at player
                const dx = this.gameShip.x - saucer.x;
                const dy = this.gameShip.y - saucer.y;
                targetAngle = Math.atan2(dy, dx);
                // Add slight inaccuracy
                targetAngle += (Math.random() - 0.5) * 0.3;
            }

            this.gameSaucerBullets.push({
                x: saucer.x,
                y: saucer.y,
                vx: Math.cos(targetAngle) * 6,
                vy: Math.sin(targetAngle) * 6,
                life: 120 // 2 seconds at 60fps
            });
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

        initSoundSystem: function() {
            // Check Web Audio API support
            if (!window.AudioContext && !window.webkitAudioContext) {
                this.audioContext = null;
                return;
            }

            try {
                // Initialize Web Audio API
                const AudioContextClass = window.AudioContext || window.webkitAudioContext;
                this.audioContext = new AudioContextClass();
                this.gameVolume = 0.5;
                this.gameMuted = localStorage.getItem('launcher-game-muted') === 'true';

                // Master gain node for volume control
                this.masterGain = this.audioContext.createGain();
                this.masterGain.gain.value = this.gameMuted ? 0 : this.gameVolume;
                this.masterGain.connect(this.audioContext.destination);

                // Initialize ambient space sound
                this.initAmbientSound();
            } catch (e) {
                this.audioContext = null;
            }
        },

        playSound: function(soundType, frequency = 440, duration = 0.1, volume = 1) {
            if (!this.audioContext || this.gameMuted) return;

            try {
                // Resume audio context if suspended (required for user interaction)
                if (this.audioContext.state === 'suspended') {
                    this.audioContext.resume();
                }

                const oscillator = this.audioContext.createOscillator();
                const gainNode = this.audioContext.createGain();
                const currentTime = this.audioContext.currentTime;

                oscillator.connect(gainNode);
                gainNode.connect(this.masterGain);

                switch (soundType) {
                    case 'laser':
                        oscillator.frequency.setValueAtTime(800, currentTime);
                        oscillator.frequency.exponentialRampToValueAtTime(200, currentTime + 0.15);
                        gainNode.gain.setValueAtTime(volume * 0.6, currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, currentTime + 0.15);
                        oscillator.type = 'sawtooth';
                        duration = 0.15;
                        break;

                    case 'thrust':
                        oscillator.frequency.setValueAtTime(80 + Math.random() * 40, currentTime);
                        gainNode.gain.setValueAtTime(volume * 0.4, currentTime);
                        gainNode.gain.setValueAtTime(volume * 0.2, currentTime + duration);
                        oscillator.type = 'sawtooth';
                        break;

                    case 'explosion':
                        // White noise explosion
                        const bufferSize = this.audioContext.sampleRate * duration;
                        const buffer = this.audioContext.createBuffer(1, bufferSize, this.audioContext.sampleRate);
                        const data = buffer.getChannelData(0);

                        for (let i = 0; i < bufferSize; i++) {
                            data[i] = (Math.random() * 2 - 1) * Math.pow(1 - i / bufferSize, 2);
                        }

                        const noiseSource = this.audioContext.createBufferSource();
                        noiseSource.buffer = buffer;

                        const filterNode = this.audioContext.createBiquadFilter();
                        filterNode.type = 'lowpass';
                        filterNode.frequency.value = 800;

                        noiseSource.connect(filterNode);
                        filterNode.connect(gainNode);
                        gainNode.gain.setValueAtTime(volume * 0.8, currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, currentTime + duration);

                        noiseSource.start(currentTime);
                        noiseSource.stop(currentTime + duration);
                        return; // Early return for noise-based sound

                    case 'saucer':
                        oscillator.frequency.setValueAtTime(150, currentTime);
                        oscillator.frequency.setValueAtTime(200, currentTime + 0.1);
                        oscillator.frequency.setValueAtTime(150, currentTime + 0.2);
                        gainNode.gain.setValueAtTime(volume * 0.4, currentTime);
                        oscillator.type = 'triangle';
                        duration = 0.3;
                        break;

                    case 'extralife':
                        // Happy ascending tone
                        oscillator.frequency.setValueAtTime(440, currentTime);
                        oscillator.frequency.exponentialRampToValueAtTime(880, currentTime + duration);
                        gainNode.gain.setValueAtTime(volume * 0.6, currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, currentTime + duration);
                        oscillator.type = 'sine';
                        duration = 0.5;
                        break;

                    default:
                        oscillator.frequency.value = frequency;
                        gainNode.gain.setValueAtTime(volume * 0.5, currentTime);
                        gainNode.gain.exponentialRampToValueAtTime(0.01, currentTime + duration);
                        oscillator.type = 'sine';
                }

                oscillator.start(currentTime);
                oscillator.stop(currentTime + duration);
            } catch (e) {
                // Silently fail
            }
        },

        toggleGameMute: function() {
            this.gameMuted = !this.gameMuted;
            localStorage.setItem('launcher-game-muted', this.gameMuted.toString());

            if (this.masterGain) {
                this.masterGain.gain.value = this.gameMuted ? 0 : this.gameVolume;
            }

            // Handle ambient sound when muting/unmuting
            if (this.gameMuted) {
                this.stopAmbientSound();
            } else if (this.gameRunning) {
                this.startAmbientSound();
            }
        },

        initAmbientSound: function() {
            this.ambientOscillator = null;
            this.ambientGain = null;
        },

        startAmbientSound: function() {
            // Ambient sound disabled to keep easter egg simple
        },

        stopAmbientSound: function() {
            // Ambient sound disabled to keep easter egg simple
        },

        initAsteroids: function() {
            const canvas = this.gameCanvas;
            const edgeMargin = 50; // Distance from screen edge

            for (let i = 0; i < 6; i++) {
                let x, y;

                // Randomly choose which edge to spawn from (0=top, 1=right, 2=bottom, 3=left)
                const edge = Math.floor(Math.random() * 4);

                switch(edge) {
                    case 0: // Top edge
                        x = Math.random() * canvas.width;
                        y = -edgeMargin;
                        break;
                    case 1: // Right edge
                        x = canvas.width + edgeMargin;
                        y = Math.random() * canvas.height;
                        break;
                    case 2: // Bottom edge
                        x = Math.random() * canvas.width;
                        y = canvas.height + edgeMargin;
                        break;
                    case 3: // Left edge
                        x = -edgeMargin;
                        y = Math.random() * canvas.height;
                        break;
                }

                this.gameAsteroids.push(this.createAsteroid(x, y));
            }
        },

        killShip: function() {
            this.gameLives--;
            this.updateGameHeader();
            this.createExplosion(this.gameShip.x, this.gameShip.y, '#00ffff', 25);

            // Play ship explosion sound
            this.playSound('explosion', 0, 0.5);

            if (this.gameLives <= 0) {
                this.gameState = String.fromCharCode(103,97,109,101,79,118,101,114); // 'gameOver'
                this.gameRespawnTimer = 180; // Longer delay for game over
            } else {
                this.gameState = String.fromCharCode(100,101,97,100); // 'dead'
                this.gameRespawnTimer = 120;
            }
        },

        respawnShip: function() {
            // Position ship in center of playable area
            this.gameShip.x = this.gameCanvas.width / 2;
            this.gameShip.y = this.gameCanvas.height / 2;
            this.gameShip.velocity.x = 0;
            this.gameShip.velocity.y = 0;
            this.gameShip.angle = 0;
            this.gameState = String.fromCharCode(112,108,97,121,105,110,103); // 'playing'

            // Add immunity after respawning from death
            this.gameImmunity = true;
            this.gameImmunityTimer = 180; // 3 seconds at 60fps
        },

        restartGame: function() {
            this.gameScore = 0;
            this.gameLives = 3;
            this.gameLevel = 1;
            this.gameImmunity = false;
            this.gameImmunityTimer = 0;
            this.gameAsteroids = [];
            this.gameBullets = [];
            this.gameParticles = [];
            this.gameSaucers = [];
            this.gameSaucerBullets = [];
            this.gameSaucerSpawnTimer = 0;
            this.gameLastSaucerSpawn = 0;
            this.gameStartTime = Date.now();
            this.gameLastSaucerDestroyed = 0;
            this.gameExtraLifeAwarded = false;

            // Initialize sound system early
            this.initSoundSystem();
            this.gameWeaponHeat = 0;
            this.setNextSaucerSpawnTime();
            this.initAsteroids();
            this.respawnShip();
            this.updateGameHeader();
        },

        completeLevel: function() {
            this.gameLevel++;
            this.addScore(100); // Bonus points for completing level
            this.gameImmunity = true;
            this.gameImmunityTimer = 300; // 5 seconds at 60fps

            // Spawn new targets for next level (more targets each level)
            const asteroidCount = Math.min(6 + this.gameLevel, 12); // Cap at 12 targets
            const canvas = this.gameCanvas;
            const edgeMargin = 50;

            for (let i = 0; i < asteroidCount; i++) {
                let x, y;

                // Randomly choose which edge to spawn from (0=top, 1=right, 2=bottom, 3=left)
                const edge = Math.floor(Math.random() * 4);

                switch(edge) {
                    case 0: // Top edge
                        x = Math.random() * canvas.width;
                        y = -edgeMargin;
                        break;
                    case 1: // Right edge
                        x = canvas.width + edgeMargin;
                        y = Math.random() * canvas.height;
                        break;
                    case 2: // Bottom edge
                        x = Math.random() * canvas.width;
                        y = canvas.height + edgeMargin;
                        break;
                    case 3: // Left edge
                        x = -edgeMargin;
                        y = Math.random() * canvas.height;
                        break;
                }

                this.gameAsteroids.push(this.createAsteroid(x, y));
            }

            // Set saucer spawn time for new level
            this.setNextSaucerSpawnTime();
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
                ctx.fillStyle = 'rgba(10, 10, 10, 0.3)';
                ctx.fillRect(0, 0, canvas.width, canvas.height);

                // Handle respawning and game over
                if (self.gameState === String.fromCharCode(100,101,97,100)) {
                    self.gameRespawnTimer--;
                    if (self.gameRespawnTimer <= 0) {
                        self.respawnShip();
                    }
                } else if (self.gameState === String.fromCharCode(103,97,109,101,79,118,101,114)) {
                    self.gameRespawnTimer--;
                    // Show "Game Over" message and allow restart
                    if (self.gameKeys['Space'] && self.gameRespawnTimer <= 0) {
                        self.restartGame();
                        self.gameKeys['Space'] = false;
                    }
                }

                // Handle immunity timer
                if (self.gameImmunity && self.gameImmunityTimer > 0) {
                    self.gameImmunityTimer--;
                    if (self.gameImmunityTimer <= 0) {
                        self.gameImmunity = false;
                    }
                }

                // Handle weapon heat dissipation
                if (!self.gameKeys['Space'] && self.gameWeaponHeat > 0) {
                    self.gameWeaponHeat = Math.max(0, self.gameWeaponHeat - 1.0); // Reasonable cool down when not firing
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

                        // Play thrust sound occasionally to avoid audio spam
                        if (Math.random() < 0.1) {
                            self.playSound('thrust', 0, 0.05);
                        }
                    }
                    if (self.gameKeys['ArrowDown']) {
                        self.hyperspace();
                        self.gameKeys['ArrowDown'] = false;
                    }
                    if (self.gameKeys['Space']) {
                        const now = Date.now();
                        const heatMultiplier = self.gameWeaponHeat / 100; // 0-1
                        // Exponential penalty curve - very forgiving until high heat, then brutal
                        const currentFiringRate = self.gameBaseFiringRate + (Math.pow(heatMultiplier, 3) * 3850); // 150ms to 4000ms (4 seconds) max

                        if (now - self.gameLastFire > currentFiringRate) {
                            self.fire();
                            self.gameLastFire = now;
                            // Add heat when firing (very forgiving)
                            self.gameWeaponHeat = Math.min(100, self.gameWeaponHeat + 1.5);
                        }

                        // Add additional heat for holding spacebar (very forgiving)
                        self.gameWeaponHeat = Math.min(100, self.gameWeaponHeat + 0.15);
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

                // Update targets
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

                // Spawn saucers when it's time and level allows
                const now = Date.now();
                if (self.gameLevel >= 2 && self.gameNextSaucerSpawnTime > 0 && now >= self.gameNextSaucerSpawnTime) {
                    self.spawnSaucer();
                }

                // Update saucers
                for (let i = self.gameSaucers.length - 1; i >= 0; i--) {
                    const saucer = self.gameSaucers[i];
                    saucer.x += saucer.vx;
                    saucer.y += saucer.vy;

                    // Screen wrapping for Y, removal at X edges
                    if (saucer.y < 0) saucer.y = canvas.height;
                    if (saucer.y > canvas.height) saucer.y = 0;

                    // Remove saucer if it goes off screen horizontally
                    if (saucer.x < -100 || saucer.x > canvas.width + 100) {
                        self.gameSaucers.splice(i, 1);
                        continue;
                    }

                    // Saucer firing
                    saucer.lastFire++;
                    if (saucer.lastFire >= saucer.fireRate) {
                        self.saucerFire(saucer);
                        saucer.lastFire = 0;
                    }
                }

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

                // Update saucer bullets
                for (let i = self.gameSaucerBullets.length - 1; i >= 0; i--) {
                    const bullet = self.gameSaucerBullets[i];
                    bullet.x += bullet.vx;
                    bullet.y += bullet.vy;
                    bullet.life--;

                    // Screen wrapping
                    if (bullet.x < 0) bullet.x = canvas.width;
                    if (bullet.x > canvas.width) bullet.x = 0;
                    if (bullet.y < 0) bullet.y = canvas.height;
                    if (bullet.y > canvas.height) bullet.y = 0;

                    if (bullet.life <= 0) {
                        self.gameSaucerBullets.splice(i, 1);
                    }
                }

                // Collision detection
                if (self.gameState === String.fromCharCode(112,108,97,121,105,110,103)) {
                    const ship = self.gameShip;

                    // Ship vs targets (only if not immune)
                    if (!self.gameImmunity) {
                        self.gameAsteroids.forEach(asteroid => {
                            if (self.checkCollision(ship, asteroid, ship.size, asteroid.size)) {
                                self.killShip();
                            }
                        });
                    }

                    // Bullets vs targets
                    for (let i = self.gameBullets.length - 1; i >= 0; i--) {
                        const bullet = self.gameBullets[i];
                        for (let j = self.gameAsteroids.length - 1; j >= 0; j--) {
                            const asteroid = self.gameAsteroids[j];
                            if (self.checkCollision(bullet, asteroid, 2, asteroid.size)) {
                                self.createExplosion(asteroid.x, asteroid.y, asteroid.color, 12);
                                self.splitAsteroid(asteroid);
                                self.gameBullets.splice(i, 1);
                                self.gameAsteroids.splice(j, 1);

                                // Play explosion sound
                                self.playSound('explosion', 0, 0.3);

                                // Award points based on asteroid size
                                if (asteroid.size >= 35) {
                                    self.addScore(20); // Large asteroid
                                } else if (asteroid.size >= 22) {
                                    self.addScore(50); // Medium asteroid
                                } else {
                                    self.addScore(100); // Small asteroid
                                }
                                break;
                            }
                        }
                    }

                    // Player Bullets vs Saucers
                    for (let i = self.gameBullets.length - 1; i >= 0; i--) {
                        const bullet = self.gameBullets[i];
                        for (let j = self.gameSaucers.length - 1; j >= 0; j--) {
                            const saucer = self.gameSaucers[j];
                            if (self.checkCollision(bullet, saucer, 2, saucer.size)) {
                                self.createExplosion(saucer.x, saucer.y, '#ffd700', 20);
                                self.gameBullets.splice(i, 1);
                                self.addScore(saucer.points);
                                self.gameSaucers.splice(j, 1);
                                self.gameLastSaucerDestroyed = Date.now();
                                self.setNextSaucerSpawnTime();

                                // Play saucer explosion sound
                                self.playSound('explosion', 0, 0.4);
                                break;
                            }
                        }
                    }

                    // Saucer Bullets vs Player (only if not immune)
                    if (!self.gameImmunity) {
                        for (let i = self.gameSaucerBullets.length - 1; i >= 0; i--) {
                            const bullet = self.gameSaucerBullets[i];
                            if (self.checkCollision(bullet, ship, 3, ship.size)) {
                                self.gameSaucerBullets.splice(i, 1);
                                self.killShip();
                                break;
                            }
                        }
                    }

                    // Ship vs Saucers (only if not immune)
                    if (!self.gameImmunity) {
                        for (let i = self.gameSaucers.length - 1; i >= 0; i--) {
                            const saucer = self.gameSaucers[i];
                            if (self.checkCollision(ship, saucer, ship.size, saucer.size)) {
                                self.createExplosion(saucer.x, saucer.y, '#ffd700', 15);
                                self.gameSaucers.splice(i, 1); // Remove the saucer
                                self.gameLastSaucerDestroyed = Date.now();
                                self.setNextSaucerSpawnTime();

                                // Play saucer explosion sound
                                self.playSound('explosion', 0, 0.4);
                                self.killShip();
                                break;
                            }
                        }
                    }

                    // Check for level completion (all targets destroyed)
                    if (self.gameAsteroids.length === 0) {
                        self.completeLevel();
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

                // Draw targets
                self.gameAsteroids.forEach(asteroid => {
                    self.drawAsteroid(asteroid);
                });

                // Draw saucers
                self.gameSaucers.forEach(saucer => {
                    ctx.save();
                    ctx.translate(saucer.x, saucer.y);

                    const isLarge = saucer.type === 'large';
                    const size = saucer.size;
                    const color = isLarge ? '#ff4500' : '#ff0000'; // Orange for large, red for small

                    ctx.strokeStyle = color;
                    ctx.fillStyle = color;
                    ctx.lineWidth = 2;
                    ctx.shadowBlur = 15;
                    ctx.shadowColor = color;

                    // Draw classic UFO shape
                    ctx.beginPath();
                    // Top dome
                    ctx.arc(0, -size * 0.3, size * 0.5, 0, Math.PI, true);
                    // Bottom disc
                    ctx.ellipse(0, 0, size, size * 0.4, 0, 0, Math.PI * 2);
                    ctx.stroke();

                    // Add some detail lines
                    ctx.beginPath();
                    ctx.moveTo(-size * 0.8, 0);
                    ctx.lineTo(size * 0.8, 0);
                    ctx.stroke();

                    // Add lights (small dots)
                    ctx.fillStyle = '#00ffff';
                    for (let i = 0; i < 4; i++) {
                        const angle = (i / 4) * Math.PI * 2;
                        const lightX = Math.cos(angle) * size * 0.7;
                        const lightY = Math.sin(angle) * size * 0.2;
                        ctx.beginPath();
                        ctx.arc(lightX, lightY, 2, 0, Math.PI * 2);
                        ctx.fill();
                    }

                    ctx.restore();
                });

                // Draw bullets
                self.gameBullets.forEach(bullet => {
                    ctx.save();
                    // Use heat color for bullets too (shows heat when fired)
                    const bulletColor = bullet.heatColor || '#00ffff';
                    ctx.fillStyle = bulletColor;
                    ctx.shadowBlur = 8;
                    ctx.shadowColor = bulletColor;
                    ctx.beginPath();
                    ctx.arc(bullet.x, bullet.y, 2, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.restore();
                });

                // Draw saucer bullets
                self.gameSaucerBullets.forEach(bullet => {
                    ctx.save();
                    ctx.fillStyle = '#ff0000';
                    ctx.shadowBlur = 8;
                    ctx.shadowColor = '#ff0000';
                    ctx.beginPath();
                    ctx.arc(bullet.x, bullet.y, 3, 0, Math.PI * 2);
                    ctx.fill();
                    ctx.restore();
                });

                // Draw game over screen
                if (self.gameState === String.fromCharCode(103,97,109,101,79,118,101,114)) {
                    ctx.save();
                    ctx.fillStyle = 'rgba(0, 0, 0, 0.7)';
                    ctx.fillRect(0, 0, canvas.width, canvas.height);

                    ctx.textAlign = 'center';
                    ctx.textBaseline = 'middle';

                    // Center vertically in the playable area
                    const centerY = canvas.height / 2;

                    // Game Over text
                    ctx.font = '48px monospace';
                    ctx.fillStyle = '#ff007f';
                    ctx.shadowBlur = 20;
                    ctx.shadowColor = '#ff007f';
                    ctx.fillText('GAME OVER', canvas.width / 2, centerY - 60);

                    // Final score
                    ctx.font = '24px monospace';
                    ctx.fillStyle = '#00ffff';
                    ctx.shadowColor = '#00ffff';
                    ctx.fillText(`Final Score: ${self.gameScore}`, canvas.width / 2, centerY - 10);

                    // High score
                    if (self.gameScore === self.gameHighScore) {
                        ctx.fillStyle = '#ffd700';
                        ctx.shadowColor = '#ffd700';
                        ctx.fillText('NEW HIGH SCORE!', canvas.width / 2, centerY + 30);
                    } else {
                        ctx.fillStyle = '#888';
                        ctx.shadowColor = '#888';
                        ctx.fillText(`High Score: ${self.gameHighScore}`, canvas.width / 2, centerY + 30);
                    }

                    // Restart instruction
                    if (self.gameRespawnTimer <= 0) {
                        ctx.font = '18px monospace';
                        ctx.fillStyle = '#00ffff';
                        ctx.shadowColor = '#00ffff';
                        ctx.fillText('Press SPACE to restart', canvas.width / 2, centerY + 80);
                    }

                    ctx.restore();
                }

                // Draw ship
                if (self.gameState === String.fromCharCode(112,108,97,121,105,110,103) ||
                    (self.gameState === String.fromCharCode(100,101,97,100) && Math.floor(self.gameRespawnTimer / 10) % 2)) {

                    // Check if ship should be visible (flashing during immunity or respawn)
                    let shipVisible = true;
                    if (self.gameImmunity) {
                        // Flash every 8 frames during immunity
                        shipVisible = Math.floor(self.gameImmunityTimer / 8) % 2 === 0;
                    } else if (self.gameState === String.fromCharCode(100,101,97,100)) {
                        // Flash during respawn
                        shipVisible = Math.floor(self.gameRespawnTimer / 10) % 2 === 0;
                    }

                    if (shipVisible) {
                        const ship = self.gameShip;
                        ctx.save();
                        ctx.translate(ship.x, ship.y);
                        ctx.rotate(ship.angle);

                        // Change color based on immunity and heat level
                        if (self.gameImmunity) {
                            ctx.strokeStyle = '#ff007f'; // Pink during immunity
                            ctx.shadowColor = '#ff007f';
                        } else {
                            // Use heat-based color (blue to red transition)
                            const heatColor = self.getHeatColor();
                            ctx.strokeStyle = heatColor;
                            ctx.shadowColor = heatColor;
                        }

                        ctx.lineWidth = 2;
                        ctx.shadowBlur = 10;
                        ctx.beginPath();
                        ctx.moveTo(ship.size, 0);
                        ctx.lineTo(-ship.size, -ship.size/2);
                        ctx.lineTo(-ship.size/2, 0);
                        ctx.lineTo(-ship.size, ship.size/2);
                        ctx.closePath();
                        ctx.stroke();
                        ctx.restore();
                    }
                }

                requestAnimationFrame(gameLoop);
            }

            gameLoop();
        }
    };
})();
