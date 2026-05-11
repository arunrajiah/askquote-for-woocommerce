# AskQuote for WooCommerce

A production-quality, WordPress.org-compliant "Request a Quote" plugin for WooCommerce.

## Requirements

- PHP 7.4+
- WordPress 6.2+
- WooCommerce 7.0+
- Composer (dev only)

## Local Development Setup

```bash
# Clone
git clone <repo-url> askquote-for-woocommerce
cd askquote-for-woocommerce

# Install dev dependencies
composer install

# Install WordPress test suite (adjust arguments)
bash bin/install-wp-tests.sh wordpress_test root '' localhost latest
```

## Running Tests

```bash
composer run test
# or directly:
./vendor/bin/phpunit
```

## Linting

```bash
# Check coding standards
composer run lint

# Auto-fix where possible
composer run lint-fix
```

## Project Structure

```
askquote-for-woocommerce/
├── includes/
│   ├── admin/           Admin screens, settings, list table, meta box
│   ├── frontend/        Quote button, cart, form, My Account page
│   ├── post-types/      CPT registration
│   ├── emails/          WC_Email subclasses + manager
│   ├── api/             REST API controller
│   ├── helpers/         Global functions, status helper
│   └── extensibility/   Hook documentation class
├── templates/           Overridable templates (emails + frontend)
├── assets/              CSS and JS
└── tests/               PHPUnit bootstrap and test cases
```

## Overriding Templates

Copy any file from `templates/` to `wp-content/themes/your-theme/askquote-for-woocommerce/` maintaining the same relative path. WordPress theme directories take precedence.

## Key Hooks

| Hook | Type | Description |
|------|------|-------------|
| `askquote_loaded` | action | After plugin init |
| `askquote_quote_submitted` | action | After quote created |
| `askquote_quote_status_changed` | action | On status change |
| `askquote_quote_button_visible` | filter | Control button visibility per product |
| `askquote_quote_button_html` | filter | Customise button HTML |
| `askquote_quote_form_fields` | filter | Add/remove/reorder form fields |
| `askquote_quote_data_before_save` | filter | Modify data before DB insert |
| `askquote_email_recipients` | filter | Customise email recipients |

See `includes/extensibility/class-hook-registry.php` for the full documented list.

## REST API

Base URL: `{site_url}/wp-json/askquote/v1`

| Method | Endpoint | Auth Required |
|--------|----------|---------------|
| POST | `/quotes` | None |
| GET | `/quotes` | `manage_woocommerce` |
| GET | `/quotes/{id}` | Admin or quote owner |
| PATCH | `/quotes/{id}/status` | `manage_woocommerce` |

## Release Process

1. Update `CHANGELOG.md` and `readme.txt`.
2. Bump version in `askquote-for-woocommerce.php` and `class-askquote.php`.
3. Tag a release: `git tag v0.x.0 && git push --tags`.
4. The `deploy-wp-org.yml` GitHub Action deploys to WordPress.org SVN automatically (requires `SVN_USERNAME` and `SVN_PASSWORD` secrets).

## License

GPL v2 or later — see [LICENSE](LICENSE).
