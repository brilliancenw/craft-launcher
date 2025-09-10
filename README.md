# Craft Launcher Plugin

A universal search launcher for the Craft CMS admin panel that provides quick access to content, settings, and navigation throughout your Craft installation. Think of it as Spotlight for macOS or Command Palette for VS Code, but specifically designed for Craft CMS with intelligent usage tracking.

## Features

### Core Search & Navigation
- **Universal Search**: Quickly search across entries, users, categories, assets, globals, sections, entry types, and more
- **Browse Mode**: Type `*` to explore all content types and drill down into specific areas
- **Smart Navigation**: Jump to any section of the Craft control panel instantly  
- **Keyboard Shortcuts**: Navigate entirely with your keyboard for maximum efficiency
- **Theme Integration**: Seamlessly matches your Craft admin panel theme and styling
- **Permission-Aware**: Only shows content you have permission to access

### Intelligent Usage Tracking
- **Launch History**: Tracks which items you actually navigate to (not just search for)
- **Popular Items**: Shows your most-used items when you first open the launcher
- **Launch Count Display**: See how many times you've accessed each item
- **Individual Item Removal**: Remove specific items from your history with a subtle X button
- **Privacy Controls**: Enable/disable history tracking or clear all data

### Commerce Integration
- **Customer Search**: Find Commerce customers by name or email
- **Product Search**: Search products and variants
- **Order Search**: Find orders by reference number or customer details
- **Customer Name Fallbacks**: Handles missing customer names gracefully

### Advanced Content Types
- **Entry Types**: Search and navigate to entry type configurations
- **Section Management**: Quick access to section settings
- **Field Management**: Navigate directly to field definitions
- **Plugin Settings**: Access plugin configuration panels
- **User Management**: Find users and access their profiles

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## Installation

### Method 1: Via Composer (Recommended)

1. **Navigate to your Craft project:**
   ```bash
   cd /path/to/your/craft/project
   ```

2. **Install the plugin:**
   ```bash
   composer require brilliance/craft-launcher
   ```

3. **Enable the plugin:**
   ```bash
   php craft plugin/install launcher
   ```

### Method 2: Via Control Panel

1. Navigate to **Settings → Plugins** in your Craft admin panel
2. Click the **"Plugin Store"** button  
3. Search for **"Launcher"**
4. Click **"Install"** next to the Craft Launcher plugin
5. Follow the on-screen installation instructions

### Post-Installation

1. **Set Permissions**: Navigate to **Settings → Users → User Groups** and ensure your user groups have the "Access Launcher" permission
2. **Configure Settings**: Visit **Settings → Launcher** to customize keyboard shortcuts and search behavior
3. **Test It Out**: Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux) anywhere in the admin panel

## Usage Guide

### Opening the Launcher

- **Keyboard Shortcut**: Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux) from anywhere in the Craft admin panel
- **Same Shortcut to Close**: Press your keyboard shortcut again or `Esc` to close
- **Smart Initial Display**: Shows your most popular items when first opened (based on actual usage)

### Search Modes

#### **Universal Search Mode** (Default)
Just start typing to search across all enabled content types:
- `homepage` - Finds entries, categories, assets with "homepage" in the title
- `john` - Finds users, entries, or content authored by or mentioning "john"
- `products` - Finds entries, categories, sections, Commerce products related to products

#### **Browse Mode**
Type `*` to enter browse mode and explore your content systematically:

1. **Type `*`** - Shows all available content types
2. **Select a content type** - Use arrow keys or number keys (1-9) to select
3. **Browse all items** - See all entries, users, categories, etc. of that type
4. **Navigate normally** - Use Enter or click to open items

**Available browse categories:**
- **Entries** - All entry content
- **Categories** - Category items  
- **Assets** - Media and files
- **Users** - User accounts
- **Global Sets** - Global content
- **Sections** - Entry section settings
- **Entry Types** - Entry type definitions
- **Category Groups** - Category group settings
- **Asset Volumes** - Asset volume settings
- **Fields** - Field definitions
- **Plugins** - Plugin settings

### Launch History System

#### **How It Works**
The launcher tracks which items you actually navigate to (not just search for) and builds a personalized usage profile:

- **Recording**: Every time you click or press Enter on a search result, it's recorded
- **Frequency Tracking**: Items you use more often appear higher in the list
- **Intelligent Ranking**: Combines frequency with recency for optimal relevance
- **Per-User**: Each user has their own separate launch history

#### **Popular Items Display**
When you first open the launcher (without typing), you'll see:
- **Section Title**: "Popular Items (hover over items to remove)"
- **Launch Counts**: Each item shows how many times you've accessed it
- **Smart Ordering**: Most-used items appear first
- **Remove Options**: Hover over any item to see a subtle X button

#### **Managing Your History**
- **Remove Individual Items**: Hover over any popular item and click the X button
- **Clear All History**: Use the "Clear History" utility in plugin settings
- **Disable Tracking**: Turn off launch history in Settings → Launcher
- **Privacy**: All data is stored locally in your Craft database

