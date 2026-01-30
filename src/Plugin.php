<?php

declare(strict_types=1);

namespace WCFN;

use WCFN\Admin\SettingsPage;
use WCFN\Normalizer\FieldNormalizer;
use WCFN\WooCommerce\ProductSaveListener;

final class Plugin
{
    public static function init(): void
    {
        if (!class_exists('WooCommerce')) {
            return;
        }

        $normalizer = new FieldNormalizer();

        new SettingsPage();
        new ProductSaveListener($normalizer, wc_get_logger());
    }
}
