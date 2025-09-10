# Craft Launcher

A universal search plugin that brings Spotlight-style navigation to your Craft CMS admin panel. Think Command Palette for VS Code, but designed specifically for Craft CMS with intelligent usage tracking.

Built by developers, for developers - we created this tool to streamline our own Craft workflow and are now sharing it with the community as a free utility.

## Core Features

### Universal Search
- Instantly search across entries, categories, assets, users, globals, sections, entry types, and more
- Lightning-fast keyboard navigation with `Cmd+K` / `Ctrl+K`
- Smart search results with relevant metadata and contextual information

### Intelligent Usage Tracking
- **Launch History System**: Tracks which items you actually navigate to (not just search for)
- **Popular Items Display**: Shows your most-used items when you first open the launcher
- **Launch Count Tracking**: See how many times you've accessed each item
- **Individual Item Removal**: Remove specific items from your history with a subtle X button

### Browse Mode
- Type `*` to explore all content types systematically
- Drill down into specific areas (entries, users, settings, plugins, etc.)
- Perfect for content discovery and comprehensive site exploration

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

## Built for the Community

This plugin was developed to solve our own workflow challenges and is now shared freely with the Craft community. We believe great tools should be accessible to everyone, regardless of budget.

**Requirements**: Craft CMS 5.0+ and PHP 8.2+

---

**Developed by Brilliance** - Made with care for the Craft CMS community