### Commerce Integration

If Craft Commerce is installed, the launcher provides enhanced e-commerce search:

#### **Customer Search**
- Search by customer name or email address
- Graceful handling of customers without names
- Direct links to customer management pages

#### **Product Search**
- Find products and product variants
- Search by product name or SKU
- Quick access to product edit pages

#### **Order Search**
- Search orders by reference number
- Find orders by customer name or email
- Multiple search strategies for comprehensive results

### Keyboard Navigation

| Key | Action |
|-----|--------|
| **Type anything** | Start searching |
| **`*`** | Enter browse mode |
| **`↑` `↓`** | Navigate through results |
| **`Enter`** | Open first result (selected result) |
| **`Cmd+1` to `Cmd+9`** | Jump to numbered results (configurable modifier) |
| **`Esc`** | Close launcher |
| **`Cmd+K` / `Ctrl+K`** | Close launcher (same as open) |
| **`Hover + Click X`** | Remove item from history |

**Note**: Result navigation uses modifier keys (default: `Cmd+1` through `Cmd+9`) to avoid conflicts when typing numbers in search queries. The modifier key can be customized in Settings → Launcher.

### Pro Tips

- **Popular vs Recent**: The launcher shows popular items (based on usage) rather than just recent items for better productivity
- **Usage Tracking**: The more you use an item, the higher it appears in your popular items list
- **Privacy Control**: You can disable history tracking or clear all data anytime in settings
- **Commerce Orders**: Search for orders using partial reference numbers for quick access
- **Entry Types**: Quickly navigate to entry type configurations for content modeling work

## Configuration

Navigate to **Settings → Launcher** to customize your experience:

### General Settings

| Setting | Description | Default |
|---------|-------------|---------|
| **Keyboard Shortcut** | Hotkey to open/close launcher | `Cmd+K` / `Ctrl+K` |
| **Search Debounce Delay** | Delay before search executes (milliseconds) | `300` |
| **Maximum Results** | Number of results to show per content type | `10` |

### Result Navigation Shortcuts

| Setting | Description | Default |
|---------|-------------|---------|
| **Modifier Key** | Key used with numbers for result selection | `Command (⌘)` |

Configure how to activate search results:
- **First Result**: Always uses Return/Enter key
- **Numbered Results**: Use modifier key + number (1-9)
- **Available Modifiers**: Command (⌘), Control (Ctrl), Alt/Option (⌥), or Shift (⇧)
- **Example**: With "Command" selected, press Cmd+1 for first numbered result, Cmd+2 for second, etc.

### Launch History Settings

| Setting | Description | Default |
|---------|-------------|---------|
| **Track Launch History** | Enable intelligent usage tracking | `Enabled` |
| **Max Popular Items** | Maximum number of popular items to show | `10` |

Control your privacy and usage tracking:
- **Enable/Disable**: Turn launch history tracking on or off
- **Clear History**: Remove all stored usage data
- **Per-User Storage**: Each user maintains separate history data

### Content Types

Control which types of content appear in search results:

- **Entries** - Blog posts, pages, and other entry content
- **Categories** - Category taxonomies  
- **Assets** - Images, documents, and media files
- **Users** - User accounts and profiles
- **Global Sets** - Site-wide content and settings
- **Sections** - Entry section configurations
- **Entry Types** - Entry type definitions
- **Category Groups** - Category group settings  
- **Asset Volumes** - Asset storage configurations
- **Fields** - Custom field definitions
- **Plugins** - Installed plugin settings

### Commerce Settings

If Craft Commerce is installed:

- **Search Commerce Customers** - Include customer accounts in search
- **Search Commerce Products** - Include products and variants
- **Search Commerce Orders** - Include order search functionality

### Content Filtering

Fine-tune which content appears in results:

- **Search Drafts**: Include draft entries in search results
- **Search Revisions**: Include entry revisions in search results  
- **Search Disabled Items**: Include disabled entries, users, etc.
- **Search Entries by Author**: Find entries by author name
- **Searchable Sections**: Limit entry search to specific sections
- **Searchable Entry Types**: Limit entry search to specific types
- **Searchable Category Groups**: Limit category search to specific groups
- **Searchable Asset Volumes**: Limit asset search to specific volumes

### Permissions

The launcher respects all existing Craft permissions:
- Users only see content they can access
- The "Access Launcher" permission controls who can use the plugin
- All element-level permissions are automatically enforced
- Launch history is stored per-user and private

## Advanced Features

### Custom Search Providers

Developers can extend the launcher with custom search providers:

```php
use brilliance\launcher\events\RegisterSearchProvidersEvent;
use brilliance\launcher\services\SearchService;
use yii\base\Event;

Event::on(
    SearchService::class,
    SearchService::EVENT_REGISTER_SEARCH_PROVIDERS,
    function(RegisterSearchProvidersEvent $event) {
        $event->providers[] = new MyCustomSearchProvider();
    }
);
```

### Custom Actions

Register custom actions for search results:

