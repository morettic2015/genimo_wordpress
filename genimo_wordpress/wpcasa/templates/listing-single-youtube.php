<?php
global $listing;
$url = "https://genimo.com.br/api/site/property/41/" . get_post_meta($listing->ID, '_listing_id', true);
$data = wp_remote_get($url);
$json = json_decode($data['body']);
$videos = $json->property->videos;
$youtube = str_replace("watch?v=", "", $videos[0]->dsEmbedSource);
$total = count($videos);
if ($total > 0) {
    ?>
    <iframe width="100%" height="400" src="<?php echo $youtube; ?>" frameborder="0" allow="autoplay; encrypted-media" allowfullscreen></iframe>
<?php } ?>
