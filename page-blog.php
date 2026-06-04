<?php
/**
 * Template Name: page-blog
 */
get_header();

$paged    = max(1, absint($_GET['paged'] ?? 1));
$cat_slug = sanitize_key($_GET['cat'] ?? '');

$cat_id = 0;
if ($cat_slug) {
    $term = get_category_by_slug($cat_slug);
    if ($term) $cat_id = $term->term_id;
}

$args = [
    'post_type'           => 'post',
    'post_status'         => 'publish',
    'posts_per_page'      => 9,
    'paged'               => $paged,
    'orderby'             => 'date',
    'order'               => 'DESC',
    'ignore_sticky_posts' => true,
];
if ($cat_id > 0) $args['cat'] = $cat_id;

$blog_query  = new WP_Query($args);
$filter_cats = get_categories(['hide_empty' => true, 'orderby' => 'name', 'parent' => 0]);
?>

<main>
<section class="container">

  <div class="archive-filters">
    <button class="filter-btn <?= !$cat_slug ? 'active' : '' ?>"
            data-cat=""
            data-cat-id="0">همه</button>

    <?php foreach ($filter_cats as $cat): ?>
      <button class="filter-btn <?= $cat_slug === $cat->slug ? 'active' : '' ?>"
              data-cat="<?= esc_attr($cat->slug) ?>"
              data-cat-id="<?= $cat->term_id ?>">
        <?= esc_html($cat->name) ?>
      </button>
    <?php endforeach; ?>
  </div>

  <style>
    #blog-posts-wrap {
      position: relative; }
    #blog-spinner {
      display: none; position: absolute; top: 50%; left: 50%; transform: translate(-50%, -50%); z-index: 10; pointer-events: none; }
    #blog-spinner svg {
      animation: blog-spin 0.8s linear infinite; }
    @keyframes blog-spin {
      to { transform: rotate(360deg); } }
  </style>

  <div id="blog-posts-wrap"
       data-cat="<?= esc_attr($cat_slug) ?>"
       data-cat-id="<?= $cat_id ?>"
       data-paged="<?= $paged ?>">

    <div id="blog-spinner">
      <svg width="44" height="44" viewBox="0 0 50 50">
        <circle cx="25" cy="25" r="20" fill="none" stroke="#ddd" stroke-width="4"/>
        <path d="M25 5 a20 20 0 0 1 20 20" fill="none" stroke="#333" stroke-width="4" stroke-linecap="round"/>
      </svg>
    </div>

    <?php if ($blog_query->have_posts()): ?>
      <div class="archive-grid">
        <?php while ($blog_query->have_posts()): $blog_query->the_post(); ?>
          <?php emdadcamera_render_card(get_the_ID()); ?>
        <?php endwhile; ?>
      </div>

      <?php if ($blog_query->max_num_pages > 1): ?>
        <div class="blog-pagination">
          <?php echo paginate_links([
            'total'     => $blog_query->max_num_pages,
            'current'   => $paged,
            'prev_text' => '&laquo;',
            'next_text' => '&raquo;',
            'type'      => 'plain',
          ]); ?>
        </div>
      <?php endif; ?>

    <?php else: ?>
      <p class="no-posts-found">هیچ موردی یافت نشد.</p>
    <?php endif; ?>
    <?php wp_reset_postdata(); ?>

  </div>

</section>
</main>
<?php get_footer(); ?>