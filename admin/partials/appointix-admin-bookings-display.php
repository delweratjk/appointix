<?php
/**
 * Admin area view for the bookings page - Premium Design
 */
$bookings = Appointix_Bookings_Model::get_bookings();
$currency = get_option('appointix_currency', '$');

// Count stats
$total = count($bookings);
$pending = 0;
$confirmed = 0;
$completed = 0;

foreach ($bookings as $b) {
    if ($b->status === 'pending')
        $pending++;
    elseif ($b->status === 'confirmed')
        $confirmed++;
    elseif ($b->status === 'completed')
        $completed++;
}
?>

<style>
    .aptx-bookings-wrap {
        font-family: -apple-system, BlinkMacSystemFont, 'Segoe UI', Roboto, sans-serif;
        margin: 20px 20px 20px 0;
    }

    .aptx-bookings-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        margin-bottom: 30px;
    }

    .aptx-bookings-header h1 {
        font-size: 28px;
        font-weight: 700;
        margin: 0;
        color: #1e293b;
    }

    /* Stats Cards */
    .aptx-stats-grid {
        display: grid;
        grid-template-columns: repeat(4, 1fr);
        gap: 20px;
        margin-bottom: 30px;
    }

    .aptx-stat-card {
        background: #fff;
        border-radius: 16px;
        padding: 24px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        border-left: 4px solid #e2e8f0;
    }

    .aptx-stat-card.total {
        border-left-color: #6366f1;
    }

    .aptx-stat-card.pending {
        border-left-color: #f59e0b;
    }

    .aptx-stat-card.confirmed {
        border-left-color: #10b981;
    }

    .aptx-stat-card.completed {
        border-left-color: #0ea5e9;
    }

    .aptx-stat-value {
        font-size: 32px;
        font-weight: 700;
        color: #1e293b;
        line-height: 1;
    }

    .aptx-stat-label {
        font-size: 14px;
        color: #64748b;
        margin-top: 8px;
    }

    /* Bookings Table */
    .aptx-bookings-card {
        background: #fff;
        border-radius: 16px;
        box-shadow: 0 1px 3px rgba(0, 0, 0, 0.08);
        overflow: hidden;
    }

    .aptx-card-header {
        display: flex;
        align-items: center;
        justify-content: space-between;
        padding: 20px 24px;
        border-bottom: 1px solid #e2e8f0;
    }

    .aptx-card-header h2 {
        font-size: 18px;
        font-weight: 600;
        margin: 0;
        color: #1e293b;
    }

    .aptx-bookings-table {
        width: 100%;
        border-collapse: collapse;
    }

    .aptx-bookings-table th {
        background: #f8fafc;
        padding: 14px 20px;
        text-align: left;
        font-weight: 600;
        font-size: 12px;
        text-transform: uppercase;
        letter-spacing: 0.5px;
        color: #64748b;
        border-bottom: 1px solid #e2e8f0;
    }

    .aptx-bookings-table td {
        padding: 18px 20px;
        border-bottom: 1px solid #f1f5f9;
        vertical-align: top;
    }

    .aptx-bookings-table tr:hover {
        background: #f8fafc;
    }

    /* Customer Cell */
    .aptx-customer-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .aptx-customer-email {
        font-size: 13px;
        color: #6366f1;
        margin-bottom: 2px;
    }

    .aptx-customer-phone {
        font-size: 13px;
        color: #64748b;
    }

    /* Apartment Cell */
    .aptx-apartment-name {
        font-weight: 600;
        color: #1e293b;
        margin-bottom: 4px;
    }

    .aptx-apartment-type {
        display: inline-block;
        padding: 4px 10px;
        background: #e0e7ff;
        color: #4338ca;
        border-radius: 12px;
        font-size: 11px;
        font-weight: 600;
        text-transform: uppercase;
    }

    /* Dates Cell */
    .aptx-dates-wrapper {
        display: flex;
        flex-direction: column;
        gap: 6px;
    }

    .aptx-date-row {
        display: flex;
        align-items: center;
        gap: 8px;
    }

    .aptx-date-label {
        font-size: 11px;
        font-weight: 600;
        color: #64748b;
        text-transform: uppercase;
        width: 70px;
    }

    .aptx-date-value {
        font-weight: 500;
        color: #1e293b;
    }

    .aptx-nights-badge {
        display: inline-block;
        padding: 4px 10px;
        background: #dbeafe;
        color: #1d4ed8;
        border-radius: 12px;
        font-size: 12px;
        font-weight: 600;
        margin-top: 4px;
    }

    /* Price Cell */
    .aptx-price {
        font-size: 18px;
        font-weight: 700;
        color: #10b981;
    }

    /* Status Select */
    .aptx-status-select {
        padding: 8px 12px;
        border: 2px solid #e2e8f0;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        min-width: 120px;
        background: #fff;
    }

    .aptx-status-select:focus {
        outline: none;
        border-color: #6366f1;
    }

    /* Status Badge Alt */
    .aptx-status-badge {
        display: inline-block;
        padding: 6px 14px;
        border-radius: 20px;
        font-size: 12px;
        font-weight: 600;
    }

    .aptx-status-pending {
        background: #fef3c7;
        color: #92400e;
    }

    .aptx-status-confirmed {
        background: #d1fae5;
        color: #065f46;
    }

    .aptx-status-cancelled {
        background: #fee2e2;
        color: #991b1b;
    }

    .aptx-status-completed {
        background: #dbeafe;
        color: #1e40af;
    }

    /* Actions */
    .aptx-action-btn {
        padding: 8px 14px;
        border: none;
        border-radius: 8px;
        font-size: 13px;
        font-weight: 500;
        cursor: pointer;
        transition: all 0.2s;
    }

    .aptx-btn-view {
        background: #f1f5f9;
        color: #475569;
    }

    .aptx-btn-view:hover {
        background: #e2e8f0;
    }

    .aptx-btn-delete {
        background: #fee2e2;
        color: #dc2626;
    }

    .aptx-btn-delete:hover {
        background: #fecaca;
    }

    /* Empty State */
    .aptx-empty-state {
        text-align: center;
        padding: 60px 40px;
        color: #64748b;
    }

    .aptx-empty-state svg {
        margin-bottom: 16px;
        opacity: 0.5;
    }

    @media (max-width: 1200px) {
        .aptx-stats-grid {
            grid-template-columns: repeat(2, 1fr);
        }
    }
