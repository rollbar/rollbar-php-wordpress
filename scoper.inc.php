<?php

declare(strict_types=1);

$finder = Isolated\Symfony\Component\Finder\Finder::class;

return [
    'prefix' => 'RollbarWP',
    'output-dir' => __DIR__ . '/dist',
    'finders' => [
        $finder::create()->files()->in('build'),
    ],
    'exclude-namespaces' => [
        'Rollbar',
    ],
    'exclude-classes' => [
        'WP_REST_Request',
        'WP_REST_Response',
        'WP_REST_Server',
        'WP_HTTP_Proxy',
    ],
    'exclude-functions' => [
        'add_action',
        'add_filter',
        'add_settings_field',
        'add_settings_section',
        'admin_url',
        'checked',
        'disabled',
        'do_settings_sections',
        'esc_attr',
        'esc_html',
        'esc_url',
        'get_admin_url',
        'plugin_dir_url',
        'register_setting',
        'selected',
        'settings_fields',
        'submit_button',
        'wp_enqueue_script',
        'wp_enqueue_script',
        'wp_enqueue_style',
        'wp_localize_script',
        'wp_localize_script',
        'wp_nonce_field',
        'zend_monitor_custom_event',
    ],
    'patchers' => [
        static function (string $filePath, string $prefix, string $content): string {
            // Fix ClassLoader in string not being prefixed.
            if ($filePath === __DIR__ . '/build/vendor/composer/autoload_real.php') {
                $content = str_replace(
                    '(\'Composer\\\\Autoload\\\\ClassLoader\' === $class)',
                    '(\'RollbarWP\Composer\Autoload\ClassLoader\' === $class)',
                    $content,
                );
            }

            return $content;
        },
    ],
];
