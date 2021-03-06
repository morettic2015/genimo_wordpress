<?php
/**
 * Template: Single Listing Description
 */
global $listing; ?>

<div class="wpsight-listing-section wpsight-listing-section-description">
	
	<?php do_action( 'wpsight_listing_single_description_before', $listing->ID ); ?>
	
	<?php if( wpsight_is_listing_not_available() ) : ?>
		<div class="wpsight-alert wpsight-alert-small wpsight-alert-not-available">
			<?php _e( 'This property is currently not available.', 'wpcasa' ); ?>
		</div>
	<?php endif; ?>

	
	
	<?php do_action( 'wpsight_listing_single_description_after', $listing->ID ); ?>

</div><!-- .wpsight-listing-section -->