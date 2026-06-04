<?php
if (!defined('ABSPATH')) exit;

class EmdadInvoice_Frontend {

    public function __construct() {
        add_action('init', [$this, 'register_endpoints']);
        add_action('template_redirect', [$this, 'handle_invoice_page']);
        add_filter('query_vars', [$this, 'add_query_vars']);
        add_action('wp_ajax_emdad_pay_invoice', [$this, 'handle_pay_redirect']);
        add_action('wp_ajax_nopriv_emdad_pay_invoice', [$this, 'handle_pay_redirect']);
    }

    public function register_endpoints() {
        add_rewrite_rule('^faktur/([^/]+)/?$', 'index.php?emdad_invoice_token=$matches[1]', 'top');
    }

    public function add_query_vars($vars) {
        $vars[] = 'emdad_invoice_token';
        return $vars;
    }

    public function handle_invoice_page() {
        $token = get_query_var('emdad_invoice_token');
        if (!$token) return;

        // Handle download
        if (isset($_GET['download'])) {
            require_once EMDAD_INVOICE_DIR . 'classes/class-emdad-download.php';
            $dl = new EmdadInvoice_Download();
            $invoice_id = intval($_GET['id'] ?? 0);
            if ($_GET['download'] === 'print') {
                $dl->generatePDF($invoice_id);
            } else {
                $dl->generateIMG($invoice_id);
            }
            exit;
        }

        global $wpdb;
        $invoice = $wpdb->get_row($wpdb->prepare(
            "SELECT * FROM {$wpdb->prefix}emdad_invoices WHERE invoice_number=%s OR id=%d",
            $token, intval($token)
        ));

        if (!$invoice) wp_die('<h2>فاکتور مورد نظر یافت نشد.</h2>', 'خطا', ['response' => 404]);

        $items    = EmdadInvoice_DB::get_invoice_items($invoice->id);
        $payments = EmdadInvoice_DB::get_invoice_payments($invoice->id);
        $settings = get_option('emdad_invoice_settings', []);
        $invoice_url = home_url('/faktur/' . $invoice->invoice_number . '/');
        $qr_url = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($invoice_url);

        include EMDAD_INVOICE_DIR . 'views/invoice-public.php';
        exit;
    }

    public function handle_pay_redirect() {
        $invoice_id = intval($_POST['invoice_id'] ?? 0);
        if (!$invoice_id) wp_die('خطا');
        $invoice = EmdadInvoice_DB::get_invoice($invoice_id);
        if (!$invoice || $invoice->remaining <= 0) wp_die('فاکتور معتبر نیست');
        wp_redirect(admin_url('admin-post.php?action=emdad_pay_invoice&invoice_id=' . $invoice_id));
        exit;
    }
}
