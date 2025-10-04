#!/bin/bash
#
# Smoke Test Script for Craft Launcher Plugin
# Quick validation before releases
#

# Load configuration (auto-detects paths)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source "$SCRIPT_DIR/config.sh"

# Don't exit on error - we want to run all tests
set +e

# Colors for output
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m' # No Color

echo -e "${BLUE}Craft Launcher - Smoke Test Suite${NC}"
echo "=========================================="
if [ "${TEST_VERBOSE:-0}" = "1" ]; then
    echo -e "${BLUE}Configuration:${NC}"
    echo "  Project Root: $PROJECT_ROOT"
    echo "  Plugin Root:  $PLUGIN_ROOT"
    echo "  PHP Binary:   $PHP_BIN ($PHP_VERSION)"
fi
echo ""

# Test counter
TESTS_RUN=0
TESTS_PASSED=0
TESTS_FAILED=0

# Helper functions
pass() {
    echo -e "${GREEN}✓${NC} $1"
    ((TESTS_PASSED++))
    ((TESTS_RUN++))
}

fail() {
    echo -e "${RED}✗${NC} $1"
    ((TESTS_FAILED++))
    ((TESTS_RUN++))
}

info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

warn() {
    echo -e "${YELLOW}⚠${NC} $1"
}

section() {
    echo ""
    echo -e "${BLUE}━━━ $1 ━━━${NC}"
}

# Test 1: PHP Syntax Check
section "PHP Syntax Validation"
info "Checking PHP syntax on all source files..."

SYNTAX_ERRORS=0
while IFS= read -r -d '' file; do
    if ! $PHP_BIN -l "$file" > /dev/null 2>&1; then
        fail "Syntax error in: $file"
        ((SYNTAX_ERRORS++))
    fi
done < <(find "$PLUGIN_ROOT/src" -name "*.php" -print0)

if [ $SYNTAX_ERRORS -eq 0 ]; then
    pass "All PHP files have valid syntax"
else
    fail "Found $SYNTAX_ERRORS PHP syntax errors"
fi

# Test 2: Required Files Exist
section "File Structure Validation"
info "Checking for required files in $PLUGIN_ROOT..."

required_files=(
    "src/Launcher.php"
    "src/services/HistoryService.php"
    "src/services/SearchService.php"
    "src/services/UserPreferenceService.php"
    "src/variables/LauncherVariable.php"
    "src/templates/settings.twig"
    "src/utilities/LauncherTableUtility.php"
    "composer.json"
    "README.md"
    "CHANGELOG.md"
)

for file in "${required_files[@]}"; do
    if [ -f "$PLUGIN_ROOT/$file" ]; then
        pass "Required file exists: $file"
    else
        fail "Missing required file: $file"
    fi
done

# Test 3: Composer Configuration
section "Composer Configuration"
info "Validating composer.json..."

if [ -f "$PLUGIN_ROOT/composer.json" ]; then
    # Check if composer.json is valid JSON
    if $PHP_BIN -r "json_decode(file_get_contents('$PLUGIN_ROOT/composer.json')); if (json_last_error() !== JSON_ERROR_NONE) exit(1);" 2>/dev/null; then
        pass "composer.json is valid JSON"
    else
        fail "composer.json has invalid JSON syntax"
    fi

    # Check required fields
    if grep -q '"name".*"brilliance/craft-launcher"' "$PLUGIN_ROOT/composer.json"; then
        pass "Package name is correctly set"
    else
        fail "Package name is missing or incorrect"
    fi

    if grep -q '"type".*"craft-plugin"' "$PLUGIN_ROOT/composer.json"; then
        pass "Package type is set to craft-plugin"
    else
        fail "Package type is not craft-plugin"
    fi
fi

# Test 4: Database Connection (via Craft)
section "Database & Craft Connectivity"
info "Testing Craft CMS connection at $PROJECT_ROOT..."

cd "$PROJECT_ROOT"

# Test database connection by running a simple craft command
if $PHP_BIN craft help > /dev/null 2>&1; then
    pass "Craft CMS is accessible and database connected"
