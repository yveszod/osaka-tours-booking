<?php

if (!defined('ABSPATH')) {
    exit;
}

class Osaka_Tours_Page
{
    public static function register_menu(): void
    {
        add_menu_page(
            'Osaka Tours',
            'Osaka Tours',
            'manage_options',
            'osaka-tours',
            [self::class, 'render'],
            'dashicons-palmtree',
            26
        );
    }

    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }

        global $wpdb;

        $tours = $wpdb->get_results("
            SELECT
                id,
                name,
                description,
                duration_minutes,
                price,
                currency,
                active
            FROM tours
            ORDER BY id DESC
        ");

        ?>

        <div class="wrap osaka-tours-admin">

            <h1>Osaka Tours</h1>

            <p class="description">
                Manage and monitor your available tours.
            </p>

            <?php if ($wpdb->last_error): ?>

                <div class="notice notice-error">
                    <p>Unable to load tours. Please check the database connection.</p>
                </div>

            <?php elseif (empty($tours)): ?>

                <div class="notice notice-info">
                    <p>No tours found.</p>
                </div>

            <?php else: ?>

                <table class="wp-list-table widefat fixed striped">

                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tour</th>
                            <th>Description</th>
                            <th>Duration</th>
                            <th>Price</th>
                            <th>Status</th>
                        </tr>
                    </thead>

                    <tbody>

                        <?php foreach ($tours as $tour): ?>

                            <tr>
                                <td><?php echo esc_html($tour->id); ?></td>

                                <td>
                                    <strong>
                                        <?php echo esc_html($tour->name); ?>
                                    </strong>
                                </td>

                                <td>
                                    <?php echo esc_html($tour->description); ?>
                                </td>

                                <td>
                                    <?php
                                    echo esc_html(
                                        number_format_i18n(
                                            $tour->duration_minutes / 60,
                                            1
                                        ) . ' hours'
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php
                                    echo esc_html($tour->currency);
                                    ?>
                                    <?php
                                    echo esc_html(
                                        number_format_i18n($tour->price)
                                    );
                                    ?>
                                </td>

                                <td>
                                    <?php if ($tour->active): ?>
                                        <span class="osaka-status active">
                                            Active
                                        </span>
                                    <?php else: ?>
                                        <span class="osaka-status inactive">
                                            Inactive
                                        </span>
                                    <?php endif; ?>
                                </td>
                            </tr>

                        <?php endforeach; ?>

                    </tbody>

                </table>

            <?php endif; ?>

        </div>

        <?php
    }
}