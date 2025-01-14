<?php
/**
 * Template for displaying movie archives
 */

get_header();

// Get current category if we're on a taxonomy page
$current_category = get_queried_object();
$current_category_id = $current_category instanceof WP_Term ? $current_category->term_id : 0;
?>

<div class="movies-archive">
    <div class="container">
        <header class="page-header">
            <?php if (is_tax('movie_category')) : ?>
                <h1 class="page-title"><?php echo esc_html($current_category->name); ?></h1>
            <?php else : ?>
                <h1 class="page-title"><?php _e('Movies', 'movies-manager'); ?></h1>
            <?php endif; ?>
        </header>

        <!-- Category filter list -->
        <div class="movies-category-filter">
            <ul>
                <li>
                    <a href="<?php echo esc_url(get_post_type_archive_link('movie')); ?>" 
                       class="<?php echo !is_tax('movie_category') ? 'active' : ''; ?>">
                        <?php _e('All Movies', 'movies-manager'); ?>
                    </a>
                </li>
                <?php
                $categories = get_terms(array(
                    'taxonomy' => 'movie_category',
                    'hide_empty' => true,
                ));

                foreach ($categories as $category) :
                    $is_current = $current_category_id === $category->term_id;
                ?>
                    <li>
                        <a href="<?php echo esc_url(get_term_link($category)); ?>" 
                           class="<?php echo $is_current ? 'active' : ''; ?>">
                            <?php echo esc_html($category->name); ?>
                            <span class="count">(<?php echo esc_html($category->count); ?>)</span>
                        </a>
                    </li>
                <?php endforeach; ?>
            </ul>
        </div>

        <?php if (have_posts()) : ?>
            <div class="movies-grid">
                <?php while (have_posts()) : the_post(); ?>
                    <article id="post-<?php the_ID(); ?>" <?php post_class('movie-card'); ?>>
                        <div class="movie-poster">
                            <?php if (has_post_thumbnail()) : ?>
                                <a href="<?php the_permalink(); ?>">
                                    <?php the_post_thumbnail('medium'); ?>
                                </a>
                            <?php endif; ?>
                        </div>
                        
                        <div class="movie-details">
                            <h2 class="movie-title">
                                <a href="<?php the_permalink(); ?>"><?php the_title(); ?></a>
                            </h2>
                            
                            <?php
                            $release_date = get_post_meta(get_the_ID(), '_release_date', true);
                            $rating = get_post_meta(get_the_ID(), '_rating', true);
                            ?>
                            
                            <?php if ($release_date) : ?>
                                <div class="movie-release-date">
                                    <?php echo esc_html(date_i18n(get_option('date_format'), strtotime($release_date))); ?>
                                </div>
                            <?php endif; ?>
                            
                            <?php if ($rating) : ?>
                                <div class="movie-rating">
                                    <?php echo esc_html(number_format($rating, 1)); ?>/10
                                </div>
                            <?php endif; ?>
                            
                            <div class="movie-excerpt">
                                <?php the_excerpt(); ?>
                            </div>
                        </div>
                    </article>
                <?php endwhile; ?>
            </div>

            <?php the_posts_pagination(); ?>

        <?php else : ?>
            <p><?php _e('No movies found.', 'movies-manager'); ?></p>
        <?php endif; ?>
    </div>
</div>

<?php
get_footer(); 