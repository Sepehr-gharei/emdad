<?php


function allow_svg_upload($mimes) {
    $mimes['svg'] = 'image/svg+xml';
    $mimes['svgz'] = 'image/svg+xml';

    return $mimes;
}
add_filter('upload_mimes', 'allow_svg_upload');

function fix_svg_mime_type($data, $file, $filename, $mimes) {
    $ext = pathinfo($filename, PATHINFO_EXTENSION);
    if ($ext === 'svg') {
        $data['ext'] = 'svg';
        $data['type'] = 'image/svg+xml';
    }
    return $data;
}
add_filter('wp_check_filetype_and_ext', 'fix_svg_mime_type', 10, 4);

function check_svg_upload_capability($file) {
    $wp_filetype = wp_check_filetype($file['name']);

    if ($wp_filetype['ext'] === 'svg' && !current_user_can('manage_options')) {
        $file['error'] = 'شما مجاز به آپلود فایل‌های SVG نیستید.';
    }

    return $file;
}
add_filter('wp_handle_upload_prefilter', 'check_svg_upload_capability');