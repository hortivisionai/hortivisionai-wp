<?php
/**
 * Title: Default Footer
 * Slug: twentytwentythree/footer-default
 * Categories: footer
 * Block Types: core/template-part/footer
<<<<<<< HEAD
 * Description: Footer with site title and powered by WordPress.
=======
>>>>>>> 5a18ec73027fbaf1a0c22718fce45ac95a328f94
 */
?>
<!-- wp:group {"layout":{"type":"constrained"}} -->
<div class="wp-block-group">
	<!-- wp:group {"align":"wide","style":{"spacing":{"padding":{"top":"var:preset|spacing|40"}}},"layout":{"type":"flex","justifyContent":"space-between"}} -->
	<div class="wp-block-group alignwide" style="padding-top:var(--wp--preset--spacing--40)">
		<!-- wp:site-title {"level":0} /-->
		<!-- wp:paragraph {"align":"right"} -->
		<p class="has-text-align-right">
		<?php
		printf(
			/* Translators: WordPress link. */
			esc_html__( 'Proudly powered by %s', 'twentytwentythree' ),
			'<a href="' . esc_url( __( 'https://wordpress.org', 'twentytwentythree' ) ) . '" rel="nofollow">WordPress</a>'
		)
		?>
		</p>
		<!-- /wp:paragraph -->
	</div>
	<!-- /wp:group -->
</div>
<!-- /wp:group -->
