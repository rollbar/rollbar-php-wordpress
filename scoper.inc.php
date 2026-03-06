<?php

declare(strict_types=1);

require_once './utils/NamespaceTokenFixer.php';

$finder = Isolated\Symfony\Component\Finder\Finder::class;
$fixer = new Rollbar\WordPress\BuildUtils\NamespaceTokenFixer();

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
        static function (string $filePath, string $prefix, string $content) use ($fixer): string {
            if ($fixer->fileIsFixable($filePath)) {
                $fixer->configure($filePath, $content);
                $content = $fixer->fix();
            }

            return $content;
        },
    ],
];
