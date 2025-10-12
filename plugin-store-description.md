# Craft Launcher

A universal search plugin that brings Spotlight-style navigation to your Craft CMS admin panel. Think Command Palette for VS Code, but designed specifically for Craft CMS with intelligent usage tracking and powerful plugin integrations.

Built by developers, for developers - we created this tool to streamline our own Craft workflow and are now sharing it with the community as a free utility.

## Core Features

### Universal Search
- Instantly search across entries, categories, assets, users, globals, sections, entry types, and more
- Lightning-fast keyboard navigation with `Cmd+K` / `Ctrl+K`
- Smart search results with relevant metadata and contextual information

### Plugin Integration Framework
- **Extensible Integration System**: Third-party plugins can add contextual information and actions to search results
- **Built-in Blitz Integration**: Shows cache status and provides clear cache actions for [Blitz](https://plugins.craftcms.com/blitz?craft5) cached pages
- **Built-in View Count Integration**: Displays view statistics for entries and other elements when using [View Count](https://plugins.craftcms.com/view-count?craft5)
- **Developer API**: Complete integration system for plugin developers to extend Launcher with their own contextual data and actions

### Intelligent Usage Tracking
- **Launch History System**: Tracks which items you actually navigate to (not just search for)
- **Popular Items Display**: Shows your most-used items when you first open the launcher
- **Launch Count Tracking**: See how many times you've accessed each item
- **Individual Item Removal**: Remove specific items from your history with a subtle X button

### Browse Mode
- Type `*` to explore all content types systematically
- Drill down into specific areas (entries, users, settings, plugins, etc.)
- Perfect for content discovery and comprehensive site exploration

### Front-end Launcher
- **Context-Aware Search**: Use the launcher on your live site to quickly edit the page you're viewing
- **Full Admin Access**: Search all content types and settings while browsing your front-end
- **User Preferences**: Enable/disable front-end access per user with granular controls
- **Security Built-in**: Rate limiting, bot detection, and permission validation protect against abuse

### Commerce Integration
- **Order Search**: Find orders by reference number or customer details
- **Customer Search**: Search Commerce customers by name or email
- **Product Search**: Search products and variants with multiple strategies
- **Graceful Fallbacks**: Handles missing customer names and complex Commerce data

### Advanced Content Types
- **Entry Types Search**: Navigate directly to entry type configurations
- **Section Management**: Quick access to section settings and configurations
- **Field Management**: Jump to field definitions instantly
- **Plugin Settings**: Access any plugin's configuration panel
- **User Management**: Find users and access their profiles

## Lightning Fast Navigation

- Jump to any result using Return key or configurable modifier keys (Cmd+1-9)
- Smart keyboard shortcuts that don't interfere with search typing
- Popular items based on actual usage appear when you open the launcher
- Privacy controls - disable tracking or clear history anytime
- Seamless integration with Craft's existing permissions system

## Native Craft Integration

- Automatically matches your Craft admin panel styling
- Clean, accessible interface that feels like part of Craft
- Responsive design that works on all screen sizes
- Respects all user permissions - only shows accessible content

## Plugin Integrations

Launcher includes a powerful integration system that allows plugins to display contextual information and actions directly in search results. This creates a unified workflow where you can see cache status, view counts, or other plugin-specific data without leaving the launcher.

### Built-in Integrations

**Blitz Cache** ([Install Blitz](https://plugins.craftcms.com/blitz?craft5))
- View cache status (Cached, Uncached, or Not Cacheable) for entries, categories, and globals
- Clear cache for specific pages directly from search results
- Automatic detection of cacheable pages

**View Count** ([Install View Count](https://plugins.craftcms.com/view-count?craft5))
- See view statistics for entries, categories, assets, and other elements
- Formatted view counts with K/M suffixes for large numbers
- Real-time data displayed alongside search results

### For Plugin Developers

Launcher provides a complete API for third-party plugins to integrate with search results. Register your own integrations to display custom status information, metrics, or actions for your plugin's features. Full documentation and examples included.

## Quick Start

1. Install from the Plugin Store or via Composer: `composer require brilliance/craft-launcher`
2. Enable the plugin: `php craft plugin/install launcher`
3. Press `Cmd+K` or `Ctrl+K` anywhere in the admin panel
4. Start typing to search, or type `*` to browse content types

## Highly Configurable

- Customize keyboard shortcuts to fit your workflow
- Configure result navigation shortcuts (Cmd, Ctrl, Alt, or Shift + numbers)
- Control which content types appear in search results
- Configure launch history settings and privacy controls
- Fine-tune search behavior, result limits, and debounce timing
- Enable/disable Commerce integration based on your needs
- Enable/disable plugin integrations individually
- Configure front-end launcher access per user

## Built for the Community

This plugin was developed to solve our own workflow challenges and is now shared freely with the Craft community. We believe great tools should be accessible to everyone, regardless of budget.

**Requirements**: Craft CMS 5.0+ and PHP 8.2+

---

**Developed by Brilliance** - Made with care for the Craft CMS community
