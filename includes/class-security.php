<?php

class Security {

    public function __construct() {
        define( 'DISALLOW_FILE_EDIT', true );
        add_filter( 'the_generator', [$this, 'remove_wordpress_version_number']);
        add_filter( 'rest_endpoints', [$this, 'disable_wordpress_users_rest_api']);

        add_action( 'login_init',[$this, 'redirect_login_page']);
        add_filter( 'authenticate', [$this, 'verify_username_password'], 1, 3);
        add_action( 'wp_logout',[$this, 'logout_page']);

        add_action('admin_init', function () {
            if (!is_user_logged_in()) {
                return;
            }
            $user = wp_get_current_user();
            if (in_array('subscriber', (array) $user->roles)) {
                if (defined('DOING_AJAX') && DOING_AJAX) {
                    return;
                }

                wp_redirect(home_url('/profile/'));
                exit;
            }
        });

        // Optional: Redirect wp-admin login attempts to profile for subscribers
        add_filter('login_redirect', function ($redirect_to, $request, $user) {
            // Check if user is valid and has subscriber role
            if ($user && !is_wp_error($user) && in_array('subscriber', (array) $user->roles)) {
                return home_url('/profile/');
            }
            return $redirect_to;
        }, 10, 3);
    }
      
    public function remove_wordpress_version_number() {
          return '';
    }

    public function disable_wordpress_users_rest_api($endpoints) {
        if ( isset( $endpoints['/wp/v2/users'] ) ) {
            unset( $endpoints['/wp/v2/users'] );
        }
        if ( isset( $endpoints['/wp/v2/users/(?P<id>[d]+)'] ) ) {
            unset( $endpoints['/wp/v2/users/(?P<id>[d]+)'] );
        }
        return $endpoints;
    }

    public function redirect_login_page() {
        $login_page = home_url();
        $page_viewed = $_SERVER['REQUEST_URI'];
    
        if (strpos($page_viewed, 'wp-login.php') !== false && $_SERVER['REQUEST_METHOD'] == 'GET' && !isset($_GET['action'])) {
            wp_redirect($login_page);
            exit;
        }
    
        if (strpos($page_viewed, 'wp-login.php') !== false && isset($_GET['action']) && $_GET['action'] == 'register') {
            wp_redirect($login_page);
            exit;
        }
    
        if (!is_user_logged_in() && strpos($page_viewed, 'profile') !== false) {
            wp_redirect(home_url('/login/'));
            exit;
        }
    }
    
    
    public function login_failed() {
        $login_page  = home_url('/manager-login/');
        wp_redirect( $login_page . '?login=failed' );
        exit;
    }
       
    public function verify_username_password( $user, $username, $password ) {
        $login_page  = home_url();
          if( $username == "" || $password == "" ) {
              wp_redirect( $login_page );
              exit;
          }
    }
      
    public function logout_page() {
        $home_url  = home_url();
        wp_redirect( $home_url );
        exit;
    }
}

new Security;