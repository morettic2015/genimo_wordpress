<?php
/**
 * Template: Before Listings Archive
 */
global $wpsight_query; ?>

<?php do_action( 'wpsight_listings_before', $wpsight_query ); ?>

<?php if( isset( $show_panel ) && $show_panel ) wpsight_panel( $wpsight_query ); ?>

<?php if( wpsight_is_listings_archive() && ! is_page() ) : ?>

<div class="wpsight-listings hentry">

	<div class="entry-content">

<?php else : ?>

<div class="wpsight-listings">

<?php endif; ?>