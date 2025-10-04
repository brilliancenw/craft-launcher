#!/bin/bash
#
# Run All Tests Script
# Runs smoke tests followed by integration tests
#

set -e

# Colors
RED='\033[0;31m'
GREEN='\033[0;32m'
YELLOW='\033[1;33m'
BLUE='\033[0;34m'
NC='\033[0m'

echo -e "${BLUE}╔═══════════════════════════════════════════════════════════╗${NC}"
echo -e "${BLUE}║        Craft Launcher - Complete Test Suite              ║${NC}"
echo -e "${BLUE}╚═══════════════════════════════════════════════════════════╝${NC}"
echo ""

# Get script directory
SCRIPT_DIR="$( cd "$( dirname "${BASH_SOURCE[0]}" )" && pwd )"

# Run smoke tests
echo -e "${BLUE}Step 1/2: Running Smoke Tests...${NC}"
echo ""

if "$SCRIPT_DIR/smoke-test.sh"; then
    echo ""
    echo -e "${GREEN}✅ Smoke tests passed!${NC}"
    echo ""
else
    echo ""
    echo -e "${RED}❌ Smoke tests failed!${NC}"
    echo -e "${YELLOW}Fix smoke test failures before running integration tests.${NC}"
    exit 1
fi

# Ask before running integration tests
echo ""
echo -e "${YELLOW}━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━━${NC}"
echo -e "${BLUE}Step 2/2: Integration Tests${NC}"
echo -e "${YELLOW}⚠ WARNING: Integration tests will uninstall and reinstall the plugin!${NC}"
echo ""
read -p "Run integration tests? (y/N) " -n 1 -r
echo
echo ""

if [[ $REPLY =~ ^[Yy]$ ]]; then
    if "$SCRIPT_DIR/integration-test.sh"; then
        echo ""
        echo -e "${GREEN}╔═══════════════════════════════════════════════════════════╗${NC}"
        echo -e "${GREEN}║              ✅ ALL TESTS PASSED                          ║${NC}"
        echo -e "${GREEN}╚═══════════════════════════════════════════════════════════╝${NC}"
        echo ""
        echo -e "${GREEN}🎉 Your plugin is ready for release!${NC}"
        echo ""
        exit 0
    else
        echo ""
        echo -e "${RED}╔═══════════════════════════════════════════════════════════╗${NC}"
        echo -e "${RED}║              ❌ INTEGRATION TESTS FAILED                  ║${NC}"
        echo -e "${RED}╚═══════════════════════════════════════════════════════════╝${NC}"
        echo ""
        echo -e "${YELLOW}Please review the failures above and fix before releasing.${NC}"
        echo ""
        exit 1
    fi
else
    echo -e "${YELLOW}Skipped integration tests.${NC}"
    echo ""
    echo -e "${GREEN}✅ Smoke tests passed${NC}"
    echo -e "${YELLOW}⏭  Integration tests skipped${NC}"
    echo ""
    echo -e "${BLUE}To run integration tests later:${NC}"
    echo "  ./integration-test.sh"
    echo ""
    exit 0
fi
