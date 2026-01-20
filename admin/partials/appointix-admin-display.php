<?php
/**
 * Provide a admin area view for the plugin
 */
$total_apartments = Appointix_Apartments_Model::count_apartments();
$total_bookings = Appointix_Bookings_Model::count_bookings();
$pending_bookings = Appointix_Bookings_Model::count_pending_bookings();
?>

<div class="wrap appointix-admin-wrap">
    <header class="appointix-header">
        <h1><?php _e('Dashboard Overview', 'appointix'); ?></h1>
        <a href="<?php echo admin_url('post-new.php?post_type=appointix_apartment'); ?>"
            class="appointix-btn-primary"><?php _e('Add New Apartment', 'appointix'); ?></a>
    </header>

    <div class="appointix-stats-grid">
        <div class="appointix-stat-card">
            <div class="stat-value"><?php echo esc_html($total_apartments); ?></div>
            <div class="stat-label"><?php _e('Active Apartments', 'appointix'); ?></div>
        </div>
        <div class="appointix-stat-card">
            <div class="stat-value"><?php echo esc_html($total_bookings); ?></div>
            <div class="stat-label"><?php _e('Total Bookings', 'appointix'); ?></div>
        </div>
        <div class="appointix-stat-card">
            <div class="stat-value"><?php echo esc_html($pending_bookings); ?></div>
            <div class="stat-label"><?php _e('Pending Bookings', 'appointix'); ?></div>
        </div>
    </div>

    <div class="appointix-card">
        <h2><?php _e('Quick Start', 'appointix'); ?></h2>
        <p><?php _e('Use the shortcode <code>[appointix_apartments]</code> on any page to display your apartment catalog.', 'appointix'); ?>
        </p>
        <div style="margin-top:20px; display: flex; gap: 10px;">
            <a href="<?php echo admin_url('edit.php?post_type=appointix_apartment'); ?>"
                class="button button-primary"><?php _e('Manage Apartments', 'appointix'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=appointix-bookings'); ?>"
                class="button button-secondary"><?php _e('View All Bookings', 'appointix'); ?></a>
            <a href="<?php echo admin_url('admin.php?page=appointix-settings'); ?>"
                class="button button-secondary"><?php _e('Configure Settings', 'appointix'); ?></a>
        </div>
    </div>

    <div class="appointix-grid-two-col"
        style="display:grid; grid-template-columns: 1fr 1fr; gap: 20px; margin-top:20px;">
        <div class="appointix-card">
            <h2><?php _e('Recent Apartments', 'appointix'); ?></h2>
            <?php
            $recent_apts = Appointix_Apartments_Model::get_latest_apartments(5);
            if (!empty($recent_apts)): ?>
                <table class="wp-list-table widefat fixed striped" style="border:none; box-shadow:none;">
                    <thead>
                        <tr>
                            <th><?php _e('Name', 'appointix'); ?></th>
                            <th><?php _e('Price', 'appointix'); ?></th>
                        </tr>
                    </thead>
                    <tbody>
                        <?php foreach ($recent_apts as $apt): ?>
                            <tr>
                                <td>
                                    <a
                                        href="<?php echo get_edit_post_link($apt->id); ?>"><strong><?php echo esc_html($apt->name); ?></strong></a>
                                </td>
                                <td><?php echo get_option('appointix_currency', '$') . number_format($apt->price_per_night, 0); ?>
                                </td>
                            </tr>
                        <?php endforeach; ?>
                    </tbody>
                </table>
            <?php else: ?>
                <p><?php _e('No apartments found.', 'appointix'); ?></p>
            <?php endif; ?>
        </div>

        <div class="appointix-card">
            <h2><?php _e('Need Help?', 'appointix'); ?></h2>
            <p><?php _e('If you have any questions or need assistance setting up your booking system, please refer to our documentation or contact support.', 'appointix'); ?>
            </p>
            <ul style="list-style: disc; padding-left: 20px; margin-top: 15px;">
                <li><a href="#"><?php _e('Plugin Documentation', 'appointix'); ?></a></li>
                <li><a href="#"><?php _e('Video Tutorials', 'appointix'); ?></a></li>
                <li><a href="#"><?php _e('Support Forum', 'appointix'); ?></a></li>
            </ul>
        </div>
    </div>
</div>