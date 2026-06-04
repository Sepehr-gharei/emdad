<?php
/* Template Name: Order */
get_header();
?>


<main class="order-page">
        <div class="container">
            <div class="order-header">
                <h1>فرم ثبت سفارش</h1>
                <p>اطلاعات خود را وارد کنید تا کارشناسان ما با شما تماس بگیرند.</p>
            </div>
    
            <?php emdad_render_form('order'); ?>
        </div>
    </main>
    
<?php 
get_footer();