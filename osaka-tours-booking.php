<?php
/**
 * Plugin Name: Osaka Tours Booking
 * Description: Internal tour and booking management dashboard.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('OSAKA_TOURS_VERSION', '1.0.0');
define('OSAKA_TOURS_PATH', plugin_dir_path(__FILE__));
define('OSAKA_TOURS_URL', plugin_dir_url(__FILE__));

require_once OSAKA_TOURS_PATH . 'includes/admin/class-tours-page.php';

add_action('admin_menu', function () {
    Osaka_Tours_Page::register_menu();
});

add_action('admin_enqueue_scripts', function ($hook) {
    if ($hook !== 'toplevel_page_osaka-tours') {
        return;
    }

    wp_enqueue_style(
        'osaka-tours-admin',
        OSAKA_TOURS_URL . 'assets/css/admin.css',
        [],
        OSAKA_TOURS_VERSION
    );
});