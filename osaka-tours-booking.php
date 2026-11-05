<?php
/**
 * Plugin Name: Kansai Tours Booking
 * Description: Internal tour and booking management dashboard.
 * Version: 1.0.0
 */

if (!defined('ABSPATH')) {
    exit;
}

define('KANSAI_TOURS_VERSION', '1.0.0');
define('KANSAI_TOURS_PATH', plugin_dir_path(__FILE__));
define('KANSAI_TOURS_URL', plugin_dir_url(__FILE__));

require_once KANSAI_TOURS_PATH . 'includes/admin/class-dashboard-page.php';
require_once KANSAI_TOURS_PATH . 'includes/admin/class-tours-page.php';
require_once KANSAI_TOURS_PATH . 'includes/admin/class-bookings-page.php';

add_action('admin_menu', function () {
    Kansai_Tours_Page::register_menu();
});

add_action('admin_enqueue_scripts', function ($hook) {
    if (!in_array($hook, [
        'toplevel_page_kansai-tours',
        'kansai-tours_page_kansai-tours-tours',
        'kansai-tours_page_kansai-tours-bookings'
    ], true)) {
        return;
    }

    wp_enqueue_style(
        'kansai-tours-admin',
        KANSAI_TOURS_URL . 'assets/css/admin.css',
        [],
        KANSAI_TOURS_VERSION
    );
});