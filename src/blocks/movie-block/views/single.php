<?php
$queryArgs = [
  'post_type' => 'movie',
  'p' => $args['movieId']
];
$query = new WP_Query($queryArgs);
?>

<?php if ($query->have_posts()) : ?>
  <div class="movie-block-single">
    <?php while ($query->have_posts()) : ?>
      <div class="movie-block-single__card mv_card">
        <?php $query->the_post(); ?>
        <?php the_post_thumbnail("medium", ['class' => 'movie-block-single__image']); ?>
        <div class="movie-block-single__content">
          <h2 class="movie-block-single__title mv_title"><?php the_title(); ?></h2>
          <div class="movie-block-single__categories mv_categories">
            <?php $genres = get_the_terms(get_the_ID(), 'movie_category'); ?>
        <?php foreach ($genres as $genre) : ?>
          <span class="movie-block-single__category mv_category"><?php echo $genre->name; ?></span>
        <?php endforeach; ?>
          </div>
          <p><?= the_content(); ?></p>
        </div>
      </div>
    <?php endwhile; ?>
  </div>
<?php endif; ?>
