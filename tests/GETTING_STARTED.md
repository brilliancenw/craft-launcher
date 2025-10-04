# Testing - Getting Started

Welcome to the Craft Launcher test suite! This guide will get you up and running quickly.

## Quick Start (30 seconds)

```bash
# Navigate to plugin's tests directory
cd tests

# Run all tests (paths auto-detected!)
./run-all-tests.sh
```

**No configuration needed!** The tests automatically find your Craft installation and PHP.

That's it! The script will:
1. Run smoke tests (fast, ~15 seconds)
2. Ask if you want to run integration tests (slower, ~60 seconds)

## What Gets Tested?

### Smoke Tests (Non-Destructive)
- PHP syntax on all files
- File structure integrity
- Database connectivity
- Plugin is installed and working
- Settings page renders correctly
- Twig variables are registered
- All services load properly

### Integration Tests (Destructive - Reinstalls Plugin)
- Complete uninstall/install cycle
- Database table creation/deletion
- Schema validation
- Migration system
- Post-installation functionality

## Daily Workflow

### Before Committing Code
```bash
./smoke-test.sh
```
Takes 15 seconds. Catches most issues.

### Before Creating a Release
```bash
./run-all-tests.sh
```
Choose "Yes" to run integration tests.

### Manual Testing
Use the manual checklist for comprehensive testing:
```bash
open MANUAL_TESTING.md
```

## Understanding Results

### Success
```
ALL TESTS PASSED
```
You're good to go!

### Failure
```
TESTS FAILED
Tests Failed: 3
```
Review the output above to see which tests failed.

### Common Failures

**"Plugin not installed"**
```bash
php craft plugin/install launcher
```

**"Settings page failed to render"**
```bash
php craft clear-caches/all
```

**"PHP syntax error"**
Fix the syntax error in the file indicated.

## Test Scripts

| Script | Purpose | Duration | Destructive? |
|--------|---------|----------|--------------|
| `smoke-test.sh` | Quick validation | ~15s | No |
| `integration-test.sh` | Full lifecycle | ~60s | Yes |
| `run-all-tests.sh` | Both tests | ~75s | Yes (asks first) |

## Files in This Directory

- `README.md` - Complete testing documentation
- `MANUAL_TESTING.md` - Checklist for manual testing
- `GETTING_STARTED.md` - This file!
- `smoke-test.sh` - Automated smoke tests
- `integration-test.sh` - Automated integration tests
- `run-all-tests.sh` - Run all tests in sequence

## Pre-Release Checklist

- [ ] Run `./run-all-tests.sh`
- [ ] All automated tests pass
- [ ] Review `MANUAL_TESTING.md` checklist
- [ ] Test critical paths manually:
  - Settings page loads
  - Launcher opens (Cmd+K)
  - Search works
  - History tracking works
- [ ] Update version in `composer.json`
- [ ] Update `CHANGELOG.md`
- [ ] Create git tag
- [ ] Create GitHub release

## Need Help?

- Full documentation: See `README.md`
- Manual testing: See `MANUAL_TESTING.md`
- Troubleshooting: Check test output and Craft logs

## Tips

**Run smoke tests before every commit**
They're fast and catch most issues.

**Run integration tests before releases**
They ensure the install/uninstall cycle works.

**Use the manual checklist for major releases**
Automated tests can't catch everything.

**Keep tests updated**
When you add features, add tests!

---

**Ready to test? Run:**
```bash
./run-all-tests.sh
```

Good luck! 
