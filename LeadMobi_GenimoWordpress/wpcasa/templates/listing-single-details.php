<?php
/**
 * Template: Single Listing Details
 */
global $listing;
?>

<div class="wpsight-listing-section wpsight-listing-section-details">

    <?php
    do_action('wpsight_listing_single_details_before', $listing->ID);

    echo "<p style='text-align: justify;text-justify: inter-word;line-height: 1.6;font-size:18px'>";
    $li = get_post($listing->ID);
    echo $li->post_content;
    echo "</p>";
    ?>

    <?php wpsight_listing_details($listing->ID); ?>

<?php do_action('wpsight_listing_single_details_after', $listing->ID); ?>

</div><!-- .wpsight-listing-section-details -->