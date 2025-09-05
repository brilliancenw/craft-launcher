# Craft Launcher Plugin

A universal search launcher for the Craft CMS admin panel that provides quick access to content, settings, and navigation throughout your Craft installation. Think of it as Spotlight for macOS or Command Palette for VS Code, but specifically designed for Craft CMS.

## ✨ Features

- **Universal Search**: Quickly search across entries, users, categories, assets, globals, and more
- **Browse Mode**: Type `*` to explore all content types and drill down into specific areas
- **Smart Navigation**: Jump to any section of the Craft control panel instantly  
- **Keyboard Shortcuts**: Navigate entirely with your keyboard for maximum efficiency
- **Recent Items**: Access your recently viewed items when you open the launcher
- **Theme Integration**: Seamlessly matches your Craft admin panel theme and styling
- **Fuzzy Search**: Find what you're looking for even with partial or approximate matches
- **Customizable**: Configure search behavior, shortcuts, and content types to match your workflow
- **Permission-Aware**: Only shows content you have permission to access

## Requirements

- Craft CMS 5.0.0 or later
- PHP 8.2 or later

## 🚀 Installation

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

### Method 3: Manual Installation

1. Download the plugin files from the [releases page](https://github.com/brilliance/craft-launcher/releases)
2. Extract to `vendor/brilliance/craft-launcher/`  
3. Run `php craft plugin/install launcher`

### Post-Installation

1. **Set Permissions**: Navigate to **Settings → Users → User Groups** and ensure your user groups have the "Access Launcher" permission
2. **Configure Settings**: Visit **Settings → Launcher** to customize keyboard shortcuts and search behavior
3. **Test It Out**: Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux) anywhere in the admin panel!

## 📖 Usage Guide

### Opening the Launcher

- **Keyboard Shortcut**: Press `Cmd+K` (Mac) or `Ctrl+K` (Windows/Linux) from anywhere in the Craft admin panel
- **Same Shortcut to Close**: Press your keyboard shortcut again or `Esc` to close
- When you first open the launcher, you'll see your recently accessed items

### Search Modes

#### 🔍 **Universal Search Mode** (Default)
Just start typing to search across all enabled content types:
- `homepage` - Finds entries, categories, assets with "homepage" in the title
- `john` - Finds users, entries, or content authored by or mentioning "john"
- `products` - Finds entries, categories, sections related to products

#### 🗂️ **Browse Mode** (New!)
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
- **Category Groups** - Category group settings
- **Asset Volumes** - Asset volume settings
- **Fields** - Field definitions
- **Plugins** - Plugin settings

#### 🎯 **Quick Actions**
The launcher intelligently surfaces the most relevant actions for each item:
- **Entries**: Edit, view live site, duplicate, delete
- **Users**: Edit profile, view permissions
- **Settings**: Direct navigation to configuration panels

### Keyboard Navigation

| Key | Action |
|-----|--------|
| **Type anything** | Start searching |
| **`*`** | Enter browse mode |
| **`↑` `↓`** | Navigate through results |
| **`Enter`** | Open selected result |
| **`1-9`** | Jump to result by number |
| **`Esc`** | Close launcher |
| **`Cmd+K` / `Ctrl+K`** | Close launcher (same as open) |

### Pro Tips

- **Recent Items**: When you first open the launcher, your most recently accessed items appear automatically
- **Fuzzy Matching**: You don't need exact spelling - "homepg" will find "Homepage"  
- **Permission Aware**: You'll only see content you have permission to access
- **Fast Navigation**: Use number keys (1-9) to instantly jump to any visible result

## ⚙️ Configuration

Navigate to **Settings → Launcher** to customize your experience:

### General Settings

| Setting | Description | Default |
|---------|-------------|---------|
| **Keyboard Shortcut** | Hotkey to open/close launcher | `Cmd+K` / `Ctrl+K` |
| **Search Debounce Delay** | Delay before search executes (milliseconds) | `300` |
| **Maximum Results** | Number of results to show per content type | `10` |

### Content Types

Control which types of content appear in search results:

- ✅ **Entries** - Blog posts, pages, and other entry content
- ✅ **Categories** - Category taxonomies  
- ✅ **Assets** - Images, documents, and media files
- ✅ **Users** - User accounts and profiles
- ✅ **Global Sets** - Site-wide content and settings
- ✅ **Sections** - Entry section configurations
- ✅ **Category Groups** - Category group settings  
- ✅ **Asset Volumes** - Asset storage configurations
- ✅ **Fields** - Custom field definitions
- ✅ **Plugins** - Installed plugin settings

### Content Filtering

Fine-tune which content appears in results:

- **Search Drafts**: Include draft entries in search results
- **Search Revisions**: Include entry revisions in search results  
- **Search Disabled Items**: Include disabled entries, users, etc.
- **Searchable Sections**: Limit entry search to specific sections
- **Searchable Entry Types**: Limit entry search to specific types
- **Searchable Category Groups**: Limit category search to specific groups
- **Searchable Asset Volumes**: Limit asset search to specific volumes

### Permissions

The launcher respects all existing Craft permissions:
- Users only see content they can access
- The "Access Launcher" permission controls who can use the plugin
- All element-level permissions are automatically enforced

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

## 🛠️ Troubleshooting

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

## 🤝 Contributing

We love contributions! Here's how you can help make Craft Launcher even better:

### 🐛 **Bug Reports**
Found a bug? Please [create an issue](https://github.com/brilliance/craft-launcher/issues/new?template=bug_report.md) with:
- Clear description of the problem
- Steps to reproduce the issue  
- Your Craft CMS version and PHP version
- Screenshots or videos if helpful

### 💡 **Feature Requests** 
Have an idea for a new feature? [Open a feature request](https://github.com/brilliance/craft-launcher/issues/new?template=feature_request.md) and tell us:
- What problem it would solve
- How you envision it working
- Any examples from other tools

### 🔧 **Pull Requests**
Ready to contribute code? We'd love your help! 

1. **Fork** the repository
2. **Create** a feature branch (`git checkout -b feature/amazing-feature`)  
3. **Make** your changes with clear, descriptive commits
4. **Test** thoroughly (include new tests if applicable)
5. **Submit** a pull request with a clear description

### 📋 **Development Guidelines**
- Follow [Craft CMS coding standards](https://docs.craftcms.com/4.x/contribute/coding-guidelines.html)
- Write clear commit messages  
- Include tests for new functionality
- Update documentation for any user-facing changes

### 🗨️ **Discussion**
- Join the conversation in [GitHub Discussions](https://github.com/brilliance/craft-launcher/discussions)
- Share your use cases, tips, and workflows
- Help other users with questions

## 📞 **Support & Community**

- 🐛 **Bug Reports**: [GitHub Issues](https://github.com/brilliance/craft-launcher/issues)
- 💬 **General Discussion**: [GitHub Discussions](https://github.com/brilliance/craft-launcher/discussions)  
- 📖 **Documentation**: This README and inline code comments
- 🆘 **Help**: Tag us in the [Craft CMS Discord](https://craftcms.com/discord) #help channel

## 📄 License

This plugin is licensed under the MIT License - see the [LICENSE.md](LICENSE.md) file for details.

## 🙏 Credits

**Developed by [Brilliance](https://www.brilliancenw.com/)**

Special thanks to:
- The [Craft CMS team](https://craftcms.com/) for creating an incredible platform
- The Craft community for inspiration and feedback  
- All contributors who help make this plugin better

---

**Made with ❤️ for the Craft CMS community**