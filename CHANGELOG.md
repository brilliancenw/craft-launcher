# Changelog

## [v1.0.8.2] - 2025-10-04

### Fixed
- **CRITICAL**: Fixed migration class name conflict during plugin updates
- **IMPROVED**: Created new migration with unique timestamp to prevent "class already in use" errors
- **ENHANCED**: Plugin updates now work correctly in all environments

> **Critical Migration Fix**: This patch resolves a PHP fatal error where the migration class name was already in use, preventing plugin updates. The migration has been recreated with a fresh timestamp to eliminate conflicts.

## [v1.0.8.1] - 2025-10-04

### Fixed
- **FIXED**: Welcome screen now works in production environments with read-only settings
- **IMPROVED**: Created new database table for UI state management separate from plugin configuration
- **ENHANCED**: First-run experience now persists correctly in all deployment environments

### Technical Improvements
- **ADDED**: `InterfaceService` for managing UI state data outside of plugin settings
- **ADDED**: New database migration for `launcher_interface_settings` table
- **IMPROVED**: Better separation of configuration vs. state data
- **UPDATED**: Schema version bumped to 1.2.0 for migration trigger

> **Bug Fix**: This patch resolves an issue where the welcome screen would appear every time in production environments that use read-only plugin settings. The welcome screen dismissal is now stored in a dedicated database table instead of plugin settings, ensuring proper functionality in all environments.

## [v1.0.8] - 2025-10-03

### Fixed
- Fixed "Open Front-end Links in New Tab" setting not being respected on front-end
- Corrected PHP heredoc syntax issue that prevented boolean preferences from being passed to JavaScript

### Improved
- Enhanced user interface responsiveness and interaction handling
- Better front-end launcher experience with proper preference management

> **Minor Bug Fix**: This release resolves an issue where the "Open Front-end Links in New Tab" user preference was being ignored on the front-end. Users can now properly control link behavior through their account preferences.

## [v1.0.7] - 2025-10-03

### Fixed
- **CRITICAL**: Fixed settings page crash caused by unregistered Twig variable
- **IMPROVED**: Added LauncherVariable class to properly expose plugin services to Twig templates
- **ENHANCED**: Settings page now properly displays database table status diagnostics

### Added
- **NEW**: Direct link to Launcher utility when history table is missing
- **IMPROVED**: Better user experience with clear call-to-action button for table creation
- **ENHANCED**: More helpful error messaging when database table needs to be created

### Technical Improvements
- **ADDED**: Twig variable registration via `CraftVariable::EVENT_INIT`
- **FIXED**: `craft.launcher.history.getTableStatus()` now accessible in templates
- **IMPROVED**: Settings template with improved UI for table management

> **Settings Page Fix**: This release resolves a critical issue where accessing the plugin settings page resulted in a Twig runtime error. The plugin's services are now properly exposed to the Twig environment, and users get clear guidance when the history table needs to be created.

Thanks to @rauwebieten for reporting this issue!

## [v1.0.6.2] - 2024-09-26

### Fixed
- **CRITICAL**: Fixed null reference error when entry sections are missing or deleted
- **IMPROVED**: Added defensive null checks for `getSection()` calls in SearchService and SearchController
- **ENHANCED**: Entry search now gracefully handles entries with missing section relationships
- **ADDED**: Fallback "Unknown Section" display for entries without valid sections

### Technical Improvements
- **IMPROVED**: Robust error handling prevents search crashes from orphaned entries
- **ENHANCED**: Better data validation in entry context validation
- **ADDED**: Null safety checks for entry type relationships as well

> **Search Fix**: This patch resolves search failures caused by entries that have lost their section relationships (e.g., when sections are deleted but entries remain). Search now works reliably in all scenarios.

## [v1.0.6.1] - 2024-09-26

### Fixed
- **CRITICAL**: Fixed migration failure when launcher_user_history table doesn't exist
- **IMPROVED**: Migration `m250925_181500_add_missing_history_columns` now safely handles all installation scenarios
- **ENHANCED**: Added defensive table and column existence checks to prevent migration errors
- **ADDED**: Automatic table creation if missing during migration process

