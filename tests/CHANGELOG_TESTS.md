# Test Suite Changelog

## v2.0.0 - 2025-10-03

### Major Update: Auto-Detection & Portability

**Breaking Change:** Test scripts now use automatic path detection instead of hardcoded paths.

### Added
- **Auto-detection system** (`config.sh`)
  - Automatically finds Craft project root
  - Automatically detects PHP binary (MAMP or system)
  - Loads configuration from project's `.env` file
  - No hardcoded paths in repository
- **`.env` integration**
  - Optionally override auto-detected values
  - See `.env.example` for available options
- **Verbose mode**
  - Set `TEST_VERBOSE=1` to see detected configuration
- **PHP version validation**
  - Warns if PHP < 8.2

### Changed
- All test scripts now source `config.sh` for configuration
- Removed hardcoded paths from:
  - `smoke-test.sh`
  - `integration-test.sh`
  - `run-all-tests.sh`
  - All documentation files
- Updated documentation to reflect auto-detection
- PHP heredocs now use variable expansion for paths

### Benefits
- **Portable**: Works on any developer's machine
- **No personal paths in git**: Keeps repository clean
- **Zero configuration**: Works out of the box
- **Flexible**: Can override via .env if needed
- **Team friendly**: Other developers can run tests immediately

### Migration Guide

**For developers:**
No action needed! Tests now auto-detect everything.

**If you had custom paths:**
Move them to your project's `.env` file:
```bash
# Optional overrides in .env
PHP_BIN=/custom/path/to/php
PROJECT_ROOT=/custom/project/path
```

---

## v1.0.0 - 2025-10-03

### Initial Release

- **Smoke test script** - Quick validation in ~15 seconds
- **Integration test script** - Full lifecycle testing
- **Run-all-tests script** - Orchestrates all tests
- **Comprehensive documentation**
  - README.md
  - MANUAL_TESTING.md
  - GETTING_STARTED.md
- **Tests coverage**:
  - PHP syntax validation
  - File structure
  - Database connectivity
  - Plugin lifecycle
  - Settings page
  - Twig variables
  - Service loading
  - Migration system
