# Changelog

## [v1.1.0] - 2024-09-14

### Added
- **NEW**: Static settings destinations search - quickly find admin pages like Email Settings, Routes, Users, Plugins, etc.
- **NEW**: Loading indicator with animated dots provides visual feedback during search operations
- **NEW**: Professional gear icon for settings search results that matches Craft's admin panel design

### Enhanced
- **IMPROVED**: Popular Items UI with smoother user experience
- **IMPROVED**: Launch counter now fades in/out smoothly (0.5s transition) on hover and keyboard focus
- **IMPROVED**: All keyboard shortcuts now work correctly - cmd-1 through cmd-9 launch the correct items
- **IMPROVED**: Missing cmd-9 shortcut now displays and functions properly

### Fixed
- **FIXED**: Keyboard shortcut alignment - cmd-2 now correctly launches the second item (was off by one)
- **FIXED**: Popular Items fencepost error that prevented cmd-9 from showing
- **FIXED**: Settings icon mapping so static settings show gear icon instead of default circle

### Removed
- **REMOVED**: Misleading "(Hover over items to remove)" text from Popular Items section
- **REMOVED**: Confusing "Content" and "Media" entries from settings search results

### Technical Improvements
- **ENHANCED**: Better search performance with loading states for larger sites
- **ENHANCED**: Icon consistency across all launcher result types
- **ENHANCED**: Cleaner codebase with improved keyboard navigation logic

> **What's New**: You can now search for administrative settings like "email", "users", "routes", or "plugins" to quickly navigate to control panel pages. The launcher also provides better visual feedback during searches and more intuitive keyboard navigation.

## [v1.0.2] - 2024-09-09

### Enhanced Keyboard Navigation
- **NEW**: Configurable modifier keys for result navigation (Command, Control, Alt, Shift)
- **IMPROVED**: Modifier + number shortcuts (Cmd+1-9) prevent conflicts with search typing
- **FIXED**: Browse mode keyboard shortcuts now work correctly (no more offset issues)
- **ADDED**: Visual keyboard shortcut indicators on search results (Return, Cmd+1, Cmd+2, etc.)

### UI/UX Improvements
- **IMPROVED**: Better spacing between keyboard shortcut icons and remove buttons
- **ADDED**: Smart keyboard shortcut display that adapts to selected modifier key
- **ENHANCED**: Result navigation shortcuts are now clearly labeled in settings

### Developer Experience
- **ADDED**: New settings in plugin configuration for result navigation shortcuts
- **IMPROVED**: Settings UI shows preview of keyboard shortcuts based on selected modifier
- **ENHANCED**: Better separation of concerns in JavaScript keyboard handling

### Bug Fixes
- **FIXED**: Commerce Product URLs now use proper cpEditUrl() method for correct product type/slug URLs
- **FIXED**: UI collision between keyboard shortcuts and history remove buttons
- **FIXED**: Browse mode shortcut numbering alignment with keyboard handlers

### Documentation Updates
- **UPDATED**: README.md with new keyboard navigation details
- **ENHANCED**: Plugin store description with improved shortcut information
- **ADDED**: Configuration documentation for new result navigation settings

> **Upgrade Note**: Existing keyboard shortcuts will continue working. The new modifier key settings default to Command (⌘) to maintain current behavior.

## [v1.0.1] - 2024-09-06

### Features Added
- Launch History System with intelligent usage tracking
- Individual history item removal with X button
- Entry Types search functionality
- Enhanced Commerce order search
- Author search capability

### Fixes
- Commerce order search functionality
- Customer name null display issues
- Entry Types URL format
- Launch history race condition
- CSRF token and JSON response handling

### Documentation
- Complete README rewrite
- Comprehensive feature documentation
- Database schema and API documentation

## [v1.0.0] - 2024-09-04

### Added
- Initial release of Craft Launcher Plugin
- Universal search across entries, users, categories, assets, globals
- Browse mode with `*` trigger
- Keyboard navigation and shortcuts
- Theme integration with Craft CMS admin panel
- Permission-aware content filtering
- Basic Commerce integration
- Customizable search behavior and content types

### Features
- Search across all major Craft CMS content types
- Smart keyboard navigation (Cmd+K/Ctrl+K)
- Browse mode for systematic content exploration
- Craft admin panel theme integration
- Permission-based content visibility
- Configurable search settings
- Basic plugin architecture with extensible search providers
