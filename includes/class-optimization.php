<?php

class Optimization {

    public function __construct() {
        // add_action( 'wp_enqueue_scripts', [$this, 'Dregister_Script'], 99 );
        // add_action('elementor/editor/after_enqueue_scripts', 'Dregister_Script', 15);
        add_action('wp_default_scripts', [$this, 'sponsersho_remove_jquery_migrate']);
        add_action('init', [$this, 'sponsersho_disable_emojis']);
        add_action('init', [$this, 'sponsersho_disable_embeds'], 9999);
        remove_action( 'wp_enqueue_scripts', 'wp_enqueue_global_styles' );
        remove_action( 'wp_footer', 'wp_enqueue_global_styles', 1 );
        remove_action( 'wp_body_open', 'wp_global_styles_render_svg_filters' );
        remove_action('wp_head', 'wp_shortlink_wp_head', 10, 0);

        add_filter('use_block_editor_for_post', '__return_false');
        add_filter('use_block_editor_for_post_type', '__return_false', 10);
        remove_action('try_gutenberg_panel', 'wp_try_gutenberg_panel');

        add_filter('the_content', [$this, 'add_lazy_load_attribute']);
        add_filter('widget_text', [$this, 'add_lazy_load_attribute']);
        add_filter('post_thumbnail_html', [$this, 'add_lazy_load_attribute'], 10, 5);

        add_action('wp_footer', function () {
            wp_dequeue_style('core-block-supports');
        });

        add_action('do_feed', [$this, 'disable_all_feeds'], 1);
        add_action('do_feed_rdf', [$this, 'disable_all_feeds'], 1);
        add_action('do_feed_rss', [$this, 'disable_all_feeds'], 1);
        add_action('do_feed_rss2', [$this, 'disable_all_feeds'], 1);
        add_action('do_feed_atom', [$this, 'disable_all_feeds'], 1);
        add_action('do_feed_rss2_comments', [$this, 'disable_all_feeds'], 1);
        add_action('do_feed_atom_comments', [$this, 'disable_all_feeds'], 1);

        remove_action('wp_head', 'feed_links', 2);
        remove_action('wp_head', 'feed_links_extra', 3);
    }

    public function disable_all_feeds() {
        wp_die(
            __('Feeds are not available on this site. Please visit the homepage.', 'your-textdomain'),
            __('Feeds Disabled', 'your-textdomain'),
            array('response' => 404)
        );
    }    

    public function Dregister_Script() {
        wp_dequeue_style( 'wp-block-library' );
    }

    public function sponsersho_remove_jquery_migrate($scripts){
        if (!is_admin() && isset($scripts->registered['jquery'])) {
            $script = $scripts->registered['jquery'];
            
            if ($script->deps) {
                $script->deps = array_diff($script->deps, array(
                    'jquery-migrate'
                ));
            }
        }
    }

    public function sponsersho_disable_emojis(){
        remove_action('wp_head', 'print_emoji_detection_script', 7);
        remove_action('admin_print_scripts', 'print_emoji_detection_script');
        remove_action('wp_print_styles', 'print_emoji_styles');
        remove_action('admin_print_styles', 'print_emoji_styles');	
        remove_filter('the_content_feed', 'wp_staticize_emoji');
        remove_filter('comment_text_rss', 'wp_staticize_emoji');	
        remove_filter('wp_mail', 'wp_staticize_emoji_for_email');
        add_filter('emoji_svg_url', '__return_false');
    }

    public function sponsersho_disable_embeds() {
        global $wp;
        $wp->public_query_vars = array_diff($wp->public_query_vars, array('embed'));
        add_filter('embed_oembed_discover', '__return_false');
        remove_filter('oembed_dataparse', 'wp_filter_oembed_result', 10);
        remove_action('wp_head', 'wp_oembed_add_discovery_links');
        remove_action('wp_head', 'wp_oembed_add_host_js');
        add_filter('tiny_mce_plugins', [$this, 'sponsersho_disable_embeds_tiny_mce_plugin']);
        add_filter('rewrite_rules_array', [$this, 'sponsersho_disable_embeds_rewrites']);
        remove_filter('pre_oembed_result', 'wp_filter_pre_oembed_result', 10);
    }

    public function sponsersho_disable_embeds_tiny_mce_plugin($plugins) {
        return array_diff($plugins, array('wpembed'));
    }
    
    public function sponsersho_disable_embeds_rewrites($rules) {
        foreach($rules as $rule => $rewrite) {
            if(false !== strpos($rewrite, 'embed=true')) {
                unset($rules[$rule]);
            }
        }
        return $rules;
    }

    function add_lazy_load_attribute($content) {
        if (is_admin()){
            return $content;
        }
        return str_replace('<img ', '<img loading="lazy" ', $content);
    }
}

new Optimization;