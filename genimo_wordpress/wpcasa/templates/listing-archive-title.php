<div class="wpsight-listing-section wpsight-listing-section-title">
	
	<?php do_action( 'wpsight_listing_archive_title_before' ); ?>
    <!-- <?php 
        global $wpdb;
        $post = get_post();
        $query = "SELECT meta_value from wp_postmeta where meta_key='_listing_id' and post_id = ".$post->ID;
        $query2 = "SELECT meta_value from wp_postmeta where meta_key='_cd_internal_' and post_id = ".$post->ID;
        $code1 = $wpdb->get_var($query);
        $code2 = $wpdb->get_var($query2);
        $code = empty($code2)?$code1:$code2;
    
?>-->
	<div class="wpsight-listing-title">
	    <?php the_title( sprintf( '<h2 class="entry-title"><a href="%s" rel="bookmark">', esc_url( get_permalink() ) ), ' - '.$code.'</a></h2>' ); ?>
	</div>
	
	<?php do_action( 'wpsight_listing_archive_title_after' ); ?>

</div><!-- .wpsight-listing-section-title -->