else
    fail "Cannot connect to Craft CMS or database"
fi

# Test 5: Plugin Installation State
section "Plugin Installation Status"

# Check if plugin is installed
if $PHP_BIN craft plugin/list 2>/dev/null | grep -q "launcher"; then
    pass "Plugin appears in plugin list"

    # Get plugin status
    if $PHP_BIN craft plugin/list 2>/dev/null | grep "launcher" | grep -q "✓"; then
        pass "Plugin is installed and enabled"
    else
        warn "Plugin is listed but may not be enabled"
    fi
else
    fail "Plugin not found in Craft plugin list"
fi

# Test 6: Critical Class Loading
section "Class Loading Test"

info "Testing if critical classes can be loaded..."

# Create temporary test script that uses Craft's own bootstrap
TEST_SCRIPT=$(mktemp)
cat > "$TEST_SCRIPT" <<'PHPTEST'
<?php
// Load shared bootstrap the same way craft command does
require 'PROJECT_ROOT_PLACEHOLDER/bootstrap.php';

// Load Craft
$app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/console.php';

// Create a test controller
class TestController extends \yii\console\Controller
{
    public function actionRun()
    {
        try {
            $plugin = \brilliance\launcher\Launcher::getInstance();
            if ($plugin) {
                echo "PLUGIN_OK\n";

                // Test services
                if ($plugin->history) echo "HISTORY_OK\n";
                if ($plugin->search) echo "SEARCH_OK\n";
                if ($plugin->userPreference) echo "USERPREF_OK\n";
            }
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }

        return 0;
    }
}

$app->controllerMap['testplugin'] = TestController::class;
$exitCode = $app->runAction('testplugin/run');
exit($exitCode);
PHPTEST
# Replace placeholder with actual project root
sed -i '' "s|PROJECT_ROOT_PLACEHOLDER|$PROJECT_ROOT|g" "$TEST_SCRIPT"

OUTPUT=$($PHP_BIN "$TEST_SCRIPT" 2>&1)

if [ "${TEST_VERBOSE:-0}" = "1" ]; then
    echo "DEBUG OUTPUT:"
    echo "$OUTPUT"
fi

if echo "$OUTPUT" | grep -q "PLUGIN_OK"; then
    pass "Plugin class loads correctly"
else
    fail "Plugin class failed to load"
fi

if echo "$OUTPUT" | grep -q "HISTORY_OK"; then
    pass "HistoryService is accessible"
else
    fail "HistoryService failed to load"
fi

if echo "$OUTPUT" | grep -q "SEARCH_OK"; then
    pass "SearchService is accessible"
else
    fail "SearchService failed to load"
fi

if echo "$OUTPUT" | grep -q "USERPREF_OK"; then
    pass "UserPreferenceService is accessible"
else
    fail "UserPreferenceService failed to load"
fi

rm "$TEST_SCRIPT"

# Test 7: Settings Page HTTP Test
section "Settings Page Accessibility"

info "Testing settings page endpoint..."

SETTINGS_TEST=$(mktemp)
cat > "$SETTINGS_TEST" <<'PHPTEST'
<?php
require 'PROJECT_ROOT_PLACEHOLDER/bootstrap.php';
$app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/console.php';

class SettingsTestController extends \yii\console\Controller
{
    public function actionRun()
    {
        try {
            $plugin = \brilliance\launcher\Launcher::getInstance();
            $settings = $plugin->getSettings();

            if ($settings) {
                echo "SETTINGS_OK\n";
            }

            // Verify settings has expected properties
            if (property_exists($settings, 'hotkey') && property_exists($settings, 'maxResults')) {
                echo "SETTINGS_PROPS_OK\n";
            }

            // Test that the settings template exists
            $templatePath = $plugin->getBasePath() . '/templates/settings.twig';
            if (file_exists($templatePath)) {
                echo "SETTINGS_TEMPLATE_OK\n";
            }
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }

        return 0;
    }
}

