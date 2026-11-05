<?php

if (!defined('ABSPATH')) {
    exit;
}

class Kansai_Tours_Bookings_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }

        global $wpdb;
        $per_page = 50;
        $current_page = isset($_GET['paged']) ? max(1, absint($_GET['paged'])) : 1;
        $total_bookings = (int) $wpdb->get_var('SELECT COUNT(*) FROM bookings');
        $total_pages = max(1, (int) ceil($total_bookings / $per_page));
        $current_page = min($current_page, $total_pages);
        $offset = ($current_page - 1) * $per_page;

        $bookings = $wpdb->get_results($wpdb->prepare("
            SELECT
                b.id,
                b.customer_name,
                b.customer_email,
                b.customer_whatsapp,
                b.special_requests,
                b.guests,
                b.amount_paid,
                b.currency,
                b.payment_status,
                b.booking_status,
                b.created_at,
                t.name AS tour_name,
                ts.starts_at
            FROM bookings b
            LEFT JOIN tour_slots ts ON ts.id = b.tour_slot_id
            LEFT JOIN tours t ON t.id = ts.tour_id
            ORDER BY b.created_at DESC, b.id DESC
            LIMIT %d OFFSET %d
        ", $per_page, $offset));
        ?>
        <div class="wrap kansai-tours-admin">
            <h1>Bookings</h1>
            <p class="description">Recent reservations, guest details, and payment status.</p>

            <?php if ($wpdb->last_error): ?>
                <div class="notice notice-error">
                    <p>Unable to load bookings. Please check that the booking database migrations have been applied.</p>
                </div>
            <?php elseif (empty($bookings)): ?>
                <div class="notice notice-info">
                    <p>No bookings found.</p>
                </div>
            <?php else: ?>
                <table class="wp-list-table widefat fixed striped">
                    <thead>
                        <tr>
                            <th>ID</th>
                            <th>Guest</th>
                            <th>Tour / Date</th>
                            <th>Guests</th>
                            <th>Amount</th>
                            <th>Booking status</th>
                            <th>Payment status</th>
                            <th>Special requests</th>
                            <th>Created</th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($bookings as $booking): ?>
                            <tr>
                                <td><?php echo esc_html($booking->id); ?></td>
                                <td>
                                    <strong><?php echo esc_html($booking->customer_name); ?></strong><br>
                                    <a href="mailto:<?php echo esc_attr($booking->customer_email); ?>"><?php echo esc_html($booking->customer_email); ?></a>
                                    <?php if (!empty($booking->customer_whatsapp)): ?>
                                        <br><?php echo esc_html($booking->customer_whatsapp); ?>
                                    <?php endif; ?>
                                </td>
                                <td>
                                    <?php echo esc_html($booking->tour_name ?: 'Tour unavailable'); ?><br>
                                    <?php if ($booking->starts_at): ?>
                                        <?php echo esc_html(wp_date(get_option('date_format'), strtotime($booking->starts_at))); ?>
                                    <?php else: ?>
                                        <span class="description">Date unavailable</span>
                                    <?php endif; ?>
                                </td>
                                <td><?php echo esc_html($booking->guests); ?></td>
                                <td><?php echo esc_html($booking->currency . ' ' . number_format_i18n($booking->amount_paid)); ?></td>
                                <td class="booking_tag booking_<?php print $booking->booking_status; ?>"><?php echo esc_html(ucfirst($booking->booking_status)); ?></td>
                                <td><?php echo esc_html(ucfirst($booking->payment_status)); ?></td>
                                <td><?php echo !empty($booking->special_requests) ? nl2br(esc_html($booking->special_requests)) : '&mdash;'; ?></td>
                                <td><?php echo $booking->created_at ? esc_html(wp_date(get_option('date_format') . ' ' . get_option('time_format'), strtotime($booking->created_at))) : '&mdash;'; ?></td>
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