<?php
/**
 * Template Name: login
 */
if ( ! defined('ABSPATH') ) exit;

remove_action('template_redirect', 'wc_login_redirect');

?><!DOCTYPE html>
<html <?php language_attributes(); ?>>
<head>
    <meta charset="<?php bloginfo('charset'); ?>">
    <meta name="viewport" content="width=device-width, initial-scale=1">
    <title><?php wp_title('|', true, 'right'); ?></title>
    <?php wp_head(); ?>
</head>
<body <?php body_class('ec-fullpage-body'); ?>>
<?php wp_body_open(); ?>

    <?php echo do_shortcode('[emdadcamera_customer_login]'); ?>

<?php wp_footer(); ?>
</body>
</html>