```php
use brilliance\launcher\events\RegisterActionsEvent;
use brilliance\launcher\services\ActionService;
use yii\base\Event;

Event::on(
    ActionService::class,
    ActionService::EVENT_REGISTER_ACTIONS,
    function(RegisterActionsEvent $event) {
        $event->actions[] = new MyCustomAction();
    }
);
```

### Launch History API

Programmatically interact with launch history:

```php
// Get popular items for current user
$popularItems = Launcher::$plugin->history->getPopularItems(10);

// Clear user's history
$success = Launcher::$plugin->history->clearUserHistory();

// Get usage statistics
$stats = Launcher::$plugin->history->getUserStats();
```

## Database Schema

The plugin creates one additional table for launch history tracking:

### `launcher_user_history`
Stores per-user launch history data:
- **userId**: References the user who performed the launch
- **itemType**: Type of item (Entry, Section, User, etc.)
- **itemTitle**: Display name of the item
- **itemUrl**: Admin URL that was accessed
- **itemHash**: Unique identifier for deduplication
- **launchCount**: Number of times the user has launched this item
- **lastLaunchedAt**: Timestamp of most recent launch
- **firstLaunchedAt**: Timestamp when first launched

## Troubleshooting

### Common Issues

#### Launcher Won't Open
- **Check plugin status**: Ensure the plugin is installed and enabled in Settings → Plugins
- **Verify permissions**: Make sure your user has the "Access Launcher" permission
- **Keyboard conflicts**: Try changing the shortcut if it conflicts with browser/OS shortcuts
- **Clear caches**: Run `php craft clear-caches/all`

#### No Search Results  
- **Permission check**: Verify you can access the content you're searching for
- **Content type settings**: Check that the content types are enabled in Settings → Launcher
- **Rebuild indexes**: Run `php craft resave/entries` to rebuild search indexes
- **Check filters**: Review content filtering settings (drafts, disabled items, etc.)

#### Popular Items Not Showing
- **Launch history disabled**: Check that launch history is enabled in settings
- **No usage data**: Use the launcher a few times to build up history data
- **Clear caches**: Run `php craft clear-caches/all` and test again
- **Database issues**: Verify the launcher_user_history table exists

#### Commerce Search Not Working
- **Commerce not installed**: Verify Craft Commerce is properly installed and enabled
- **Class loading issues**: Clear all caches and restart your web server
- **Permission issues**: Ensure you have permission to access Commerce data

#### Browse Mode Not Working
- **Clear storage**: Delete the `storage/` folder contents and reload
- **JavaScript errors**: Check browser console for any error messages
- **Browser cache**: Clear your browser cache completely

#### Styling Problems
- **Clear CP resources**: Run `php craft clear-caches/cp-resources`
- **Browser cache**: Perform a hard refresh (Cmd+Shift+R / Ctrl+Shift+F5)
- **Theme conflicts**: Check if other plugins are overriding CSS

### Debug Mode

To enable detailed logging, add this to your `config/general.php`:

```php
'devMode' => true,
'enableTemplateCaching' => false,
```

Check `storage/logs/web-[date].log` for launcher-specific log entries.

## Contributing

We love contributions! Here's how you can help make Craft Launcher even better:

### **Bug Reports**
Found a bug? Please [create an issue](https://github.com/brilliance/craft-launcher/issues/new?template=bug_report.md) with:
- Clear description of the problem
- Steps to reproduce the issue  
- Your Craft CMS version and PHP version
- Screenshots or videos if helpful

### **Feature Requests** 
Have an idea for a new feature? [Open a feature request](https://github.com/brilliance/craft-launcher/issues/new?template=feature_request.md) and tell us:
- What problem it would solve
- How you envision it working
- Any examples from other tools

### **Pull Requests**
Ready to contribute code? We'd love your help! 

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)  
3. **Make** your changes with clear, descriptive commits
4. **Test** thoroughly (include new tests if applicable)
5. **Submit** a pull request with a clear description

### **Development Guidelines**
- Follow [Craft CMS coding standards](https://docs.craftcms.com/4.x/contribute/coding-guidelines.html)
- Write clear commit messages  
- Include tests for new functionality
- Update documentation for any user-facing changes

### **Discussion**
- Join the conversation in [GitHub Discussions](https://github.com/brilliance/craft-launcher/discussions)
- Share your use cases, tips, and workflows
- Help other users with questions

## Support & Community

- **Bug Reports**: [GitHub Issues](https://github.com/brilliance/craft-launcher/issues)
- **General Discussion**: [GitHub Discussions](https://github.com/brilliance/craft-launcher/discussions)  
- **Documentation**: This README and inline code comments
- **Help**: Tag us in the [Craft CMS Discord](https://craftcms.com/discord) #help channel

## License

This plugin is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## Credits

**Developed by [Brilliance](https://www.brilliancenw.com/)**

Special thanks to:
- The [Craft CMS team](https://craftcms.com/) for creating an incredible platform
- The Craft community for inspiration and feedback  
- All contributors who help make this plugin better

---

**Made with care for the Craft CMS community**