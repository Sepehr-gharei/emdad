<?php
if ( ! defined( 'ABSPATH' ) ) exit;
if ( ! defined( 'wp_directory' ) ) {define( 'wp_directory', get_template_directory() );}
if ( ! defined( 'wp_directory_uri' ) ) {define( 'wp_directory_uri', get_template_directory_uri() );}
$admin = wp_directory . "/admin/";

require_once $admin . "class-admin-helper.php";
require_once $admin . "flash-message.php";
require_once $admin . "class-main-settings.php";
require_once $admin . "class-main-metabox.php";