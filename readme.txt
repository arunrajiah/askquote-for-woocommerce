=== AskQuote for WooCommerce ===
Contributors: arunrajiah
Tags: woocommerce, quote, request-a-quote, b2b, wholesale
Requires at least: 6.2
Tested up to: 6.5
Stable tag: 0.1.0
Requires PHP: 7.4
WC requires at least: 7.0
WC tested up to: 8.5
License: GPLv2 or later
License URI: https://www.gnu.org/licenses/gpl-2.0.html

Add a request-a-quote workflow to your WooCommerce store. Customers build a quote cart and submit requests that you manage from your dashboard.

== Description ==

**AskQuote for WooCommerce** gives your store a complete quote-request workflow — from the "Request Quote" button on product pages through to admin management and automated email notifications.

**Key Features**

* **Quote Button** — Add a customisable "Request Quote" button to any product. Control visibility across all products, by category, by tag, or per product.
* **Quote Cart** — Customers build a private quote list (stored in the WooCommerce session) without affecting the regular shopping cart.
* **Quote Submission Form** — A flexible shortcode-powered form (`[askquote_form]`) captures customer details alongside the quoted items.
* **Admin Quote Management** — A dedicated admin menu lists all incoming quotes with status filtering, bulk actions, and a full detail view.
* **Custom Quote Statuses** — Pending, Replied, Approved, and Closed statuses keep your workflow organised.
* **Email Notifications** — Three automated emails: customer confirmation on submission, admin alert on new submission, and customer notification on approval. All powered by WooCommerce's transactional email system — fully compatible with your email theme.
* **My Account Integration** — Logged-in customers can view their past quote requests under WooCommerce My Account.
* **REST API** — A full REST API (`askquote/v1/quotes`) for headless and external integrations.
* **Developer-friendly** — Comprehensive action and filter hooks throughout. See `includes/extensibility/class-hook-registry.php` for the full list.
* **HPOS Compatible** — Fully compatible with WooCommerce High-Performance Order Storage.
* **Zero Telemetry** — No analytics, no remote assets, no phoning home.

**Shortcodes**

* `[askquote_button product_id="123"]` — Render the quote button for a specific product anywhere.
* `[askquote_cart]` — Display the current quote cart (ideal for a dedicated "Quote Cart" page).
* `[askquote_form]` — Display the quote submission form.

**Available Hooks (selection)**

* `askquote_quote_button_visible` — Filter: control per-product button visibility.
* `askquote_quote_button_html` — Filter: customise the button HTML.
* `askquote_quote_form_fields` — Filter: add or remove form fields.
* `askquote_quote_data_before_save` — Filter: modify data before a quote is saved.
* `askquote_quote_submitted` — Action: fires after a new quote is created.
* `askquote_quote_status_changed` — Action: fires on every status transition.
* `askquote_email_recipients` — Filter: modify who receives each email type.

== Installation ==

1. Upload the `askquote-for-woocommerce` folder to the `/wp-content/plugins/` directory, or install through the WordPress plugin screen.
2. Activate the plugin through the **Plugins** screen in WordPress.
3. WooCommerce must be installed and active.
4. Navigate to **AskQuote → Settings** to configure the button label, visibility, colours, and email subjects.
5. Create a page for your quote cart and add the `[askquote_cart]` shortcode. Save the page ID in the plugin options (or hard-code via the `askquote_cart_page_id` option).
6. Create a page for the quote form and add the `[askquote_form]` shortcode. Save the page ID in the plugin options (or hard-code via the `askquote_form_page_id` option).
7. Optionally, go to **WooCommerce → Settings → Emails** to fine-tune the AskQuote email templates.

== Frequently Asked Questions ==

= Does this work with variable products? =

Yes. Variation IDs are tracked in the quote cart and stored against each quote item.

= Will this affect my regular WooCommerce cart or checkout? =

No. The quote cart is completely separate and stored in the WooCommerce session independently of the shopping cart.

= Can I add custom fields to the quote form? =

Yes — use the `askquote_quote_form_fields` filter to add, remove, or reorder fields.

= Can customers view their past quotes? =

Yes. Logged-in customers see a "My Quotes" tab in their WooCommerce My Account area.

= Is there a REST API? =

Yes. Routes are available at `/wp-json/askquote/v1/quotes`. See the plugin source for full schema details.

= Can I control which products show the quote button? =

Yes. In **AskQuote → Settings → General** you can choose: all products, by category, by tag, or per-product (using a product-level toggle).

= Where can I find more advanced features? =

Advanced features are available separately at [hub.arunrajiah.com/askquote](https://hub.arunrajiah.com/askquote).

== Screenshots ==

1. Quote button on a product page.
2. Quote cart page with item list.
3. Quote submission form.
4. Admin quote list with status filters.
5. Quote detail / edit screen.

== Changelog ==

= 0.1.0 =
* Initial release.
* Quote button on products with configurable visibility.
* Session-based quote cart.
* Quote submission form with nonce protection.
* Admin quote management with custom statuses.
* Three WooCommerce-styled email notifications.
* My Account quotes endpoint.
* REST API (askquote/v1).
* HPOS compatibility declaration.

== Upgrade Notice ==

= 0.1.0 =
Initial release — no upgrade needed.
