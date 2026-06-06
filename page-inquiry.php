<?php
/* Template Name: Inquiry */
get_header();

$inquiry_info = 'inquiry_info';
$warranty_url = emdadcamera_Get_Setting($inquiry_info, 'warranty_url');
?>

<main class="inquiry-page">
    <div class="container">

        <div class="inquiry-header">
            <?php emdadcamera_breadcrumbs(); ?>
            <h1>استعلام</h1>
            <p>از طریق این صفحه می‌توانید اصالت کالا و ضمانت‌نامه محصولات خود را بررسی کنید.</p>
        </div>

        <div class="inquiry-cards-wrapper">

            <!-- کارت استعلام اصالت کالا -->
            <div class="inquiry-card">
                <div class="inquiry-card__icon">
                    <i class="icon"><?php echo emdadcamera_Icon('shield'); ?></i>
                </div>
                <div class="inquiry-card__body">
                    <h2 class="inquiry-card__title">استعلام اصالت کالا</h2>
                    <p class="inquiry-card__desc">کد یکتای محصول خود را وارد کنید تا از اصالت آن مطمئن شوید.</p>

                    <div class="inquiry-form" id="authenticity-form">
                        <div class="inquiry-input-group">
                            <input
                                type="text"
                                id="product-unique-code"
                                class="inquiry-input"
                                placeholder="کد یکتای محصول را وارد کنید"
                                maxlength="30"
                                autocomplete="off"
                                dir="ltr"
                            />
                            <button type="button" class="btn btn-reverse inquiry-btn" id="check-authenticity-btn">
                                <span class="btn__text" data-text="استعلام">استعلام</span>
                            </button>
                        </div>
                        <div class="inquiry-result" id="authenticity-result" style="display:none;"></div>
                    </div>
                </div>
            </div>

            <!-- کارت اصالت ضمانت نامه -->
            <div class="inquiry-card">
                <div class="inquiry-card__icon inquiry-card__icon--gold">
                    <i class="icon"><?php echo emdadcamera_Icon('warranty'); ?></i>
                </div>
                <div class="inquiry-card__body">
                    <h2 class="inquiry-card__title">اصالت ضمانت‌نامه</h2>
                    <p class="inquiry-card__desc">برای بررسی اصالت ضمانت‌نامه محصول خود روی دکمه زیر کلیک کنید و وارد سایت ضمانت‌نامه شوید.</p>

                    <div class="inquiry-warranty-action">
                        <?php if (!empty($warranty_url)) : ?>
                            <a href="<?php echo esc_url($warranty_url); ?>" target="_blank" rel="noopener noreferrer" class="btn btn-reverse inquiry-btn inquiry-btn--warranty">
                                <span class="btn__text" data-text="ورود به سایت ضمانت‌نامه">ورود به سایت ضمانت‌نامه</span>
                                <i class="icon"><?php echo emdadcamera_Icon('external-link'); ?></i>
                            </a>
                        <?php else : ?>
                            <a href="#" class="btn btn-reverse inquiry-btn inquiry-btn--warranty" onclick="return false;" style="opacity:.5;cursor:not-allowed;">
                                <span class="btn__text" data-text="لینک ضمانت‌نامه">لینک ضمانت‌نامه تنظیم نشده</span>
                            </a>
                        <?php endif; ?>
                        <p class="inquiry-warranty-note">* لینک از پنل مدیریت قابل تنظیم است.</p>
                    </div>
                </div>
            </div>

        </div>
    </div>
</main>

<style>
/* ----  INQUIRY PAGE  ---- */
.inquiry-page {
    padding: 6rem 0 4rem;
    min-height: 70vh;
}
.inquiry-header {
    text-align: center;
    margin-bottom: 3rem;
}
.inquiry-header h1 {
    font-size: 2rem;
    font-variation-settings: "wght" 700;
    color: var(--font-primary-color);
    margin: .5rem 0;
}
.inquiry-header p {
    color: #666;
    font-size: .95rem;
}

/* ---- Cards wrapper ---- */
.inquiry-cards-wrapper {
    display: grid;
    grid-template-columns: repeat(auto-fit, minmax(320px, 1fr));
    gap: 2rem;
    max-width: 900px;
    margin: 0 auto;
}

/* ---- Card ---- */
.inquiry-card {
    background: var(--normal-secondary-color);
    border: 1px solid var(--dark-secondary-color);
    border-radius: 18px;
    padding: 2rem;
    display: flex;
    flex-direction: column;
    gap: 1.2rem;
    transition: box-shadow .3s ease, transform .3s ease;
}
.inquiry-card:hover {
    box-shadow: 0 8px 32px var(--primary-transparent-1);
    transform: translateY(-3px);
}

/* ---- Icon area ---- */
.inquiry-card__icon {
    width: 64px;
    height: 64px;
    border-radius: 16px;
    background: var(--primary-transparent-1);
    display: flex;
    align-items: center;
    justify-content: center;
}
.inquiry-card__icon svg,
.inquiry-card__icon .icon svg {
    width: 32px;
    height: 32px;
    fill: var(--normal-primary-color);
}
.inquiry-card__icon--gold {
    background: rgba(238,190,0,.15);
}
.inquiry-card__icon--gold svg,
.inquiry-card__icon--gold .icon svg {
    fill: var(--gold-color);
}

