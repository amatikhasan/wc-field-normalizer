<?php
/**
 * Plugin Name: WooCommerce Field Normalizer
 * Description: Normalizes selected WooCommerce product fields on save.
 * Version: 1.0.0
 * Author: Atik Hasan
 * Requires PHP: 8.1
 */

declare(strict_types=1);

defined('ABSPATH') || exit;

spl_autoload_register(static function (string $class): void {
    $prefix = 'WCFN\\';
    $baseDir = __DIR__ . '/src/';

    if (!str_starts_with($class, $prefix)) {
        return;
    }

    $relativeClass = substr($class, strlen($prefix));
    $file = $baseDir . str_replace('\\', '/', $relativeClass) . '.php';

    if (file_exists($file)) {
        require $file;
    }
});

add_action('plugins_loaded', static function (): void {
    \WCFN\Plugin::init();
});
