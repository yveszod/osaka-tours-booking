<?php

if (!defined('ABSPATH')) {
    exit;
}

class Kansai_Tours_Edit_Tour_Page
{
    public static function render(): void
    {
        if (!current_user_can('manage_options')) {
            wp_die('You do not have permission to access this page.');
        }

        global $wpdb;
        $tour_id = isset($_GET['tour_id']) ? absint($_GET['tour_id']) : 0;
        if (!$tour_id) {
            wp_die('A valid tour ID is required.');
        }

        $tour = $wpdb->get_row($wpdb->prepare('SELECT * FROM tours WHERE id = %d', $tour_id));
        if (!$tour) {
            wp_die('Tour not found.');
        }

        $errors = [];
        if ($_SERVER['REQUEST_METHOD'] === 'POST') {
            check_admin_referer('kansai_tours_update_tour_' . $tour_id);

            $name = sanitize_text_field(wp_unslash($_POST['name'] ?? ''));
            $description = sanitize_textarea_field(wp_unslash($_POST['description'] ?? ''));
            $long_description = sanitize_textarea_field(wp_unslash($_POST['long_description'] ?? ''));
            $image_url = esc_url_raw(wp_unslash($_POST['image_url'] ?? ''));
            $vehicle_type = sanitize_text_field(wp_unslash($_POST['vehicle_type'] ?? ''));
            $guide_language = sanitize_text_field(wp_unslash($_POST['guide_language'] ?? ''));
            $duration_input = trim((string) wp_unslash($_POST['duration_minutes'] ?? ''));
            $price_input = trim((string) wp_unslash($_POST['price'] ?? ''));
            $currency = strtoupper(sanitize_text_field(wp_unslash($_POST['currency'] ?? '')));
            $paypal_payment_link = esc_url_raw(wp_unslash($_POST['paypal_payment_link'] ?? ''));

            if ($name === '' || strlen($name) > 255) {
                $errors[] = 'Tour name is required and must be 255 characters or fewer.';
            }
            if ($vehicle_type !== '' && strlen($vehicle_type) > 100) {
                $errors[] = 'Vehicle type must be 100 characters or fewer.';
            }
            if ($guide_language !== '' && strlen($guide_language) > 100) {
                $errors[] = 'Guide language must be 100 characters or fewer.';
            }
            if ($image_url !== '' && strlen($image_url) > 500) {
                $errors[] = 'Image URL must be 500 characters or fewer.';
            }
            if ($paypal_payment_link !== '' && strlen($paypal_payment_link) > 500) {
                $errors[] = 'Checkout link must be 500 characters or fewer.';
            }
            if (!ctype_digit($price_input) || (int) $price_input > 4294967295) {
                $errors[] = 'Price must be a non-negative whole number.';
            }
            if ($duration_input !== '' && (!ctype_digit($duration_input) || (int) $duration_input > 4294967295)) {
                $errors[] = 'Duration must be a non-negative whole number of minutes.';
            }
            if (!preg_match('/^[A-Z]{3}$/', $currency)) {
                $errors[] = 'Currency must be a three-letter code, such as JPY.';
            }

            if (!$errors) {
                $updated = $wpdb->update(
                    'tours',
                    [
                        'name' => $name,
                        'description' => $description !== '' ? $description : null,
                        'long_description' => $long_description !== '' ? $long_description : null,
                        'image_url' => $image_url !== '' ? $image_url : null,
                        'vehicle_type' => $vehicle_type !== '' ? $vehicle_type : null,
                        'guide_language' => $guide_language !== '' ? $guide_language : null,
                        'duration_minutes' => $duration_input !== '' ? (int) $duration_input : null,
                        'price' => (int) $price_input,
                        'currency' => $currency,
                        'paypal_payment_link' => $paypal_payment_link !== '' ? $paypal_payment_link : null,
                        'active' => isset($_POST['active']) ? 1 : 0,
                    ],
                    ['id' => $tour_id],
                    ['%s', '%s', '%s', '%s', '%s', '%s', '%d', '%d', '%s', '%s', '%d'],
                    ['%d']
                );

                if ($updated === false) {
                    $errors[] = 'Unable to save the tour. Please try again.';
                } else {
                    wp_safe_redirect(add_query_arg([
                        'page' => 'kansai-tours-edit-tour',
                        'tour_id' => $tour_id,
                        'updated' => '1',
                    ], admin_url('admin.php')));
                    exit;
                }
            }

            foreach ([
                'name', 'description', 'long_description', 'image_url', 'vehicle_type',
                'guide_language', 'duration_minutes', 'price', 'currency', 'paypal_payment_link',
            ] as $field) {
                $tour->$field = wp_unslash($_POST[$field] ?? '');
            }
            $tour->active = isset($_POST['active']) ? 1 : 0;
        }
        ?>
        <div class="wrap kansai-tours-admin">
            <h1>Edit Tour</h1>
            <p class="description"><a href="<?php echo esc_url(admin_url('admin.php?page=kansai-tours-tours')); ?>">&larr; Back to tours</a></p>

            <?php if (isset($_GET['updated'])): ?>
                <div class="notice notice-success is-dismissible"><p>Tour updated.</p></div>
            <?php endif; ?>

            <?php foreach ($errors as $error): ?>
                <div class="notice notice-error"><p><?php echo esc_html($error); ?></p></div>
            <?php endforeach; ?>

            <form method="post" action="<?php echo esc_url(add_query_arg(['page' => 'kansai-tours-edit-tour', 'tour_id' => $tour_id], admin_url('admin.php'))); ?>" class="kansai-tour-edit-form">
                <?php wp_nonce_field('kansai_tours_update_tour_' . $tour_id); ?>
                <table class="form-table" role="presentation">
                    <tbody>
                        <tr>
                            <th scope="row"><label for="tour-name">Name</label></th>
                            <td><input id="tour-name" name="name" type="text" class="regular-text" maxlength="255" required value="<?php echo esc_attr($tour->name); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-description">Short description</label></th>
                            <td><textarea id="tour-description" name="description" class="large-text" rows="3"><?php echo esc_textarea($tour->description); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-long-description">Long description</label></th>
                            <td><textarea id="tour-long-description" name="long_description" class="large-text" rows="6"><?php echo esc_textarea($tour->long_description); ?></textarea></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-image-url">Image URL</label></th>
                            <td><input id="tour-image-url" name="image_url" type="url" class="large-text" maxlength="500" value="<?php echo esc_attr($tour->image_url); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-vehicle-type">Vehicle type</label></th>
                            <td><input id="tour-vehicle-type" name="vehicle_type" type="text" class="regular-text" maxlength="100" value="<?php echo esc_attr($tour->vehicle_type); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-guide-language">Guide language</label></th>
                            <td><input id="tour-guide-language" name="guide_language" type="text" class="regular-text" maxlength="100" value="<?php echo esc_attr($tour->guide_language); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-duration">Duration (minutes)</label></th>
                            <td><input id="tour-duration" name="duration_minutes" type="number" min="0" step="1" value="<?php echo esc_attr($tour->duration_minutes); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-price">Price</label></th>
                            <td><input id="tour-price" name="price" type="number" min="0" step="1" required value="<?php echo esc_attr($tour->price); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-currency">Currency</label></th>
                            <td><input id="tour-currency" name="currency" type="text" maxlength="3" minlength="3" class="small-text" required value="<?php echo esc_attr($tour->currency); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row"><label for="tour-checkout-link">Checkout link</label></th>
                            <td><input id="tour-checkout-link" name="paypal_payment_link" type="url" class="large-text" maxlength="500" value="<?php echo esc_attr($tour->paypal_payment_link); ?>"></td>
                        </tr>
                        <tr>
                            <th scope="row">Status</th>
                            <td><label><input name="active" type="checkbox" value="1" <?php checked((int) $tour->active, 1); ?>> Active</label></td>
                        </tr>
                    </tbody>
                </table>
                <?php submit_button('Save Tour'); ?>
            </form>
        </div>
        <?php
    }
}
