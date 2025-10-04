# Craft Launcher - Testing Guide

This directory contains test suites for the Craft Launcher plugin. These tests help ensure the plugin works correctly before releases and catch regressions.

## Test Suites

### 1. Smoke Tests (Quick Validation)
**File:** `smoke-test.sh`
**Duration:** ~10-15 seconds
**Purpose:** Quick validation of critical functionality

Tests include:
- PHP syntax validation on all source files
- File structure and required files
- Composer configuration validity
- Database connectivity
- Plugin installation status
- Class loading (Launcher, services)
- Settings page rendering
- Twig variable registration (`craft.launcher`)
- Migration status

**Usage:**
```bash
cd tests
./smoke-test.sh
```

**When to run:**
- Before committing code changes
- Before creating releases
- After pulling updates
- Quick sanity check during development

---

### 2. Integration Tests (Full Lifecycle)
**File:** `integration-test.sh`
**Duration:** ~30-60 seconds
**Purpose:** Test complete plugin lifecycle

Tests include:
- Plugin uninstallation
- Plugin installation
- Database table creation/deletion
- Table schema validation
- Post-install functionality
- Reinstallation (edge case)
- Migration system

**⚠️ WARNING:** This test will uninstall and reinstall the plugin!

**Usage:**
```bash
cd tests
./integration-test.sh
```

**When to run:**
- Before major releases
- After database schema changes
- After migration updates
- Testing installation process

---

## Quick Start

### Run All Tests (Recommended before releases)

```bash
# Navigate to tests directory
cd /Users/markmiddleton/Sites/algolia_v5_dev/src/brilliance/craft-launcher/tests

# Run smoke tests first (fast)
./smoke-test.sh

# If smoke tests pass, run integration tests
./integration-test.sh
```

### Run Individual Tests

```bash
# Just smoke tests (fast, non-destructive)
./smoke-test.sh

# Just integration tests (slow, will reinstall plugin)
./integration-test.sh
```

---

## Understanding Test Output

### Success Indicators
- `✓` Green checkmark = Test passed
- `ALL TESTS PASSED` = Suite completed successfully

### Failure Indicators
- `✗` Red X = Test failed
- `⚠` Yellow warning = Non-critical issue
- `TESTS FAILED` = Suite failed

### Example Output
```
🧪 Craft Launcher - Smoke Test Suite
==========================================

━━━ PHP Syntax Validation ━━━
Checking PHP syntax on all source files...
All PHP files have valid syntax

━━━ File Structure Validation ━━━
Required file exists: src/Launcher.php
Required file exists: src/services/HistoryService.php
...

ALL SMOKE TESTS PASSED
```

---

## Pre-Release Checklist

Before creating a new release, run through this checklist:

### 1. Code Quality
```bash
# Check PHP syntax
./smoke-test.sh
```

### 2. Plugin Lifecycle
```bash
# Test install/uninstall
./integration-test.sh
```

### 3. Manual Testing
- [ ] Open Craft admin panel
- [ ] Navigate to plugin settings page
- [ ] Verify no errors displayed
- [ ] Test launcher keyboard shortcut (Cmd+K)
- [ ] Perform a search
- [ ] Check utility page (Utilities → Launcher)
- [ ] Verify table status shows correctly

### 4. Version & Documentation
- [ ] Update version in `composer.json`
- [ ] Update `CHANGELOG.md`
- [ ] Run smoke tests one more time
- [ ] Create git tag
- [ ] Create GitHub release

---

## Troubleshooting

### Tests Won't Run

**Problem:** `Permission denied`
**Solution:**
```bash
chmod +x smoke-test.sh integration-test.sh
```

**Problem:** `PHP command not found`
**Solution:** Update PHP_BIN path in test scripts:
```bash
# Edit the script and change PHP_BIN line
PHP_BIN="/Applications/MAMP/bin/php/php8.2.26/bin/php"
```

### Tests Fail

**Problem:** Smoke tests fail on "Plugin not installed"
**Solution:**
```bash
cd /Users/markmiddleton/Sites/algolia_v5_dev
/Applications/MAMP/bin/php/php8.2.26/bin/php craft plugin/install launcher
```

**Problem:** Integration tests fail on table creation
**Solution:**
1. Check database connection
2. Verify user has CREATE/DROP table permissions
3. Check `storage/logs/web.log` for errors

**Problem:** Settings page test fails
**Solution:**
1. Clear Craft caches: `php craft clear-caches/all`
2. Verify `craft.launcher` Twig variable is registered
3. Check `LauncherVariable.php` exists

---

## Advanced: Adding Your Own Tests

### Adding a Test to Smoke Tests

Edit `smoke-test.sh` and add a new section:

```bash
# Test 10: Your New Test
section "Your Test Name"

# Your test logic here
if some_condition; then
    pass "Test description"
else
    fail "Test description"
fi
```

### Test Helper Functions

Available in both scripts:

- `pass "message"` - Mark test as passed (green ✓)
- `fail "message"` - Mark test as failed (red ✗)
- `info "message"` - Display info message (blue ℹ)
- `warn "message"` - Display warning (yellow ⚠)
- `section "title"` - Create new test section

---

## Configuration

### Auto-Detection (Default)

**The tests work out of the box!** They automatically detect:
- Craft project root (by finding `vendor/craftcms`)
- Plugin root (relative to test directory)
- PHP binary (checks MAMP, then system PATH)

No configuration needed in most cases!

### Custom Configuration (Optional)

If auto-detection doesn't work, you can set variables in your project's `.env` file:

```bash
# Optional: Override auto-detected PHP binary
PHP_BIN=/Applications/MAMP/bin/php/php8.2.26/bin/php

# Optional: Override auto-detected project root
PROJECT_ROOT=/Users/yourname/Sites/your-project

# Optional: Set test timeout (seconds)
TEST_TIMEOUT=60

# Optional: Enable verbose output
TEST_VERBOSE=1
```

See `tests/.env.example` for all available options.

### How Auto-Detection Works

1. **Project Root**: Walks up from plugin directory looking for `vendor/craftcms`
2. **PHP Binary**: Checks MAMP (8.2.26 first), then system PATH
3. **Plugin Root**: Always relative to test directory

The configuration is loaded from `config.sh`, which:
- Auto-detects all paths
- Loads your project's `.env` file
- Allows `.env` overrides
- Validates PHP version (8.2+ required)

---

## CI/CD Integration (Future)

These tests can be integrated into GitHub Actions:

```yaml
# .github/workflows/tests.yml
name: Tests
on: [push, pull_request]
jobs:
  test:
    runs-on: ubuntu-latest
    steps:
      - uses: actions/checkout@v3
      - name: Run smoke tests
        run: ./tests/smoke-test.sh
```

---

## Support

If tests are failing and you can't determine why:

1. Check `storage/logs/web.log` in Craft
2. Run Craft's built-in utilities
3. Verify database connection
4. Ensure all Craft requirements are met

For questions or issues with the test suite itself, contact the development team.

---

## Test Coverage

### Current Coverage
- PHP syntax validation
- File structure
- Composer configuration
- Database operations
- Plugin lifecycle (install/uninstall)
- Settings page rendering
- Twig variables
- Service loading
- Migration system

### Future Additions
- Browser automation (Playwright/Cypress)
- API endpoint testing
- Search functionality unit tests
- History tracking tests
- Front-end launcher tests
- Commerce integration tests

---

**Last Updated:** 2025-10-03
**Plugin Version:** 1.0.7
