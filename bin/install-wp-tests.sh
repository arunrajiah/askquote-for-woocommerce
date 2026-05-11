#!/usr/bin/env bash
# Install WordPress test suite and a throwaway test database.
#
# Usage:
#   bash bin/install-wp-tests.sh <db-name> <db-user> <db-pass> [db-host] [wp-version] [skip-database-creation]
#
# Example:
#   bash bin/install-wp-tests.sh wordpress_test root '' localhost latest

set -e

DB_NAME="${1:-wordpress_test}"
DB_USER="${2:-root}"
DB_PASS="${3:-}"
DB_HOST="${4:-localhost}"
WP_VERSION="${5:-latest}"
SKIP_DB_CREATE="${6:-false}"

WP_TESTS_DIR="${WP_TESTS_DIR:-/tmp/wordpress-tests-lib}"
WP_CORE_DIR="${WP_CORE_DIR:-/tmp/wordpress}"

download() {
	if command -v curl &>/dev/null; then
		curl -s "$1" >"$2"
	elif command -v wget &>/dev/null; then
		wget -nv -O "$2" "$1"
	fi
}

if [[ "$WP_VERSION" == 'latest' ]]; then
	local_version_url="https://api.wordpress.org/core/version-check/1.7/"
	WP_VERSION=$(download "$local_version_url" - | grep -o '"version":"[^"]*"' | head -1 | cut -d'"' -f4)
	echo "Latest WordPress version: $WP_VERSION"
fi

WP_TESTS_TAG="tags/$WP_VERSION"
if [[ "$WP_VERSION" == 'trunk' ]]; then
	WP_TESTS_TAG='trunk'
fi

set -ex

install_wp() {
	if [[ -d "$WP_CORE_DIR/src" ]]; then
		return
	fi
	mkdir -p "$WP_CORE_DIR"
	download "https://wordpress.org/wordpress-${WP_VERSION}.tar.gz" /tmp/wordpress.tar.gz
	tar --strip-components=1 -zxmf /tmp/wordpress.tar.gz -C "$WP_CORE_DIR"
	download "https://raw.github.com/markoheijnen/wp-mysqli/master/db.php" "$WP_CORE_DIR/src/wp-content/db.php"
}

install_test_suite() {
	if [[ -d "$WP_TESTS_DIR" ]]; then
		return
	fi
	mkdir -p "$WP_TESTS_DIR"
	svn co --quiet "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/includes/" "$WP_TESTS_DIR/includes"
	svn co --quiet "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/tests/phpunit/data/"     "$WP_TESTS_DIR/data"

	download "https://develop.svn.wordpress.org/${WP_TESTS_TAG}/wp-tests-config-sample.php" "$WP_TESTS_DIR/wp-tests-config.php"

	# Point the test config at the core install and test DB.
	local config="$WP_TESTS_DIR/wp-tests-config.php"
	if [[ "$(uname -s)" == 'Darwin' ]]; then
		sed_i() { sed -i '' "$@"; }
	else
		sed_i() { sed -i "$@"; }
	fi
	sed_i "s|dirname( __FILE__ ) . '/src/'|'$WP_CORE_DIR/src/'|" "$config"
	sed_i "s/youremptytestdbnamehere/$DB_NAME/"           "$config"
	sed_i "s/yourusernamehere/$DB_USER/"                  "$config"
	sed_i "s/yourpasswordhere/$DB_PASS/"                  "$config"
	sed_i "s|localhost|$DB_HOST|"                         "$config"
}

install_db() {
	if [[ "$SKIP_DB_CREATE" == 'true' ]]; then
		return
	fi
	mysqladmin create "$DB_NAME" --user="$DB_USER" --password="$DB_PASS" --host="$DB_HOST" 2>/dev/null || true
}

install_wp
install_test_suite
install_db
