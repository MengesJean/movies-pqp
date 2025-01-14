<?php
/**
 * @see https://github.com/WordPress/gutenberg/blob/trunk/docs/reference-guides/block-api/block-metadata.md#render
 */
?>
<?php if($attributes['displayType'] === 'single'): ?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo Movies_Utils::get_template_part('src/blocks/movie-block/views/single', null, $attributes); ?>
</div>
<?php else: ?>
	<div <?php echo get_block_wrapper_attributes(); ?>>
	<?php echo Movies_Utils::get_template_part('src/blocks/movie-block/views/multiple', null, $attributes); ?>
</div>
<?php endif; ?>