### Technical Improvements
- **IMPROVED**: Idempotent migrations that can be run multiple times safely
- **ENHANCED**: Better error handling for partial plugin installations
- **ADDED**: Helper methods `addColumnIfNotExists()` and `dropColumnIfExists()` for robust migrations
- **IMPROVED**: Data preservation during migration updates using COALESCE expressions

> **Migration Fix**: This patch resolves installation issues where previous versions failed to create the launcher_user_history table, causing subsequent migrations to fail. The migration system is now fully resilient to various installation failure scenarios.

## [v1.0.6] - 2024-09-26

### Version Fix
- **FIXED**: Corrected version mismatch issue from v1.0.5 where composer.json version didn't match git tag
- **IMPROVED**: Ensures proper Packagist integration and package availability via Composer

### Features from v1.0.5 (now properly released)
- **NEW**: Admin utility for database maintenance - manually add missing history table if needed
- **FIXED**: Missing database columns for personal history tracking
- **IMPROVED**: Settings search reliability and performance improvements
- **ENHANCED**: Database schema validation and consistent gear icon display

## [v1.0.5] - 2024-09-26

### Added
- **NEW**: Admin utility for database maintenance - manually add missing history table if needed

### Fixed
- **FIXED**: Missing database columns for personal history tracking - resolves migration errors on fresh installs
- **FIXED**: Settings search reliability and performance improvements
- **FIXED**: Consistent gear icon display across all admin settings results

### Enhanced
- **IMPROVED**: Database schema validation ensures proper table structure on all environments
- **IMPROVED**: More reliable personal history data persistence

> **Database Update**: This release includes automatic migration for missing database columns. If issues persist, use the new Admin utility to manually rebuild the history table.

## [v1.0.4] - 2024-09-14

### Added
- **NEW**: Front-end launcher functionality - use the launcher on your live website! ([#1](https://github.com/brilliancenw/craft-launcher/issues/1))
- **NEW**: Personal user preferences in My Account → Launcher section
- **NEW**: "Open Front-end Links in New Tab" option for seamless content editing workflow
- **NEW**: Context-aware search - search for "edit" while viewing an entry to quickly edit that specific page
- **NEW**: Dedicated user account preference interface with toggle switches
- **NEW**: LauncherFrontEndAsset for conflict-free front-end styling (no CP CSS interference)

### Enhanced
- **IMPROVED**: Clean separation of admin and front-end asset dependencies
- **IMPROVED**: URL generation for front-end compatibility (no double "actions" prefix)
- **ENHANCED**: Professional user preferences layout matching Craft's design patterns
- **ENHANCED**: Graceful fallback handling for all navigation scenarios
- **ENHANCED**: JavaScript navigation with configurable tab behavior
- **ENHANCED**: Project config synchronization for plugin settings

### Security & Performance
- **ADDED**: Comprehensive security validation for front-end usage
- **ADDED**: Rate limiting (30 searches per minute) to prevent abuse
- **ADDED**: Automatic bot detection and suspicious request filtering
- **ADDED**: CSRF token handling for front-end compatibility
- **ADDED**: Permission validation ensures users only access content they're authorized for

### Technical Improvements
- **ADDED**: UserPreferenceService for individual user setting management
- **ADDED**: UserAccountController with EditUserTrait integration
- **IMPROVED**: Modular architecture with separate front-end and admin concerns
- **ADDED**: New template: `_user-account-content.twig` for user preferences

### Documentation Updates
- **ADDED**: Comprehensive front-end launcher documentation section in README
- **ADDED**: Setup instructions with visual preference interface guide
- **ADDED**: Security and privacy information for administrators
- **ADDED**: Usage examples and pro tips for content editors

### Developer Experience
- **ADDED**: New template: `_user-account-content.twig` for user preferences
- **ENHANCED**: Project config synchronization for plugin settings
- **IMPROVED**: Modular architecture with separate front-end and admin concerns

> **What's New**: The launcher now works on your front-end! Enable it in your account preferences to access admin functions while browsing your live site. Thanks to @wmdhosting for the excellent feature suggestion!

## [v1.0.3] - 2024-09-14

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
