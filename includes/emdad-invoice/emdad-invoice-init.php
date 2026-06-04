<?php
/**
 * فایل راه‌انداز سیستم فاکتور اختصاصی - نسخه پیشرفته
 */
if (!defined('ABSPATH')) exit;

define('EMDAD_INVOICE_VERSION', '2.1.0');
define('EMDAD_INVOICE_DIR', get_template_directory() . '/includes/emdad-invoice/');
define('EMDAD_INVOICE_URL', get_template_directory_uri() . '/includes/emdad-invoice/');

require_once EMDAD_INVOICE_DIR . 'classes/class-emdad-db.php';
require_once EMDAD_INVOICE_DIR . 'classes/class-emdad-admin.php';
require_once EMDAD_INVOICE_DIR . 'classes/class-emdad-frontend.php';
require_once EMDAD_INVOICE_DIR . 'classes/class-emdad-zarinpal.php';
require_once EMDAD_INVOICE_DIR . 'classes/class-emdad-download.php';

// رفع خطای Deprecated: the_block_template_skip_link (وردپرس 6.4+)
add_action( 'after_setup_theme', function() {
    if ( function_exists( 'wp_enqueue_block_template_skip_link' ) ) {
        remove_action( 'wp_footer', 'the_block_template_skip_link' );
        add_action( 'wp_footer', 'wp_enqueue_block_template_skip_link' );
    }
}, 1 );

function emdad_run_invoice_system() {
    new EmdadInvoice_DB();
    new EmdadInvoice_Frontend();
    new EmdadInvoice_Zarinpal();
    if (is_admin()) {
        new EmdadInvoice_Admin();
    }
}
add_action('after_setup_theme', 'emdad_run_invoice_system');
