<?php
/**
 * Provide a admin area view for the settings page
 */
$currency = get_option('appointix_currency', '$');
$email_notifications = get_option('appointix_email_notifications', get_option('admin_email'));
?>
<div class="wrap appointix-admin-wrap">
    <header class="appointix-header">
        <h1><?php _e('Settings', 'appointix'); ?></h1>
    </header>

    <div class="appointix-card">
        <h2><?php _e('General Settings', 'appointix'); ?></h2>
        <form id="appointix-settings-form">
            <div class="appointix-form-group">
                <label for="appointix_currency"><?php _e('Currency Symbol', 'appointix'); ?></label>
                <input type="text" id="appointix_currency" name="appointix_currency"
                    value="<?php echo esc_attr($currency); ?>" class="regular-text">
                <p class="description">
                    <?php _e('The symbol to display before prices (e.g. $, €, £).', 'appointix'); ?>
                </p>
            </div>

            <div class="appointix-form-group">
                <label for="appointix_email"><?php _e('Notification Email', 'appointix'); ?></label>
                <input type="email" id="appointix_email" name="appointix_email_notifications"
                    value="<?php echo esc_attr($email_notifications); ?>" class="regular-text">
                <p class="description">
                    <?php _e('Email address to receive new booking notifications.', 'appointix'); ?>
                </p>
            </div>

            <div class="appointix-modal-footer">
                <button type="submit" class="appointix-btn-primary"><?php _e('Save Settings', 'appointix'); ?></button>
            </div>
        </form>
    </div>
</div>