/* ---- Card body ---- */
.inquiry-card__title {
    font-size: 1.2rem;
    font-variation-settings: "wght" 700;
    margin: 0 0 .4rem;
}
.inquiry-card__desc {
    font-size: .9rem;
    color: #666;
    line-height: 1.7;
    margin: 0 0 1.2rem;
}

/* ---- Input group ---- */
.inquiry-input-group {
    display: flex;
    gap: .6rem;
    flex-wrap: wrap;
}
.inquiry-input {
    flex: 1;
    min-width: 0;
    height: 2.7rem;
    border: 1.5px solid var(--dark-secondary-color);
    border-radius: 10px;
    padding: 0 1rem;
    font-family: yekanbakh;
    font-size: .9rem;
    background: var(--background-color);
    color: var(--font-primary-color);
    outline: none;
    transition: border-color .25s;
    text-align: left;
}
.inquiry-input:focus {
    border-color: var(--normal-primary-color);
}
.inquiry-btn {
    flex-shrink: 0;
    height: 2.7rem;
}

/* ---- Result box ---- */
.inquiry-result {
    margin-top: 1rem;
    padding: 1rem 1.2rem;
    border-radius: 12px;
    font-size: .9rem;
    line-height: 1.6;
    font-variation-settings: "wght" 600;
    animation: fadeInUp .35s ease;
}
@keyframes fadeInUp {
    from { opacity:0; transform:translateY(8px); }
    to   { opacity:1; transform:translateY(0);   }
}
.inquiry-result--success {
    background: rgba(154,230,197,.2);
    border: 1.5px solid var(--green-color);
    color: #1a7a52;
}
.inquiry-result--error {
    background: var(--primary-transparent-1);
    border: 1.5px solid var(--normal-primary-color);
    color: var(--dark-primary-color);
}
.inquiry-result--loading {
    background: var(--dark-secondary-color);
    border: 1.5px solid #ccc;
    color: #555;
    display: flex;
    align-items: center;
    gap: .6rem;
}
.inquiry-spinner {
    width: 18px;
    height: 18px;
    border: 2.5px solid #ccc;
    border-top-color: var(--normal-primary-color);
    border-radius: 50%;
    animation: spin .7s linear infinite;
    flex-shrink: 0;
}
@keyframes spin { to { transform: rotate(360deg); } }

/* ---- Warranty btn ---- */
.inquiry-warranty-action {
    display: flex;
    flex-direction: column;
    gap: .6rem;
}
.inquiry-btn--warranty {
    width: 100%;
    justify-content: center;
    height: 2.9rem;
    font-size: .95rem;
}
.inquiry-warranty-note {
    font-size: .78rem;
    color: #aaa;
    margin: 0;
}

@media (max-width: 600px) {
    .inquiry-input-group { flex-direction: column; }
    .inquiry-btn { width: 100%; }
}
</style>

<script>
(function() {
    var btn    = document.getElementById('check-authenticity-btn');
    var input  = document.getElementById('product-unique-code');
    var result = document.getElementById('authenticity-result');

    if (!btn) return;

    function showResult(type, msg) {
        result.className = 'inquiry-result inquiry-result--' + type;
        result.innerHTML = msg;
        result.style.display = 'block';
    }

    function setLoading(on) {
        btn.disabled = on;
        if (on) {
            showResult('loading', '<div class="inquiry-spinner"></div> در حال استعلام...');
        }
    }

    btn.addEventListener('click', function() {
        var code = input.value.trim();
        if (!code) {
            showResult('error', '⚠️ لطفاً کد یکتای محصول را وارد کنید.');
            return;
        }

        setLoading(true);

        // استعلام از سایت استعلام.ایران / estelamiran
        var proxyUrl = '<?php echo get_template_directory_uri(); ?>/inquiry-proxy.php?code=' + encodeURIComponent(code);

        fetch(proxyUrl, {
            method: 'GET',
            headers: { 'Accept': 'application/json' }
        })
        .then(function(res) {
            if (!res.ok) throw new Error('HTTP ' + res.status);
            return res.json();
        })
        .then(function(data) {
            btn.disabled = false;
            // پاسخ بسته به ساختار API سایت استعلام ایران
            if (data && (data.valid === true || data.status === 'valid' || data.authentic === true)) {
                showResult('success', '✅ این محصول اصیل است و اصالت آن تأیید شد.');
            } else if (data && (data.valid === false || data.status === 'invalid' || data.authentic === false)) {
                showResult('error', '❌ این محصول اصیل نیست یا کد وارد شده معتبر نیست.');
            } else {
                showResult('error', '⚠️ پاسخی از سرور دریافت نشد. لطفاً دوباره تلاش کنید.');
            }
        })
        .catch(function(err) {
            btn.disabled = false;
            // اگر API مستقیم در دسترس نبود، کاربر را به سایت هدایت می‌کنیم
            showResult('error',
                '⚠️ اتصال مستقیم برقرار نشد. <br>' +
                '<a href="https://estelamiran.ir/?code=' + encodeURIComponent(code) + '" target="_blank" style="color:var(--normal-primary-color);text-decoration:underline;">' +
                'برای استعلام اینجا کلیک کنید ←</a>'
            );
        });
    });

    // Enter key support
    input.addEventListener('keydown', function(e) {
        if (e.key === 'Enter') btn.click();
    });
})();
</script>

<?php
get_footer();
?>