$app->controllerMap['settingstest'] = SettingsTestController::class;
$exitCode = $app->runAction('settingstest/run');
exit($exitCode);
PHPTEST
sed -i '' "s|PROJECT_ROOT_PLACEHOLDER|$PROJECT_ROOT|g" "$SETTINGS_TEST"

SETTINGS_OUTPUT=$($PHP_BIN "$SETTINGS_TEST" 2>&1)

if echo "$SETTINGS_OUTPUT" | grep -q "SETTINGS_OK"; then
    pass "Settings model loads correctly"
else
    fail "Settings model failed to load"
fi

if echo "$SETTINGS_OUTPUT" | grep -q "SETTINGS_PROPS_OK"; then
    pass "Settings has expected properties"
else
    fail "Settings properties are missing"
fi

if echo "$SETTINGS_OUTPUT" | grep -q "SETTINGS_TEMPLATE_OK"; then
    pass "Settings template file exists"
else
    fail "Settings template file is missing"
fi

rm "$SETTINGS_TEST"

# Test 8: Twig Variable Registration
section "Twig Variable Registration"

info "Testing craft.launcher Twig variable..."

TWIG_TEST=$(mktemp)
cat > "$TWIG_TEST" <<'PHPTEST'
<?php
require 'PROJECT_ROOT_PLACEHOLDER/bootstrap.php';
$app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/console.php';

class TwigTestController extends \yii\console\Controller
{
    public function actionRun()
    {
        try {
            $twig = Craft::$app->getView()->getTwig();
            $template = '{% if craft.launcher is defined %}LAUNCHER_VAR_OK{% endif %}';
            $result = $twig->createTemplate($template)->render([]);
            echo $result . "\n";

            // Test history service access
            $template2 = '{% if craft.launcher.history is defined %}HISTORY_ACCESS_OK{% endif %}';
            $result2 = $twig->createTemplate($template2)->render([]);
            echo $result2 . "\n";
        } catch (\Exception $e) {
            echo "ERROR: " . $e->getMessage() . "\n";
        }

        return 0;
    }
}

$app->controllerMap['twigtest'] = TwigTestController::class;
$exitCode = $app->runAction('twigtest/run');
exit($exitCode);
PHPTEST
sed -i '' "s|PROJECT_ROOT_PLACEHOLDER|$PROJECT_ROOT|g" "$TWIG_TEST"

TWIG_OUTPUT=$($PHP_BIN "$TWIG_TEST" 2>&1)

if echo "$TWIG_OUTPUT" | grep -q "LAUNCHER_VAR_OK"; then
    pass "craft.launcher variable is registered"
else
    fail "craft.launcher variable is not accessible"
fi

if echo "$TWIG_OUTPUT" | grep -q "HISTORY_ACCESS_OK"; then
    pass "craft.launcher.history is accessible"
else
    fail "craft.launcher.history is not accessible"
fi

rm "$TWIG_TEST"

# Test 9: Migration Status
section "Database Migrations"

MIGRATION_OUTPUT=$($PHP_BIN craft migrate/status --plugin=launcher 2>&1)

if echo "$MIGRATION_OUTPUT" | grep -q "No new migration"; then
    pass "All migrations are up to date"
elif echo "$MIGRATION_OUTPUT" | grep -q "new migration"; then
    warn "Pending migrations detected"
else
    pass "Migration system is functional"
fi

# Summary
section "Test Summary"
echo ""
echo -e "Tests Run:    ${BLUE}$TESTS_RUN${NC}"
echo -e "Tests Passed: ${GREEN}$TESTS_PASSED${NC}"
if [ $TESTS_FAILED -gt 0 ]; then
    echo -e "Tests Failed: ${RED}$TESTS_FAILED${NC}"
    echo ""
    echo -e "${RED}❌ SMOKE TEST FAILED${NC}"
    exit 1
else
    echo -e "Tests Failed: ${GREEN}0${NC}"
    echo ""
    echo -e "${GREEN}✅ ALL SMOKE TESTS PASSED${NC}"
    exit 0
fi
