# Manual Testing Checklist

Use this checklist before each release to ensure all features work correctly.

## Pre-Release Testing Checklist

### Installation & Setup

- [ ] **Fresh Install Test**
  ```bash
  # Navigate to your Craft project root, then:
  php craft plugin/uninstall launcher
  php craft plugin/install launcher
  ```
  - Verify no errors during installation
  - Check that database table is created

- [ ] **Version Check**
  - Open `composer.json` - verify version matches release
  - Open `CHANGELOG.md` - verify latest version is documented
  - Check git tag matches version

---

### Settings Page

- [ ] **Access Settings**
  - Navigate to: Settings → Plugins → Launcher
  - Page loads without errors
  - No Twig errors displayed

- [ ] **Table Status Display**
  - Scroll to "System Diagnostics" section
  - Verify "User History Table" shows green checkmark
  - If table is missing, verify link to utility appears

- [ ] **Settings Form**
  - Keyboard Shortcut field is populated (default: `cmd+k`)
  - Search Delay has value (default: `300`)
  - Maximum Results has value (default: `5`)
  - All lightswitches can be toggled
  - Click "Save" - verify success message

- [ ] **Project Config Read-Only Mode**
  - If applicable, verify read-only notice displays
  - Verify form fields are disabled

---

### Launcher Functionality

- [ ] **Open Launcher**
  - Press `Cmd+K` (Mac) or `Ctrl+K` (Windows)
  - Launcher modal appears
  - Search input is focused

- [ ] **Search Entries**
  - Type an entry name
  - Verify results appear
  - Verify entry titles display
  - Verify sections show correctly
  - Click a result - navigates to edit page

- [ ] **Search Other Types**
  - Search for a user
  - Search for a category
  - Search for an asset
  - Search for settings pages
  - All results display correctly

- [ ] **Browse Mode**
  - Type `*` in search
  - Verify browse categories appear:
    - Entries
    - Categories
    - Assets
    - Users
    - Settings
    - etc.
  - Select a category - shows items

- [ ] **Keyboard Navigation**
  - Use arrow keys to navigate results
  - Press `Enter` on first result - opens
  - Press `Cmd+1` (or configured modifier) - opens second result
  - Press `Esc` - closes launcher

- [ ] **History Tracking**
  - Launch several items
  - Close and reopen launcher
  - Type partial name of recently launched item
  - Verify it appears higher in results (popular items)

---

### Utility Page

- [ ] **Access Utility**
  - Navigate to: Utilities → Launcher
  - Page loads without errors

- [ ] **Table Status**
  - Verify table status displays
  - If table exists: shows statistics (record count, user count)
  - If table missing: shows create button

- [ ] **Create Table** (if missing)
  - Click "Create Table" button
  - Verify success message
  - Refresh page
  - Verify table now shows as existing

---

### Front-End Integration

- [ ] **Enable Front-End Launcher**
  - Navigate to: Account → Launcher (in user preferences)
  - Toggle "Enable Front-End Launcher" on
  - Save settings

- [ ] **Test Front-End**
  - Open website front-end in browser
  - Ensure you're logged in
  - Press `Cmd+K` (or configured shortcut)
  - Launcher appears on front-end
  - Search works
  - Context actions appear ("Edit this page" if applicable)

- [ ] **Security Check**
  - Log out
  - Try to open launcher
  - Verify launcher does NOT appear when logged out

---

### Database & Migrations

- [ ] **Migration Status**
  ```bash
  php craft migrate/status --plugin=launcher
  ```
  - Verify "No new migrations found"

- [ ] **Table Exists**
  - Check database directly or use utility page
  - Verify `craft_launcher_user_history` table exists
  - Verify columns are correct

---

### Error Handling

- [ ] **Missing Table Scenario**
  ```bash
  # Drop the table manually in database, then:
  ```
  - Open settings page - should show warning with link to utility
  - Open utility - should show "Create Table" option
  - Plugin doesn't crash, search still works (just no history)

- [ ] **Orphaned Entries**
  - If you have entries with deleted sections
  - Search should still work
  - Results should show "Unknown Section" or similar

---

### Commerce Integration (if Commerce installed)

- [ ] **Commerce Search Options**
  - Settings page shows "Commerce Search Options" section
  - Toggle "Search Customers" on
  - Toggle "Search Products" on
  - Toggle "Search Orders" on
  - Save settings

- [ ] **Commerce Search**
  - Open launcher
  - Search for a customer name
  - Search for a product
  - Search for an order number
  - All return appropriate results

---

### Code Quality

- [ ] **PHP Syntax**
  ```bash
  find src -name "*.php" -exec php -l {} \;
  ```
  - No syntax errors

- [ ] **Composer Validation**
  ```bash
  composer validate --no-check-all
  ```
  - composer.json is valid

---

### Documentation

- [ ] **README.md**
  - Installation instructions are accurate
  - Features list is up to date
  - Screenshots (if any) are current

- [ ] **CHANGELOG.md**
  - Latest version has entry
  - All changes documented
  - Date is correct

- [ ] **GitHub Release**
  - Release notes match CHANGELOG
  - Installation instructions included
  - Thanks contributors if applicable

---

## Quick Smoke Test (5 minutes)

If you're short on time, run through these critical tests:

1. Settings page loads without errors
2. Launcher opens with keyboard shortcut
3. Search returns results
4. Can navigate to an entry
5. Utility page loads
6. No console errors in browser

If all 6 pass, you're probably good to release!

---

## Test Automation

For automated testing, see:
- `smoke-test.sh` - Quick automated validation
- `integration-test.sh` - Full install/uninstall cycle
- `README.md` - Full testing documentation

---

## Troubleshooting Test Failures

### Settings Page Won't Load
**Issue:** Twig error about `craft.launcher`
**Fix:**
1. Verify `src/variables/LauncherVariable.php` exists
2. Check `src/Launcher.php` registers the variable
3. Clear caches: `php craft clear-caches/all`

### Launcher Won't Open
**Issue:** Keyboard shortcut doesn't work
**Fix:**
1. Check browser console for JavaScript errors
2. Verify asset bundles are loading
3. Check user has `accessLauncher` permission

### Search Returns No Results
**Issue:** Search is empty
**Fix:**
1. Rebuild search indexes: `php craft resave/entries`
2. Check database connection
3. Verify user has permissions to view content

### History Not Tracking
**Issue:** Popular items don't appear
**Fix:**
1. Verify table exists (utility page)
2. Check table has correct schema
3. Ensure `enableLaunchHistory` setting is on

---

**Tip:** Keep this checklist handy and check items off before each release!
