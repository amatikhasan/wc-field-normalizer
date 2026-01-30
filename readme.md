# WooCommerce Field Normalizer

Normalizes selected WooCommerce product fields during save to ensure consistent data quality.

## Key Features

- **Core and meta Fields Normalization**: Trims whitespace, reduces multiple spaces to single spaces, and normalizes line breaks for `post_title`, `post_content`, `post_excerpt`, and other configured fields.
- **Admin Configuration**: Provides a simple UI to select core and meta fields to normalize and a toggle to enable logging.
- **Logging**: Integration with WooCommerce logger to record normalized fields per product for audit purposes.
- **Testing**: Includes a PHPUnit test suite to verify normalization logic (whitespace trimming, line break standardization).
- **Safe and Extensible**: Uses strict types, namespaces, and object-oriented design with a reusable `FieldNormalizer` class.

## Technical Approach

- **Hooks**: Uses `woocommerce_before_product_object_save` to intercept product saves, ensuring compatibility with both simple and variable products.
- **Normalization**: Implemented in a single reusable `FieldNormalizer` class.
- **Settings**: Core fields presented as checkboxes to prevent typos; meta fields can be entered manually.
- **Autoloading**: Lightweight PSR-4–style autoloader loads plugin classes without external dependencies.

## How to Test

I added a suite of unit tests to make sure the normalizer doesn't break in the future.

1. **Install dependencies**: Run `composer install` (only dev dependencies needed).
2. **Run tests**: Run `vendor/bin/phpunit`.
