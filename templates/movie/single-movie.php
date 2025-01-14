<?php
/**
 * Template for displaying single movie posts
 */

get_header();

while (have_posts()) :
    the_post();
    
    // Get movie meta data
    $release_date = get_post_meta(get_the_ID(), '_release_date', true);
    $rating = get_post_meta(get_the_ID(), '_rating', true);
    $budget = get_post_meta(get_the_ID(), '_budget', true);
    $vote_count = get_post_meta(get_the_ID(), '_vote_count', true);
    $backdrop_id = get_post_meta(get_the_ID(), '_backdrop_path', true);
    ?>

    <article id="post-<?php the_ID(); ?>" <?php post_class('single-movie'); ?>>
        <?php if ($backdrop_id) : ?>
            <div class="movie-backdrop">
                <?php echo wp_get_attachment_image($backdrop_id, 'full'); ?>
            </div>
        <?php endif; ?>

        <div class="container">
            <div class="movie-content">
                <div class="movie-poster">
                    <?php the_post_thumbnail('large'); ?>
                </div>

                <div class="movie-details">
                    <header class="movie-header">
                        <h1 class="movie-title"><?php the_title(); ?></h1>
                        
                        <?php if ($release_date) : ?>
                            <div class="movie-release-date">
                                <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($release_date))); ?>
                            </div>
                        <?php endif; ?>
                    </header>

                    <div class="movie-meta">
                        <?php if ($rating) : ?>
                            <div class="movie-rating">
                                <span class="label"><?php _e('Rating:', 'movies-manager'); ?></span>
                                <span class="value"><?php echo esc_html(number_format($rating, 1)); ?>/10</span>
                                <?php if ($vote_count) : ?>
                                    <span class="vote-count">(<?php echo esc_html(number_format($vote_count)); ?> votes)</span>
                                <?php endif; ?>
                            </div>
                        <?php endif; ?>

                        <?php if ($budget) : ?>
                            <div class="movie-budget">
                                <span class="label"><?php _e('Budget:', 'movies-manager'); ?></span>
                                <span class="value"><?php echo esc_html(number_format($budget, 0, '.', ',')); ?> USD</span>
                            </div>
                        <?php endif; ?>

                        <?php
                        $categories = get_the_terms(get_the_ID(), 'movie_category');
                        if ($categories && !is_wp_error($categories)) : ?>
                            <div class="movie-categories">
                                <span class="label"><?php _e('Categories:', 'movies-manager'); ?></span>
                                <?php echo get_the_term_list(get_the_ID(), 'movie_category', '<span class="value">', ', ', '</span>'); ?>
                            </div>
                        <?php endif; ?>
                    </div>

                    <div class="movie-description">
                        <?php the_content(); ?>
                    </div>
                </div>
            </div>
        </div>
    </article>

<?php
endwhile;

get_footer(); 