<?php

declare(strict_types=1);

namespace WCFN\Admin;

final class SettingsPage
{
    private const OPTION_FIELDS = 'wcfn_core_fields';
    private const OPTION_META = 'wcfn_meta_fields';

    private const CORE_FIELDS = [
        'post_title' => 'Product title',
        'post_content' => 'Description',
        'post_excerpt' => 'Short description',
    ];

    public function __construct()
    {
        add_action('admin_menu', [$this, 'registerPage']);
        add_action('admin_init', [$this, 'registerSettings']);
        add_action('admin_enqueue_scripts', [$this, 'enqueueScripts']);
        add_action('wp_ajax_wcfn_get_meta_keys', [$this, 'ajaxGetMetaKeys']);
    }

    /**
     * Returns all selected fields (core + meta).
     */
    public static function getFields(): array
    {
        $core = (array) get_option(self::OPTION_FIELDS, []);
        $metaRaw = (string) get_option(self::OPTION_META, '');

        $meta = array_filter(
            array_map('trim', explode("\n", $metaRaw))
        );

        return array_merge($core, $meta);
    }

    public function registerPage(): void
    {
        add_submenu_page(
            'woocommerce',
            'Field Normalizer',
            'Field Normalizer',
            'manage_woocommerce',
            'wc-field-normalizer',
            [$this, 'render']
        );
    }

    public function registerSettings(): void
    {
        register_setting('wcfn', self::OPTION_FIELDS, [
            'type' => 'array',
            'sanitize_callback' => [$this, 'sanitizeCoreFields'],
        ]);

        register_setting('wcfn', self::OPTION_META, [
            'type' => 'string',
            'sanitize_callback' => 'sanitize_textarea_field',
        ]);
    }

    public function sanitizeCoreFields(mixed $input): array
    {
        if (!is_array($input)) {
            return [];
        }

        return array_values(
            array_intersect($input, array_keys(self::CORE_FIELDS))
        );
    }

    public function enqueueScripts(string $hook): void
    {
        if ($hook !== 'woocommerce_page_wc-field-normalizer') {
            return;
        }

        wp_enqueue_script(
            'wcfn-admin',
            plugins_url('assets/wc-field-normalizer-admin.js', dirname(__DIR__)),
            [],
            '1.0.0',
            true
        );

        wp_localize_script('wcfn-admin', 'wcfnConfig', [
            'ajaxUrl' => admin_url('admin-ajax.php'),
            'nonce' => wp_create_nonce('wcfn_meta_nonce'),
        ]);
    }

    public function ajaxGetMetaKeys(): void
    {
        check_ajax_referer('wcfn_meta_nonce', 'nonce');

        global $wpdb;

        if (!current_user_can('manage_woocommerce')) {
            wp_send_json_error('Unauthorized');
        }

        // Fetch distinct meta keys from product and variation posts only
        // Limit to 50 to avoid performance issues
        $results = $wpdb->get_col("
            SELECT DISTINCT pm.meta_key
            FROM {$wpdb->postmeta} pm
            INNER JOIN {$wpdb->posts} p ON pm.post_id = p.ID
            WHERE p.post_type IN ('product', 'product_variation')
            AND pm.meta_key NOT LIKE '\_%'
            ORDER BY pm.meta_key ASC
            LIMIT 50
        ");

        wp_send_json_success($results);
    }

    public function render(): void
    {
        $selected = (array) get_option(self::OPTION_FIELDS, []);
        ?>
                <div class="wrap">
                    <h1>WooCommerce Field Normalizer</h1>

                    <form method="post" action="options.php">
                        <?php settings_fields('wcfn'); ?>

                        <h2>Normalize core fields</h2>

                        <?php foreach (self::CORE_FIELDS as $key => $label): ?>
                                <label style="display:block;margin-bottom:6px;">
                                    <input type="checkbox" name="<?php echo esc_attr(self::OPTION_FIELDS); ?>[]"
                                        value="<?php echo esc_attr($key); ?>" <?php checked(in_array($key, $selected, true)); ?>>
                                    <?php echo esc_html($label); ?>
                                </label>
                        <?php endforeach; ?>

                        <h2 style="margin-top:24px;">Normalize meta fields (optional)</h2>
                        <p>Enter one meta key per line.</p>

                        <div id="wcfn-meta-suggestions" style="margin-bottom: 10px;">
                            <!-- JS will populate this -->
                        </div>

                        <textarea id="wcfn-meta-textarea" name="<?php echo esc_attr(self::OPTION_META); ?>" rows="6"
                            cols="50"><?php echo esc_textarea((string) get_option(self::OPTION_META)); ?></textarea>

                        <?php submit_button(); ?>
                    </form>
                </div>
                <?php
    }
}
