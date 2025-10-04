#!/bin/bash
#
# Test Configuration
# Auto-detects paths and loads environment variables
#

# Detect script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Detect plugin root (parent of tests directory)
PLUGIN_ROOT="$(dirname "$SCRIPT_DIR")"

# Detect project root (walk up from plugin to find vendor/craftcms)
detect_project_root() {
    local current="$PLUGIN_ROOT"

    # Walk up the directory tree looking for vendor/craftcms
    while [ "$current" != "/" ]; do
        if [ -d "$current/vendor/craftcms" ] && [ -f "$current/craft" ]; then
            echo "$current"
            return 0
        fi
        current="$(dirname "$current")"
    done

    # Fallback: try common locations
    if [ -d "/Users/$USER/Sites" ]; then
        for dir in /Users/$USER/Sites/*/; do
            if [ -d "$dir/vendor/craftcms" ] && [ -f "$dir/craft" ]; then
                echo "$dir"
                return 0
            fi
        done
    fi

    return 1
}

# Auto-detect project root
PROJECT_ROOT=$(detect_project_root)

if [ -z "$PROJECT_ROOT" ]; then
    echo "ERROR: Could not auto-detect Craft project root"
    echo "Please set PROJECT_ROOT manually in config.sh or use .env"
    exit 1
fi

# Load .env file if it exists
if [ -f "$PROJECT_ROOT/.env" ]; then
    # Export variables from .env (handle both formats)
    set -a
    source <(grep -v '^#' "$PROJECT_ROOT/.env" | sed -e 's/\r$//' -e '/^$/d' -e "s/'/'\\\''/g" -e "s/=\(.*\)/='\1'/g")
    set +a
fi

# Detect PHP binary
detect_php() {
    # Check if PHP_BIN is already set in .env
    if [ -n "$PHP_BIN" ] && [ -x "$PHP_BIN" ]; then
        echo "$PHP_BIN"
        return 0
    fi

    # Check MAMP installations (try 8.2.26 first as it has fewer deprecations)
    local php_versions=(
        "/Applications/MAMP/bin/php/php8.2.26/bin/php"
        "/Applications/MAMP/bin/php/php8.4.1/bin/php"
        "/Applications/MAMP/bin/php/php8.3/bin/php"
        "/Applications/MAMP/bin/php/php8.2/bin/php"
    )

    for php_path in "${php_versions[@]}"; do
        if [ -x "$php_path" ]; then
            echo "$php_path"
            return 0
        fi
    done

    # Fallback to system PHP
    if command -v php &> /dev/null; then
        echo "php"
        return 0
    fi

    return 1
}

PHP_BIN=$(detect_php)

if [ -z "$PHP_BIN" ]; then
    echo "ERROR: Could not find PHP binary"
    echo "Please install PHP or MAMP, or set PHP_BIN in .env"
    exit 1
fi

# Verify PHP version is compatible (8.2+)
PHP_VERSION=$($PHP_BIN -r "echo PHP_VERSION;")
PHP_MAJOR=$($PHP_BIN -r "echo PHP_MAJOR_VERSION;")
PHP_MINOR=$($PHP_BIN -r "echo PHP_MINOR_VERSION;")

if [ "$PHP_MAJOR" -lt 8 ] || ([ "$PHP_MAJOR" -eq 8 ] && [ "$PHP_MINOR" -lt 2 ]); then
    echo "WARNING: PHP $PHP_VERSION detected. Craft CMS 5 requires PHP 8.2+"
fi

# Export configuration for use by test scripts
export PROJECT_ROOT
export PLUGIN_ROOT
export PHP_BIN
export PHP_VERSION

# Optional: Set test-specific env vars from .env
# These can be added to .env for custom configuration:
# TEST_TIMEOUT=60
# TEST_VERBOSE=1
export TEST_TIMEOUT="${TEST_TIMEOUT:-45}"
export TEST_VERBOSE="${TEST_VERBOSE:-0}"

# Print configuration if verbose
if [ "$TEST_VERBOSE" = "1" ]; then
    echo "Test Configuration:"
    echo "  PROJECT_ROOT: $PROJECT_ROOT"
    echo "  PLUGIN_ROOT:  $PLUGIN_ROOT"
    echo "  PHP_BIN:      $PHP_BIN"
    echo "  PHP_VERSION:  $PHP_VERSION"
    echo ""
fi
