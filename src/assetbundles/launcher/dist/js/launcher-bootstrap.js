/**
 * Rocket Launcher - Bootstrap Script
 *
 * This minimal script is safe to cache with full-page caching solutions
 * (Varnish, Blitz, Cloudflare CDN, etc.).
 *
 * It calls an action endpoint to check user authorization and, if authorized,
 * dynamically loads the full launcher assets.
 *
 * If the user is not authorized, nothing happens (silent failure).
 * No sensitive data (CSRF tokens, user preferences) is embedded in cached pages.
 */
(function() {
    'use strict';

    // Prevent multiple initializations
    if (window.__LauncherBootstrapped) {
        return;
    }
    window.__LauncherBootstrapped = true;

    // Get bootstrap URL from script data attribute
    var scriptTag = document.currentScript || (function() {
        var scripts = document.getElementsByTagName('script');
        return scripts[scripts.length - 1];
    })();

    var bootstrapUrl = scriptTag ? scriptTag.getAttribute('data-bootstrap-url') : null;
    if (!bootstrapUrl) {
        return;
    }

    /**
     * Load CSS dynamically
     * @param {string} url - URL of the CSS file
     */
    function loadCSS(url) {
        // Check if already loaded
        var existing = document.querySelector('link[href="' + url + '"]');
        if (existing) {
            return;
        }

        var link = document.createElement('link');
        link.rel = 'stylesheet';
        link.href = url;
        document.head.appendChild(link);
    }

    /**
     * Load JavaScript dynamically with callback
     * @param {string} url - URL of the JS file
     * @param {function} callback - Function to call after load
     */
    function loadJS(url, callback) {
        // Check if already loaded
        var existing = document.querySelector('script[src="' + url + '"]');
        if (existing) {
            // Script already exists, call callback if LauncherPlugin is ready
            if (window.LauncherPlugin) {
                callback();
            } else {
                existing.addEventListener('load', callback);
            }
            return;
        }

        var script = document.createElement('script');
        script.src = url;
        script.onload = callback;
        script.onerror = function() {
            // Silent failure
        };
        document.body.appendChild(script);
    }

    /**
     * Get context from page data attributes
     * Developers can add data-launcher-context to any element to provide
     * context about the current page (e.g., which entry is being viewed)
     * @returns {object}
     */
    function getPageContext() {
        var el = document.querySelector('[data-launcher-context]');
        if (el) {
            try {
                return JSON.parse(el.getAttribute('data-launcher-context'));
            } catch (e) {
                // Invalid JSON, ignore
            }
        }

        // Also check for context in a script tag (auto-inject mode)
        var contextScript = document.getElementById('launcher-context');
        if (contextScript) {
            try {
                return JSON.parse(contextScript.textContent);
            } catch (e) {
                // Invalid JSON, ignore
            }
        }

        return {};
    }

    /**
     * Bootstrap the launcher by calling the authorization endpoint
     */
    function bootstrap() {
        fetch(bootstrapUrl, {
            method: 'POST',
            headers: {
                'Accept': 'application/json',
                'X-Requested-With': 'XMLHttpRequest'
            },
            credentials: 'same-origin'
        })
        .then(function(response) {
            // 204 = not authorized, exit silently
            if (response.status === 204) {
                return null;
            }
            // 429 = rate limited, exit silently
            if (response.status === 429) {
                return null;
            }
            // Other errors, exit silently
            if (!response.ok) {
                return null;
            }
            return response.json();
        })
        .then(function(data) {
            if (!data || !data.success) {
                return;
            }

            // Load CSS first
            if (data.assets && data.assets.css) {
                loadCSS(data.assets.css);
            }

            // Load JS and initialize
            if (data.assets && data.assets.js) {
                loadJS(data.assets.js, function() {
                    if (window.LauncherPlugin && data.config) {
                        // Add asset URL and page context to config
                        data.config.assetUrl = data.assets.baseUrl;
                        data.config.frontEndContext = getPageContext();

                        // Initialize the launcher
                        window.LauncherPlugin.init(data.config);
                    }
                });
            }
        })
        .catch(function() {
            // Silent failure - no console output on production cached pages
            // This is intentional for security
        });
    }

    // Run when DOM is ready
    if (document.readyState === 'loading') {
        document.addEventListener('DOMContentLoaded', bootstrap);
    } else {
        // DOM already loaded
        bootstrap();
    }
})();
