<?php
/**
 * Template: After Listings Archive
 */
global $wpsight_query; ?>

	<?php if( is_tax() ) : ?>
	</div><!-- .entry-content -->
	<?php endif; ?>

</div><!-- .wpsight-listings -->

<?php if( isset( $show_paging ) && $show_paging ) wpsight_pagination( $wpsight_query->max_num_pages ); ?>

<?php do_action( 'wpsight_listings_after', $wpsight_query ); ?>