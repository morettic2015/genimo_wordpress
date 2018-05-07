<?php
/**
 * Template: Single Listing Image
 */
global $listing;
?>

<?php if (has_post_thumbnail($listing->ID)) : ?>

    <meta itemprop="image" content="<?php echo esc_attr(wpsight_listing_thumbnail_url($listing->ID, 'large')); ?>" />

    <div class="wpsight-listing-section wpsight-listing-section-image">




        <?php do_action('wpsight_listing_single_image_before'); ?>

        <center>
            <?php
            /**
             * @Meta Key
             */
            $the_query = new WP_Query(array("post_type" => "ml-slider", "meta_key" => "_listing_slider", "meta_value" => $listing->ID));
            $cached = get_the_ID();
            //echo $cached;
            if ($the_query->have_posts()) {
                $the_query->the_post();
                $sliderId = get_the_ID();
                echo do_shortcode("[metaslider id='" . $sliderId . "']");
                $listing->ID = $cached;
            }
            ?>
        </center>
        <?php do_action('wpsight_listing_single_image_after'); ?>

    </div><!-- .wpsight-listing-section -->

<?php endif; ?>