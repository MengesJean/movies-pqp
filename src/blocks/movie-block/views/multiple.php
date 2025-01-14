<?php
$queryArgs = [
  'post_type' => 'movie',
  'posts_per_page' => 3,
  'orderby' => 'date',
  'order' => 'DESC',
];
$query = new WP_Query($queryArgs);
?>

<?php if ($query->have_posts()) : ?>
  <div class="movie-block-multiple">
  <?php while ($query->have_posts()) : ?>
    <?php $query->the_post(); ?>
      <a href="<?php the_permalink(); ?>" class="movie-block-multiple-item mv_card">
      <?php the_post_thumbnail("medium", ['class' => 'movie-block-multiple-item__image']); ?>
      <div class="movie-block-multiple-item__content">
        <h2 class="movie-block-multiple-item__title mv_title"><?= the_title(); ?></h2>
        <div class="movie-block-multiple-item__categories mv_categories">
          <?php $genres = get_the_terms(get_the_ID(), 'movie_category'); ?>
          <?php foreach ($genres as $genre) : ?>
            <span class="movie-block-multiple-item__category mv_category"><?php echo $genre->name; ?></span>
          <?php endforeach; ?>
        </div>
        <div><?= the_excerpt(); ?></div>
      </div>
    </a>
    <?php endwhile; ?>
  </div>
<?php endif; ?>
