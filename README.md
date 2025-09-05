# Craft Launcher Plugin

A universal search launcher for the Craft CMS admin panel that provides quick access to content, settings, and navigation throughout your Craft installation.

## Features

- **Universal Search**: Quickly search across entries, users, categories, tags, assets, and globals
- **Smart Navigation**: Jump to any section of the Craft control panel instantly
- **Keyboard Shortcuts**: Navigate entirely with your keyboard for maximum efficiency
- **Recent Items**: Access your recently viewed items with ease
- **Theme Integration**: Automatically matches your Craft admin panel theme (light/dark mode)
- **Fuzzy Search**: Find what you're looking for even with partial matches
- **Customizable**: Configure search behavior and shortcuts to match your workflow

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## Installation

### Via Composer

1. Open your terminal and navigate to your Craft project:
   ```bash
   cd /path/to/project
   ```

2. Add the plugin with Composer:
   ```bash
   composer require brilliance/craft-launcher
   ```

3. Install the plugin via the CLI:
   ```bash
   php craft plugin/install launcher
   ```

Or install via the Craft Control Panel by navigating to Settings → Plugins and clicking "Install" next to Launcher.

## Usage

### Opening the Launcher

- **Keyboard Shortcut**: Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux) from anywhere in the Craft admin panel
- **Click Method**: Click the search icon in the admin panel header (if enabled in settings)

### Search Commands

The launcher supports several search commands and prefixes:

| Prefix | Description | Example |
|--------|-------------|---------|
| None | Search all content types | `homepage` |
| `>` | Navigate to control panel sections | `>settings` |
| `@` | Search users | `@admin` |
| `#` | Search categories | `#products` |
| `:` | Search tags | `:featured` |
| `!` | Search assets | `!logo` |
| `*` | Search globals | `*sitename` |

### Keyboard Navigation

| Key | Action |
|-----|--------|
| `↑` `↓` | Navigate through results |
| `Enter` | Open selected result |
| `Esc` | Close launcher |
| `Tab` | Switch between result sections |
| `Cmd+Enter` | Open in new tab (when applicable) |

### Available Actions

When an item is selected, you can:
- **View**: Navigate to the entry/element view page
- **Edit**: Open the entry/element editor
- **Preview**: Open the front-end preview (if available)
- **Delete**: Remove the item (with confirmation)

## Configuration

### Settings Page

Navigate to Settings → Launcher to configure:

- **Enable Header Icon**: Show/hide the search icon in the admin header
- **Keyboard Shortcut**: Customize the keyboard shortcut to open the launcher
- **Search Limit**: Number of results to show per category
- **Search Delay**: Debounce delay for search queries (in milliseconds)
- **Enable Fuzzy Search**: Toggle fuzzy matching for search results
- **Searchable Element Types**: Choose which element types to include in search
- **Default Action**: Set the default action when selecting an item (view/edit)

### Permissions

The launcher respects all Craft permissions. Users will only see search results for content they have permission to access.

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

## Troubleshooting

### Launcher not opening

1. Check that the plugin is installed and enabled
2. Verify keyboard shortcuts aren't conflicting with browser or OS shortcuts
3. Clear Craft's cache: `php craft clear-caches/all`

### Search not returning results

1. Verify you have permission to view the content you're searching for
2. Check that the element types are enabled in settings
3. Rebuild search indexes: `php craft resave/entries`

### Styling issues

1. Clear browser cache
2. Re-publish CP resources: `php craft clear-caches/cp-resources`

## Support

For bug reports and feature requests, please use the [GitHub issue tracker](https://github.com/brilliance/craft-launcher/issues).

## License

This plugin is licensed under the MIT License. See [LICENSE.md](LICENSE.md) for details.

## Credits

Developed by [Brilliance](https://www.brilliancenw.com/)

Special thanks to the Craft CMS team for creating an amazing platform.