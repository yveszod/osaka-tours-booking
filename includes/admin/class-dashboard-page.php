<?php

if (!defined('ABSPATH')) {
    exit;
}

class Kansai_Tours_Dashboard_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }
        ?>
        <div class="wrap kansai-tours-admin">
            <h1>Kansai Tours</h1>
            <p class="description">Welcome to the Kansai Tours management dashboard.</p>

            <div class="kansai-dashboard-card">
                <h2>Tour Management</h2>
                <p>View your tours, check their details, and monitor whether they are active.</p>
                <a class="button button-primary" href="<?php echo esc_url(admin_url('admin.php?page=kansai-tours-tours')); ?>">
                    Manage Tours
                </a>
            </div>
        </div>
        <?php
    }
}
