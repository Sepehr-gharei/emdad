<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'wp_directory' ) ) {define( 'wp_directory', get_template_directory() );}
if ( ! defined( 'wp_directory_uri' ) ) {define( 'wp_directory_uri', get_template_directory_uri() );}
$dir = wp_directory;
$dir_admin = $dir . '/admin/';
$dir_inc = $dir . '/includes/';

if(is_admin()) {
    require_once $dir_admin . 'class-admin.php';
    require_once wp_directory . "/includes/tinymce-advanced/tinymce-advanced.php";
}

require_once $dir_inc . 'helper.php';
require_once $dir_inc . 'template-tag.php';
require_once $dir_inc . 'class-icon.php';
require_once $dir_inc . 'class-optimization.php';
require_once $dir_inc . 'upload-mimes.php';
require_once $dir_inc . 'post-views.php';
require_once $dir_inc . 'post-type.php';
require_once $dir_inc . 'ajax.php';
require_once $dir_inc . 'like-dislike.php';
require_once $dir_inc . 'jdf.php';
require_once $dir_inc . 'pagination-canonical.php';
require_once $dir_inc . 'custom-search.php';
require_once $dir_inc . 'forms/includes.php';
require_once $dir_inc . 'class-menu-walker.php';
require_once $dir_inc . 'emdadcamera-login/emdadcamera-login.php';
require_once $dir_inc . 'emdad-invoice/emdad-invoice-init.php';
