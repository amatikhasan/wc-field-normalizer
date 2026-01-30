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

                <textarea name="<?php echo esc_attr(self::OPTION_META); ?>" rows="6"
                    cols="50"><?php echo esc_textarea((string) get_option(self::OPTION_META)); ?></textarea>

                <?php submit_button(); ?>
            </form>
        </div>
        <?php
    }
}
