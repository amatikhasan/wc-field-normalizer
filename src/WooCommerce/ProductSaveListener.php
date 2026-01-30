<?php

declare(strict_types=1);

namespace WCFN\WooCommerce;

use WC_Logger_Interface;
use WC_Product;
use WCFN\Admin\SettingsPage;
use WCFN\Normalizer\FieldNormalizer;

final class ProductSaveListener
{
    public function __construct(
        private readonly FieldNormalizer $normalizer,
        private readonly WC_Logger_Interface $logger
    ) {
        add_action(
            'woocommerce_before_product_object_save',
            [$this, 'onProductSave']
        );
    }

    public function onProductSave(WC_Product $product): void
    {
        $fields = SettingsPage::getFields();

        if (empty($fields)) {
            return;
        }

        foreach ($fields as $field) {
            $this->normalizeField($product, $field);
        }
    }

    private function normalizeField(WC_Product $product, string $field): void
    {
        $original = '';
        $normalized = '';

        switch ($field) {
            case 'post_title':
                $original = $product->get_name();
                $normalized = $this->normalizer->normalize($original);
                if ($original !== $normalized) {
                    $product->set_name($normalized);
                }
                break;

            case 'post_content':
                $original = $product->get_description();
                $normalized = $this->normalizer->normalize($original);
                if ($original !== $normalized) {
                    $product->set_description($normalized);
                }
                break;

            case 'post_excerpt':
                $original = $product->get_short_description();
                $normalized = $this->normalizer->normalize($original);
                if ($original !== $normalized) {
                    $product->set_short_description($normalized);
                }
                break;

            default:
                $value = $product->get_meta($field);
                if (is_string($value)) {
                    $original = $value;
                    $normalized = $this->normalizer->normalize($original);
                    if ($original !== $normalized) {
                        $product->update_meta_data($field, $normalized);
                    }
                }
        }

        if ($original !== $normalized && $normalized !== '') {
            $this->logger->info(
                sprintf(
                    'Normalized field "%s" for product %d. Old: "%s", New: "%s"',
                    $field,
                    $product->get_id(),
                    substr($original, 0, 50),
                    substr($normalized, 0, 50)
                ),
                ['source' => 'wc-field-normalizer']
            );
        }
    }
}
