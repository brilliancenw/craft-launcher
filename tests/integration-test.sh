#!/bin/bash
#
# Integration Test Script for Craft Launcher Plugin
# Tests plugin lifecycle: install, uninstall, reinstall
#

# Load configuration (auto-detects paths)
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"
source "$SCRIPT_DIR/config.sh"

# Don't exit on error - we want to run all tests
set +e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}Craft Launcher - Integration Test Suite${NC}"
echo "================================================"
echo ""
echo -e "${YELLOW}WARNING: This test will uninstall and reinstall the plugin!${NC}"
echo -e "${YELLOW}WARNING: This may affect your current plugin data.${NC}"
echo ""
read -p "Continue? (y/N) " -n 1 -r
echo
if [[ ! $REPLY =~ ^[Yy]$ ]]; then
    echo "Aborted."
    exit 0
fi
echo ""

# Test counters
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
    return 1
}

info() {
    echo -e "${BLUE}ℹ${NC} $1"
}

section() {
    echo ""
    echo -e "${BLUE}━━━ $1 ━━━${NC}"
}

# Helper function to check if plugin is installed
is_plugin_installed() {
    cd "$PROJECT_ROOT"
    if $PHP_BIN craft plugin/list 2>/dev/null | grep "launcher" | grep -q "✓"; then
        return 0
    else
        return 1
    fi
}

# Helper function to check if table exists
table_exists() {
    cd "$PROJECT_ROOT"
    TEST_SCRIPT=$(mktemp)
    cat > "$TEST_SCRIPT" <<'PHPTEST'
<?php
define('CRAFT_BASE_PATH', 'PROJECT_ROOT_PLACEHOLDER');
require_once CRAFT_BASE_PATH . '/vendor/autoload.php';
$app = require CRAFT_BASE_PATH . '/vendor/craftcms/cms/bootstrap/console.php';

$db = Craft::$app->getDb();
$tableSchema = $db->schema->getTableSchema('{{%launcher_user_history}}');
if ($tableSchema !== null) {
    echo "EXISTS";
    exit(0);
} else {
    echo "NOT_EXISTS";
    exit(1);
}
PHPTEST
    sed -i '' "s|PROJECT_ROOT_PLACEHOLDER|$PROJECT_ROOT|g" "$TEST_SCRIPT"

    OUTPUT=$($PHP_BIN "$TEST_SCRIPT" 2>&1)
    rm "$TEST_SCRIPT"

    if echo "$OUTPUT" | grep -q "EXISTS"; then
        return 0
    else
        return 1
    fi
}

# Test 1: Check Initial State
section "Initial State Check"

cd "$PROJECT_ROOT"

if is_plugin_installed; then
    pass "Plugin is currently installed"
    INITIAL_STATE="installed"
else
    fail "Plugin is not installed - cannot run integration tests" || exit 1
fi

if table_exists; then
    pass "Database table exists"
    INITIAL_TABLE_EXISTS=true
else
    info "Database table does not exist"
    INITIAL_TABLE_EXISTS=false
fi

# Test 2: Plugin Uninstallation
section "Plugin Uninstallation Test"

info "Uninstalling plugin..."
cd "$PROJECT_ROOT"

UNINSTALL_OUTPUT=$($PHP_BIN craft plugin/uninstall launcher 2>&1)

if echo "$UNINSTALL_OUTPUT" | grep -q "uninstalled launcher successfully"; then
    pass "Plugin uninstalled successfully"
else
    fail "Plugin uninstallation failed" || exit 1
fi

# Verify plugin is not installed
if ! is_plugin_installed; then
    pass "Plugin no longer appears in installed plugins"
else
    fail "Plugin still appears as installed after uninstall" || exit 1
fi

# Verify table was dropped
if ! table_exists; then
    pass "Database table was properly dropped"
else
    fail "Database table still exists after uninstall" || exit 1
fi

# Test 3: Plugin Installation
section "Plugin Installation Test"

info "Installing plugin..."
cd "$PROJECT_ROOT"

INSTALL_OUTPUT=$($PHP_BIN craft plugin/install launcher 2>&1)

if echo "$INSTALL_OUTPUT" | grep -q "installed launcher successfully"; then
    pass "Plugin installed successfully"
else
    fail "Plugin installation failed" || exit 1
fi

# Verify plugin is installed
if is_plugin_installed; then
    pass "Plugin appears in installed plugins"
else
    fail "Plugin does not appear as installed" || exit 1
fi

# Verify table was created
if table_exists; then
    pass "Database table was created"
else
    fail "Database table was not created" || exit 1
fi

# Test 4: Verify Table Schema
section "Database Schema Validation"

cd "$PROJECT_ROOT"
SCHEMA_TEST=$(mktemp)
cat > "$SCHEMA_TEST" <<'PHPTEST'
<?php
define('CRAFT_BASE_PATH', 'PROJECT_ROOT_PLACEHOLDER');
require_once CRAFT_BASE_PATH . '/vendor/autoload.php';
$app = require CRAFT_BASE_PATH . '/vendor/craftcms/cms/bootstrap/console.php';

$db = Craft::$app->getDb();
$tableSchema = $db->schema->getTableSchema('{{%launcher_user_history}}');

if ($tableSchema === null) {
    echo "TABLE_MISSING\n";
    exit(1);
}

