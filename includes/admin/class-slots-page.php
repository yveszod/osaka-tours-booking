<?php

if (!defined('ABSPATH')) {
    exit;
}

class Kansai_Tours_Slots_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }

        global $wpdb;
        $per_page = 50;
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $total_slots = (int) $wpdb->get_var('SELECT COUNT(*) FROM tour_slots');
        $total_pages = max(1, (int) ceil($total_slots / $per_page));
        $current_page = min($current_page, $total_pages);
        $offset = ($current_page - 1) * $per_page;

        $slots = $wpdb->get_results($wpdb->prepare("
            SELECT
                ts.id,
                ts.starts_at,
                ts.capacity,
                ts.meeting_point,
                ts.meeting_time,
                ts.dropoff_point,
                t.name AS tour_name,
                COALESCE(booking_totals.confirmed_guests, 0) AS confirmed_guests
            FROM tour_slots ts
            LEFT JOIN tours t ON t.id = ts.tour_id
            LEFT JOIN (
                SELECT
                    tour_slot_id,
                    SUM(CASE WHEN booking_status = 'confirmed' THEN guests ELSE 0 END) AS confirmed_guests
                FROM bookings
                GROUP BY tour_slot_id
            ) booking_totals ON booking_totals.tour_slot_id = ts.id
            ORDER BY ts.starts_at DESC, ts.id DESC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));
        ?>
        <div class="wrap kansai-tours-admin">
            <h1>Tour Slots</h1>
            <p class="description">Scheduled tour dates, capacity, and current confirmed availability.</p>

            <?php if ($wpdb->last_error): ?>
                <div class="notice notice-error">
                    <p>Unable to load tour slots. Please check that the tours and bookings tables are available.</p>
                </div>
            <?php elseif (empty($slots)): ?>
                <div class="notice notice-info">
                    <p>No tour slots found.</p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Tour</th>
                            <th>Date &amp; time</th>
                            <th>Capacity</th>
                            <th>Confirmed</th>
                            <th>Available</th>
                            <th>Meeting point</th>
                            <th>Drop-off point</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($slots as $slot): ?>
                            <?php $available = max(0, (int) $slot->capacity - (int) $slot->confirmed_guests); ?>
                            <tr>
                                <td><?php echo esc_html($slot->id); ?></td>
                                <td><strong><?php echo esc_html($slot->tour_name ?: 'Tour unavailable'); ?></strong></td>
                                <td>
                                    <?php echo esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($slot->starts_at))); ?>
                                    <?php if ($slot->meeting_time): ?>
                                        <br><span class="description">Meet: <?php echo esc_html(substr($slot->meeting_time, 0, 5)); ?></span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($slot->capacity); ?></td>
                                <td><?php echo esc_html($slot->confirmed_guests); ?></td>
                                <td>
                                    <?php if ($available > 0): ?>
                                        <span class="kansai-status active"><?php echo esc_html($available); ?> available</span>
                                    <?php else: ?>
                                        <span class="kansai-status inactive">Full</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo $slot->meeting_point ? esc_html($slot->meeting_point) : '&mdash;'; ?></td>
                                <td><?php echo $slot->dropoff_point ? esc_html($slot->dropoff_point) : '&mdash;'; ?></td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>

                <?php if ($total_pages > 1): ?>
                    <div class="tablenav">
                        <div class="tablenav-pages">
                            <?php
                            echo wp_kses_post(paginate_links([
                                'base' => add_query_arg('paged', '%#%'),
                                'format' => '',
                                'current' => $current_page,
                                'total' => $total_pages,
                                'prev_text' => '&laquo;',
                                'next_text' => '&raquo;',
                            ]));
                            ?>
                        </div>
                    </div>
                <?php endif; ?>
            <?php endif; ?>
        </div>
        <?php
    }
}