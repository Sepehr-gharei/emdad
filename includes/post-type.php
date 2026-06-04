<?php

function cyberisho_register_post_type() {

	$labels = [
		'name'               => 'اپلیکیشن و برنامه ها',
		'singular_name'      => 'اپلیکیشن و برنامه',
		'add_new'            => 'افزودن جدید',
		'add_new_item'       => 'افزودن اپلیکیشن و برنامه جدید',
		'edit_item'          => 'ویرایش اپلیکیشن و برنامه',
		'new_item'           => 'اپلیکیشن و برنامه جدید',
		'all_items'          => 'همه اپلیکیشن و برنامه ها',
		'view_item'          => 'مشاهده اپلیکیشن و برنامه',
		'search_items'       => 'جستجوی اپلیکیشن و برنامه ها',
		'not_found'          => 'موردی یافت نشد',
		'not_found_in_trash' => 'موردی در زباله‌دان یافت نشد',
		'menu_name'          => 'اپلیکیشن و برنامه ها',
	];
	$args = [
		'labels'             => $labels,
		'public'             => false,
		'show_ui'            => true,
		'show_in_menu'       => true,
		'show_in_rest'       => false,
		'has_archive'        => false,
		'publicly_queryable' => false,
		'exclude_from_search'=> false,
		'supports'           => [ 'title', 'editor', 'thumbnail', 'excerpt' ],
		'menu_icon'          => 'dashicons-smartphone',
		'rewrite'            => [ 'slug' => 'applications' ],
	];

	register_post_type( 'applications', $args );
	
}
add_action( 'init', 'cyberisho_register_post_type' );