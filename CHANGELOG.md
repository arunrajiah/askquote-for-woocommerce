# Changelog

All notable changes to this project will be documented in this file.

The format is based on [Keep a Changelog](https://keepachangelog.com/en/1.0.0/),
and this project adheres to [Semantic Versioning](https://semver.org/spec/v2.0.0.html).

## [1.0.0] - 2026-05-11

### Added

- Initial release.
- Quote button on product pages and shop loop with configurable visibility (all, by category, by tag, per product).
- Session-based quote cart (independent of WooCommerce shopping cart).
- Quote submission form shortcode `[askquote_form]` with nonce protection and full sanitization.
- Quote cart shortcode `[askquote_cart]`.
- Quote button shortcode `[askquote_button]`.
- Admin quote management: custom `askquote_quote` CPT with four statuses (Pending, Replied, Approved, Closed).
- WP_List_Table-based quotes list with status tabs, bulk actions, and pagination.
- Quote detail meta box with customer info, item list, status dropdown, and admin reply.
- Settings page using WordPress Settings API: visibility, button label/colour, email subjects, uninstall option.
- Three WooCommerce-styled email notifications: customer confirmation, admin new-quote alert, customer approval notice.
- My Account endpoint `/my-account/quotes` for logged-in customers.
- REST API at `askquote/v1/quotes`.
- `askquote_quote_items` custom DB table for quote line items.
- Full i18n (text domain: `askquote-for-woocommerce`).
- HPOS compatibility declaration.
- Comprehensive action and filter hook registry.
- GitHub Actions: Plugin Check, PHPCS lint, PHPUnit matrix, WP.org deploy.
- PHPUnit test suite (CPT, cart, REST API, emails).