</style>

<div class="aptx-bookings-wrap">
    <div class="aptx-bookings-header">
        <h1><?php _e('Bookings', 'appointix'); ?></h1>
    </div>

    <!-- Stats Cards -->
    <div class="aptx-stats-grid">
        <div class="aptx-stat-card total">
            <div class="aptx-stat-value"><?php echo esc_html($total); ?></div>
            <div class="aptx-stat-label"><?php _e('Total Bookings', 'appointix'); ?></div>
        </div>
        <div class="aptx-stat-card pending">
            <div class="aptx-stat-value"><?php echo esc_html($pending); ?></div>
            <div class="aptx-stat-label"><?php _e('Pending', 'appointix'); ?></div>
        </div>
        <div class="aptx-stat-card confirmed">
            <div class="aptx-stat-value"><?php echo esc_html($confirmed); ?></div>
            <div class="aptx-stat-label"><?php _e('Confirmed', 'appointix'); ?></div>
        </div>
        <div class="aptx-stat-card completed">
            <div class="aptx-stat-value"><?php echo esc_html($completed); ?></div>
            <div class="aptx-stat-label"><?php _e('Completed', 'appointix'); ?></div>
        </div>
    </div>

    <!-- Bookings Table -->
    <div class="aptx-bookings-card">
        <div class="aptx-card-header">
            <h2><?php _e('All Bookings', 'appointix'); ?></h2>
        </div>
        <table class="aptx-bookings-table">
            <thead>
                <tr>
                    <th width="60"><?php _e('ID', 'appointix'); ?></th>
                    <th><?php _e('Customer', 'appointix'); ?></th>
                    <th><?php _e('Apartment', 'appointix'); ?></th>
                    <th><?php _e('Dates', 'appointix'); ?></th>
                    <th><?php _e('Total', 'appointix'); ?></th>
                    <th><?php _e('Status', 'appointix'); ?></th>
                    <th width="100"><?php _e('Actions', 'appointix'); ?></th>
                </tr>
            </thead>
            <tbody id="appointix-bookings-list">
                <?php include(plugin_dir_path(__FILE__) . 'appointix-admin-bookings-list.php'); ?>
            </tbody>
        </table>
    </div>
</div>