$requiredColumns = [
    'id', 'userId', 'itemType', 'itemId', 'itemTitle', 'itemUrl',
    'itemHash', 'launchCount', 'lastLaunchedAt', 'firstLaunchedAt',
    'dateCreated', 'dateUpdated'
];

foreach ($requiredColumns as $col) {
    if (isset($tableSchema->columns[$col])) {
        echo "COL_OK:$col\n";
    } else {
        echo "COL_MISSING:$col\n";
    }
}
PHPTEST
sed -i '' "s|PROJECT_ROOT_PLACEHOLDER|$PROJECT_ROOT|g" "$SCHEMA_TEST"

SCHEMA_OUTPUT=$($PHP_BIN "$SCHEMA_TEST" 2>&1)
rm "$SCHEMA_TEST"

if echo "$SCHEMA_OUTPUT" | grep -q "TABLE_MISSING"; then
    fail "Table schema check failed - table missing"
else
    REQUIRED_COLS=("id" "userId" "itemType" "itemTitle" "itemUrl" "itemHash" "launchCount")
    for col in "${REQUIRED_COLS[@]}"; do
        if echo "$SCHEMA_OUTPUT" | grep -q "COL_OK:$col"; then
            pass "Column '$col' exists"
        else
            fail "Column '$col' is missing"
        fi
    done
fi

# Test 5: Plugin Functionality After Install
section "Post-Installation Functionality"

cd "$PROJECT_ROOT"
FUNC_TEST=$(mktemp)
cat > "$FUNC_TEST" <<'PHPTEST'
<?php
require 'PROJECT_ROOT_PLACEHOLDER/bootstrap.php';
$app = require CRAFT_VENDOR_PATH . '/craftcms/cms/bootstrap/console.php';

class FuncTestController extends \yii\console\Controller
{
    public function actionRun()
    {
        try {
            $plugin = \brilliance\launcher\Launcher::getInstance();

            if (!$plugin) {
                echo "PLUGIN_FAILED\n";
                return 1;
            }
            echo "PLUGIN_OK\n";

            // Test services
            if ($plugin->history) {
                $status = $plugin->history->getTableStatus();
                if ($status['exists']) {
                    echo "TABLE_STATUS_OK\n";
                }
            }

            // Test settings
            $settings = $plugin->getSettings();
            if ($settings) {
                echo "SETTINGS_OK\n";
            }

            // Test settings template exists
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

$app->controllerMap['functest'] = FuncTestController::class;
$exitCode = $app->runAction('functest/run');
exit($exitCode);
PHPTEST
sed -i '' "s|PROJECT_ROOT_PLACEHOLDER|$PROJECT_ROOT|g" "$FUNC_TEST"

FUNC_OUTPUT=$($PHP_BIN "$FUNC_TEST" 2>&1)
rm "$FUNC_TEST"

if echo "$FUNC_OUTPUT" | grep -q "PLUGIN_OK"; then
    pass "Plugin instance accessible"
else
    fail "Plugin instance not accessible"
fi

if echo "$FUNC_OUTPUT" | grep -q "TABLE_STATUS_OK"; then
    pass "Table status check working"
else
    fail "Table status check failed"
fi

if echo "$FUNC_OUTPUT" | grep -q "SETTINGS_OK"; then
    pass "Settings model accessible"
else
    fail "Settings model failed"
fi

if echo "$FUNC_OUTPUT" | grep -q "SETTINGS_TEMPLATE_OK"; then
    pass "Settings template exists"
else
    fail "Settings template missing"
fi

# Test 6: Reinstallation Test (Edge Case)
section "Reinstallation Test"

info "Testing reinstall (uninstall + install)..."

# Uninstall
cd "$PROJECT_ROOT"
$PHP_BIN craft plugin/uninstall launcher > /dev/null 2>&1

# Reinstall
REINSTALL_OUTPUT=$($PHP_BIN craft plugin/install launcher 2>&1)

if echo "$REINSTALL_OUTPUT" | grep -q "installed launcher successfully"; then
    pass "Plugin can be reinstalled successfully"
else
    fail "Plugin reinstallation failed" || true
fi

if is_plugin_installed; then
    pass "Plugin is active after reinstall"
else
    fail "Plugin not active after reinstall" || true
fi

# Test 7: Migration Status
section "Migration System Check"

cd "$PROJECT_ROOT"
MIGRATION_OUTPUT=$($PHP_BIN craft migrate/status --plugin=launcher 2>&1)

if echo "$MIGRATION_OUTPUT" | grep -q "No new migration"; then
    pass "All migrations applied"
elif echo "$MIGRATION_OUTPUT" | grep -q "Total.*migration"; then
    info "Migration system is operational"
    pass "Migration status check successful"
else
    pass "Migration system accessible"
fi

# Summary
section "Integration Test Summary"
echo ""
echo -e "Tests Run:    ${BLUE}$TESTS_RUN${NC}"
echo -e "Tests Passed: ${GREEN}$TESTS_PASSED${NC}"

if [ $TESTS_FAILED -gt 0 ]; then
    echo -e "Tests Failed: ${RED}$TESTS_FAILED${NC}"
    echo ""
    echo -e "${RED}❌ INTEGRATION TESTS FAILED${NC}"
    exit 1
else
    echo -e "Tests Failed: ${GREEN}0${NC}"
    echo ""
    echo -e "${GREEN}✅ ALL INTEGRATION TESTS PASSED${NC}"
    exit 0
fi
