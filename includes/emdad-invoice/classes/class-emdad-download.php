<?php
if (!defined('ABSPATH')) exit;

class EmdadInvoice_Download {

    public function generatePDF($invoice_id) {
        $this->renderForOutput($invoice_id, 'pdf');
    }

    public function generateIMG($invoice_id) {
        $this->renderForOutput($invoice_id, 'img');
    }

    public function renderForOutput($invoice_id, $type) {

        $invoice = EmdadInvoice_DB::get_invoice($invoice_id);
        if (!$invoice) wp_die('فاکتور یافت نشد');

        $items    = EmdadInvoice_DB::get_invoice_items($invoice_id);
        $payments = EmdadInvoice_DB::get_invoice_payments($invoice_id);
        $settings = get_option('emdad_invoice_settings', []);

        $invoice_url  = home_url('/faktur/' . $invoice->invoice_number . '/');
        $qr_url       = 'https://api.qrserver.com/v1/create-qr-code/?size=200x200&data=' . urlencode($invoice_url);
        $invoice_name = 'invoice-' . $invoice->invoice_number;

        // ── رندر همان view زیبای فاکتور ──
        ob_start();
        include EMDAD_INVOICE_DIR . 'views/invoice-public.php';
        $html = ob_get_clean();

        // حذف </body></html> تا بتوانیم چیزی اضافه کنیم
        $html = preg_replace('#</body>\s*</html>\s*$#i', '', $html);

        // ── تغییر viewport به عرض A4 (794px) ──
        // این مهم‌ترین تغییر است: مرورگر صفحه را با عرض A4 رندر می‌کند
        $html = str_replace(
            '<meta name="viewport" content="width=device-width, initial-scale=1.0">',
            '<meta name="viewport" content="width=794">',
            $html
        );

        echo $html;

        // ── CSS مشترک برای هر دو حالت ──
        echo '<style>
        *, *::before, *::after {
            -webkit-print-color-adjust: exact !important;
            print-color-adjust: exact !important;
            color-adjust: exact !important;
        }

        /* پنهان کردن عناصر غیر فاکتور */
        .emdad-pay-bar,
        .invoice-actions-download,
        .invoice-pay-section,
        .paid-stamp,
        #wpadminbar, .admin-bar { display: none !important; }

        html { margin-top: 0 !important; }
        body { margin: 0 !important; padding: 0 !important; background: #fff !important; }
        .emdad-invoice-wrap {
            padding: 0 !important;
            background: #fff !important;
            margin: 0 !important;
        }
        .emdad-invoice-container {
            width: 794px !important;
            max-width: 794px !important;
            margin: 0 !important;        /* حذف margin:auto - باعث offset در capture می‌شه */
            box-shadow: none !important;
            border-radius: 0 !important;
            overflow: hidden !important; /* hidden به جای visible برای capture صحیح */
        }

        /* اجبار رنگ‌ها */
        .invoice-top-bar { background: #e41522 !important; }
        .invoice-type-title { color: #e41522 !important; -webkit-text-fill-color: #e41522 !important; background: none !important; }
        .party-title { color: #e41522 !important; }
        .party-box   { border-top: 3px solid #e41522 !important; }
        .seller-box  { border-top-color: #e41522 !important; }
        .buyer-box   { border-top-color: #111 !important; }
        .invoice-table thead th { background: #e41522 !important; color: #fff !important; }
        .grand-total { background: #e41522 !important; color: #fff !important; }
        .grand-total span   { color: rgba(255,255,255,.85) !important; }
        .grand-total strong { color: #fff !important; }
        .payments-table th  { background: #111 !important; color: #fff !important; }
        .badge-num           { background: #111 !important; color: #fff !important; }
        .invoice-footer-bar  { background: #111 !important; color: rgba(255,255,255,.8) !important; }
        .section-title::before { background: #22c55e !important; }
        .invoice-note-box    { border-right-color: #e41522 !important; }
        .bank-info-box       { border-right-color: #e41522 !important; }
        .remaining-row       { background: #fff5f5 !important; }
        .remaining-row span, .remaining-row strong { color: #ef4444 !important; }
        .paid-row strong     { color: #22c55e !important; }
        .finance-summary-section { background: #f5f5f7 !important; }
        .parties-section     { background: #f9f9f9 !important; }
        .sig-area            { background: #f5f5f7 !important; }
        .invoice-table tbody tr:nth-child(even) td { background: #f9f9f9 !important; }
        .subtotal-row td     { background: #f5f5f7 !important; }

        @page { size: A4 portrait; margin: 0; }
        </style>';

        if ($type === 'pdf') {
            // ── PDF: window.print() ──
            echo '<script>
            (function(){
                function doPrint(){ window.print(); }
                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(function(){ setTimeout(doPrint, 500); });
                } else {
                    setTimeout(doPrint, 1500);
                }
            })();
            </script>';

        } else {
            // ── دانلود تصویر با dom-to-image-more ──
            $domtoimg = EMDAD_INVOICE_URL . 'assets/js/dom-to-image-more.min.js';
            echo '<script src="' . esc_url($domtoimg) . '"></script>';
            echo '<script>
            (function(){
                var name = ' . json_encode($invoice_name) . ';

                function capture(){
                    var node = document.querySelector(".emdad-invoice-container");
                    if (!node){ alert("خطا: المان فاکتور یافت نشد"); return; }

                    // ── ریست کامل موقعیت‌بندی container قبل از عکس ──
                    node.style.width     = "794px";
                    node.style.maxWidth  = "794px";
                    node.style.margin    = "0";          // حذف margin:auto که باعث offset می‌شه
                    node.style.position  = "relative";
                    node.style.overflow  = "hidden";     // overflow:visible باعث کات شدن می‌شه

                    var w = 794;
                    var h = node.scrollHeight;
                    var scale = 2; // رزولوشن 2x برای کیفیت بالا

                    domtoimage.toPng(node, {
                        bgcolor : "#ffffff",
                        width   : w * scale,
                        height  : h * scale,
                        style   : {
                            transform       : "scale(" + scale + ")",
                            // "top left" برای RTL صحیح است - از گوشه چپ-بالا scale می‌شود
                            transformOrigin : "top left",
                            width           : w + "px",
                            height          : h + "px",
                            margin          : "0",
                            padding         : "0"
                        }
                    }).then(function(dataUrl){
                        var a = document.createElement("a");
                        a.href     = dataUrl;
                        a.download = name + ".png";
                        document.body.appendChild(a);
                        a.click();
                        document.body.removeChild(a);
                    }).catch(function(e){
                        console.error(e);
                        alert("خطا در تولید تصویر: " + e);
                    });
                }

                function waitAndCapture(){
                    // صبر اضافی برای QR، لوگو و فونت‌ها
                    var imgs = document.querySelectorAll("img");
                    var total = imgs.length;
                    var loaded = 0;

                    function tryCapture(){
                        loaded++;
                        if (loaded >= total) setTimeout(capture, 400);
                    }

                    if (total === 0){
                        setTimeout(capture, 600);
                        return;
                    }
                    imgs.forEach(function(img){
                        if (img.complete && img.naturalWidth > 0){
                            tryCapture();
                        } else {
                            img.onload  = tryCapture;
                            img.onerror = tryCapture; // حتی اگر خطا داشت ادامه بده
                        }
                    });
                }

                if (document.fonts && document.fonts.ready) {
                    document.fonts.ready.then(waitAndCapture);
                } else {
                    window.addEventListener("load", function(){ setTimeout(waitAndCapture, 500); });
                }
            })();
            </script>';
        }

        remove_action('wp_footer', 'the_block_template_skip_link');
        wp_footer();
        echo '</body></html>';
        exit;
    }
}
