# Changelog

## [v1.1.1] - 2025-10-12

### Documentation Updates

**Plugin Store Description**
- **UPDATED**: Featured new plugin integration framework in store listing
- **ADDED**: Direct links to Blitz and View Count plugins
- **ENHANCED**: Highlighted front-end launcher capabilities
- **IMPROVED**: Better description of extensibility features for plugin developers

**Changelog**
- **CLEANED**: Removed references to deleted releases for accuracy
- **CONSOLIDATED**: All fixes from removed versions now properly documented in v1.1.0
- **IMPROVED**: Clearer release history showing only published versions

> **Documentation Release**: This release updates the plugin store description and changelog to accurately reflect the current state of the plugin and highlight the new integration framework features.

## [v1.1.0] - 2025-10-12

### New Features

**Plugin Integration Framework**
- **NEW**: Extensible integration system allowing third-party plugins to add contextual information to search results
- **NEW**: Event-based registration system (EVENT_REGISTER_INTEGRATIONS) for plugin integrations
- **NEW**: Complete developer API with LauncherIntegrationInterface and BaseIntegration helper class
- **NEW**: Comprehensive documentation for creating custom integrations

**Built-in Integrations**
- **NEW**: Blitz Cache integration - shows cache status and provides clear cache actions
- **NEW**: View Count integration - displays view statistics for elements
- **NEW**: Dynamic element ID resolution from CP URLs for frontend-tracked items
- **NEW**: Support for custom admin panel paths (respects cpTrigger configuration)

### Critical Fixes

**Settings Management**
- **FIXED**: Settings page crash on fresh installations caused by unregistered Twig variable
- **IMPROVED**: Added LauncherVariable class to properly expose plugin services to templates
- **ENHANCED**: Settings page now properly displays database table status diagnostics

**Frontend Integration**
- **FIXED**: Launcher keyboard shortcut not working on website frontend
- **FIXED**: "Open Front-end Links in New Tab" preference not being respected
- **FIXED**: Integration display on frontend results
- **IMPROVED**: Corrected PHP heredoc syntax for boolean preferences

**Production Environment Support**
- **FIXED**: Welcome screen persistence in production environments with read-only settings
- **IMPROVED**: Created InterfaceService for UI state management separate from plugin configuration
- **ADDED**: Database table for UI state management (launcher_interface_settings)

**Database & Migrations**
- **FIXED**: Migration class name conflicts during plugin updates
- **FIXED**: Migration failures when launcher_user_history table doesn't exist
- **IMPROVED**: Idempotent migrations that can be run multiple times safely
- **ENHANCED**: Better error handling for partial plugin installations

**User Experience Improvements**
- **FIXED**: Welcome screen showing repeatedly after dismissal
- **IMPROVED**: Integration display across all contexts (popular, recent, search, frontend)
- **ENHANCED**: Element ID extraction from CP URLs
- **IMPROVED**: Better handling of items without direct element IDs

### Technical Improvements

- **ENHANCED**: Logging throughout integration system for better debugging
- **IMPROVED**: Error handling and debugging capabilities
- **ADDED**: Multi-step element lookup chain for robust ID resolution
- **IMPROVED**: Support for dynamic CP trigger configuration (security improvement)
- **ENHANCED**: Better separation of configuration vs. state data
- **IMPROVED**: More robust welcome screen experience with proper state management

> **Major Update**: This release introduces a powerful plugin integration framework that allows Blitz, View Count, and other plugins to display contextual information directly in Launcher search results. It also consolidates several critical fixes for settings management, frontend functionality, and production deployments.

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
- **NEW**: Front-end launcher functionality - use the launcher on your live website!
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

> **What's New**: The launcher now works on your front-end! Enable it in your account preferences to access admin functions while browsing your live site. Thanks to @wmdhosting for the excellent feature suggestion!

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

> **Upgrade Note**: Existing keyboard shortcuts will continue working. The new modifier key settings default to Command to maintain current behavior.

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
